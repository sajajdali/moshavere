<?php

namespace Modules\Api\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\Api\app\Resources\Api\Appointments\AppointmentUserPaginateResource;
use Modules\Api\app\Resources\Api\Appointments\AppointmentUserResource;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\Api\Transformers\Exercise\ExerciseRequestWithOutDetailResource;
use Modules\Api\Transformers\Notification\NotificationResource;
use Modules\Api\Transformers\Package\PackageUserResource;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\Diet\Enum\DietRequestStatusEnum;
use Modules\Exercise\Enum\ExercisePlanRequestEnum;
use Modules\Package\Enum\PackageTypeEnum;
use Modules\User\Entities\User;

class DashboardController extends Controller
{
    use ApiHandlerTrait;

    public function index()
    {
        $user = auth()->user();
        $appointments = $user->appointments()->whereDate('date_visit' , '>=', Carbon::today())->get();
        return $this->ok([
            'status' => true,
            'appointments' => AppointmentUserResource::collection($appointments),
            'purchased_courses' => [],
            'courses' => [] ,
            'news' => [
                [
                    'link' => '',
                    'date' => '1402/12/11',
                    'title' => "رژیم غذایی تخمدان پلی کیستیک",
                    'body'  => "PCOS و رژیم غذایی: 7 نکته کلیدی در رژیم غذایی تخمدان پلی کیستیک سندروم تخمدان پلی کیستیک (PCOS) یک اختلال",
                    'image' => "https://jesmino.com/wp-content/uploads/2024/02/سندرم-پلی-کیستیک-300x191.jpg"
                ],
                [
                    'link' => '',
                    'date' => '1402/12/11',
                    'title' => "رژیم غذایی تخمدان پلی کیستیک",
                    'body'  => "PCOS و رژیم غذایی: 7 نکته کلیدی در رژیم غذایی تخمدان پلی کیستیک سندروم تخمدان پلی کیستیک (PCOS) یک اختلال",
                    'image' => "https://jesmino.com/wp-content/uploads/2024/02/سندرم-پلی-کیستیک-300x191.jpg"
                ]
            ]
        ]);
    }

    public function appointmentList()
    {
        $type = request()->has('type') ? request()->get('type') : 1;
        $kind = $type == "2" ? AppointmentUserKindEnum::ONLINE : AppointmentUserKindEnum::IN_PERSION;
        $user = auth()->user();
        $appointmentListInPerson = $user->appointments()
            ->whereIn('status' ,[ AppointmentUserStatusEnum::STATUS_SUCCESSFUL ,AppointmentUserStatusEnum::STATUS_ATTENDED , AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT , AppointmentUserStatusEnum::STATUS_NOT_ATTENDED])
            ->where('kind' , $kind->value)
            ->orderByDesc('date_visit')->paginate()
        ;

        return $this->ok(new AppointmentUserPaginateResource($appointmentListInPerson));

    }

}
