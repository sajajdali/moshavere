<?php

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Api\Entities\AuthRequest;
use Modules\Api\Entities\UserDevice;
use Modules\Api\Enum\UserDeviceTypeEnum;
use Modules\Api\Http\Requests\LoginRequest;
use Modules\Api\Http\Requests\RegisterRequest;
use Modules\Api\Http\Requests\VerifyRequest;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\Api\Transformers\UserResource;
use Modules\User\Entities\UserMeta;
use Modules\User\Enum\UserMetaEnum;

class AuthController extends Controller
{
    use ApiHandlerTrait;

    const STATUS_VERIFY = 1;
    const STATUS_REGISTER = 2;

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {

        $accessToken = $request->bearerToken();
        $token = PersonalAccessToken::findToken($accessToken);
        if ($token) {
            UserDevice::where('access_token_id', $token->id)->delete();
        }
        $request->user()->currentAccessToken()->delete();

        return $this->ok(['message' => 'success']);
    }

    public function register(RegisterRequest $request): \Illuminate\Http\JsonResponse
    {
        $code = $request->input('code');
        $emailOrMobile = $request->input('mobile') ?? $request->input('email'); //input is validated in AuthRequestCode
        if (AuthRequest::check($emailOrMobile, $code)) {//token become checked in check method
            $user = AuthRequest::getUser($emailOrMobile); //create or get user
            $token = $user->createToken(Str::uuid())->plainTextToken; //create new token
            $tokenModel = PersonalAccessToken::findToken($token);
            UserDevice::create([
                'user_id' => $user->id,
                'access_token_id' => $tokenModel?->id,
                'type' => UserDeviceTypeEnum::tryFrom($request->input('device_os')) ?? UserDeviceTypeEnum::getDefault(),
                'fcm_token' => $request->input('fcm_token'),
                'device_version' => $request->input('device_version'),
                'device_info' => $request->json('device_info', []),
                'ip' => ip(),
            ]);

            return $this->ok([
                'message' => 'success',
                'is_old_user' => false, //todo: change to true if user is old user
                'token' => $token,
                'user' => UserResource::make($user),
            ]);
        }

        return $this->badRequest(data: ['message' => 'کد وارد شده صحیح نمی باشد']);
    }

    public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
    {

        $emailOrMobile = $request->input('mobile') ?? $request->input('email'); //input is validated in AuthRequestCode

        $emailOrMobile = convert2english($emailOrMobile);
        if (filter_var($emailOrMobile, FILTER_VALIDATE_EMAIL)) {
            $filed = 'mobile';
        }
        else if (checkMobileNumber($emailOrMobile)){
            $filed = 'mobile';
        } else {
            return $this->requestException('شماره یا ایمیل وارد شده اشتباه است');
        }
        //check user can send new request
        if (AuthRequest::canRequest($emailOrMobile)) {

            //create new request
            AuthRequest::make($emailOrMobile, ip()); //sms will send in make method

            return $this->created(['message' => 'success']);
        }

        return $this->tooManyRequest(message: 'برای ارسال مجدد باید یک دقیقه صبر کنید');
    }

    public function loginAndRegister(LoginRequest $request): \Illuminate\Http\JsonResponse
    {

        $emailOrMobile = $request->input('mobile') ?? $request->input('email'); //input is validated in AuthRequestCode
        //check user can send new request
        if (AuthRequest::canRequest($emailOrMobile)) {
            //create new request
            AuthRequest::make($emailOrMobile, ip()); //sms will send in make method

            return $this->created(['message' => 'success']);
        }

        return $this->tooManyRequest(message: 'برای ارسال مجدد باید یک دقیقه صبر کنید');
    }

    public function verify(VerifyRequest $request): \Illuminate\Http\JsonResponse
    {
        $code           = $request->input('code');
        $emailOrMobile  = $request->input('mobile') ?? $request->input('email'); //input is validated in AuthRequestCode

        // when request is register new user and statement is empty
        $status = self::STATUS_VERIFY;
        if (in_array("register" , $request->segments())){
            $status = self::STATUS_REGISTER;
            if (!$request->has('statement')){
                return $this->badRequest(data: ['message' => 'اطلاعات کاربر ارسال نشده است']);
            }
        }

        // when request is register new user and statement is empty

        if (AuthRequest::check($emailOrMobile, $code)) {//token become checked in check method
            $user = AuthRequest::getUser($emailOrMobile); //create or get user
            $token = $user->createToken(Str::uuid())->plainTextToken; //create new token
            $tokenModel = PersonalAccessToken::findToken($token);
            UserDevice::create([
                'user_id' => $user->id,
                'access_token_id' => $tokenModel?->id,
                'type' => UserDeviceTypeEnum::tryFrom($request->input('device_os')) ?? UserDeviceTypeEnum::getDefault(),
                'fcm_token' => $request->input('fcm_token'),
                'device_version' => $request->input('device_version'),
                'device_info' => $request->json('device_info', []),
                'ip' => ip(),
            ]);


            // if statement
            if ($request->has('statement')){
                $statement = json_decode($request->input('statement'));
                if (gettype($statement) <> "object"){
                    return $this->badRequest(data: ['message' => 'دیتای ارسالی با فرمت اشتباه ارسال شده است']);
                }

                $listMetas = [];
                foreach (UserMetaEnum::keys() as $key){
                    $fieldKey = strtolower($key->name);
                    if (property_exists($statement , $fieldKey)){
                        $value = $statement->{$fieldKey};
                        //for set route address
                        if ($key == UserMetaEnum::ROUTE){
                            userRoute($user, $value);
                            continue;
                        }
                        //for set route address

                        if (is_array($value) || is_object($value)){
                            $value = json_encode($value , JSON_UNESCAPED_UNICODE);
                        }
                        $listMetas[] = new UserMeta([
                            'meta_key' =>  UserMetaEnum::tryFrom($key->value),
                            'meta_value'    => $value
                        ]);
                    }

                    // insert user diseases
                    if ($request->has('diseases')){
                        $userDiseases = $request->input('diseases');
                        if (count($userDiseases) && is_array($userDiseases)){
                            $user->diseases()->sync($request->input('diseases'));
                        }
                    }
                    // insert user diseases
                }
                if (count($listMetas)){
                    $user->metas()->saveMany($listMetas);
                }
            }
            // if statement

            if ($status == self::STATUS_VERIFY) {

                return $this->ok([
                    'message' => 'success',
                    'is_old_user' => (bool)$user->gender,
                    'token' => $token,
                    'user' => UserResource::make($user),
                ]);
            } else {
                return $this->ok([
                    'message' => 'success',
                    'token' => $token,
                    'user' => UserResource::make($user),
                ]);
            }
        }

        return $this->badRequest(data: ['message' => 'کد وارد شده صحیح نمی باشد']);
    }
}
