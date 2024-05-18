<?php

namespace Modules\Api\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Builder;
use Modules\Api\app\Resources\Api\Appointments\AppointmentUserPaginateResource;
use Modules\Api\app\Resources\Api\Appointments\AppointmentUserResource;
use Modules\Api\app\Resources\Api\DoctorResource;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\Api\Transformers\Exercise\ExerciseRequestWithOutDetailResource;
use Modules\Api\Transformers\Notification\NotificationResource;
use Modules\Api\Transformers\Package\PackageUserResource;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\Chat\Enum\ChatStatusEnum;
use Modules\Diet\Enum\DietRequestStatusEnum;
use Modules\Exercise\Enum\ExercisePlanRequestEnum;
use Modules\Package\Enum\PackageTypeEnum;
use Modules\User\Entities\User;

class DashboardController extends Controller
{
    use ApiHandlerTrait;

    private function stories(): array
    {
        return [
            [
                'image' => url('storage/videos/image1.png'),
                'video' => url('storage/videos/video1.mp4'),
            ],
            [
                'image' => url('storage/videos/image2.png'),
                'video' => url('storage/videos/video2.mp4'),
            ]
            ,[
                'image' => url('storage/videos/image3.jpg'),
                'video' => url('storage/videos/video3.mp4'),
            ]
        ];
    }

    private function doctors()
    {
        $doctors = User::doctors_query()->whereHas('appointmentSettings')->get();
        return \Modules\Api\app\Resources\Api\Appointments\DoctorResource::collection($doctors);

    }
    public function index()
    {
        $user = auth()->user();
        $appointmentInPerson = $user->appointments()
            ->whereDate('date_visit' , '>=', Carbon::today())
            ->whereIn('status' ,[ AppointmentUserStatusEnum::STATUS_SUCCESSFUL ,AppointmentUserStatusEnum::STATUS_ATTENDED , AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT , AppointmentUserStatusEnum::STATUS_NOT_ATTENDED])
            ->where('kind' , AppointmentUserKindEnum::IN_PERSION)
            ->orderBy('created_at')
            ->first();

        $appointmentOnline = $user->appointments()
            ->whereIn('status' ,[ AppointmentUserStatusEnum::STATUS_SUCCESSFUL , AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT])
            ->where('kind' , AppointmentUserKindEnum::ONLINE)
            ->whereHas('online' , function (Builder $online) {
                return $online->whereIn('status' , AppointmentOnlineStatusEnum::showInDashboardApi());
            })
            ->orderBy('created_at')
            ->first();
//        dd($appointmentOnline);


        $activeChat = $user->chats()->where('status', '<>', ChatStatusEnum::CLOSED)->where('ban', false)->orderByDesc('id')->first();
        $stories = $this->stories();
        return $this->ok([
            'status' => true,
            'appointments' => [
                'online' => isset($appointmentOnline) ?  AppointmentUserResource::make($appointmentOnline) : null,
                'in_person' => isset($appointmentInPerson) ? AppointmentUserResource::make($appointmentInPerson) : null,
            ],
            'purchased_courses' => [],
            'courses' => [] ,
            'stories' => $stories ,
            'chat_badge' => (isset($activeChat) && (int) $activeChat->sum('new_message_by_support') > 0)  ? (int) $activeChat->sum('new_message_by_support') : null,
            'doctors' => $this->doctors(),
            'news' => [
                [
                    'link' => 'https://drmehrnushamiri.com/%d8%b9%d9%88%d8%a7%d8%b1%d8%b6-ivf-%d8%a8%d8%b1%d8%a7%db%8c-%d8%aa%d8%b9%db%8c%db%8c%d9%86-%d8%ac%d9%86%d8%b3%db%8c%d8%aa/',
                    'date' => '1402/12/11',
                    'title' => "عوارض ivf برای تعیین جنسیت",
                    'body'  => "عوارض ivf برای تعیین جنسیت چیست؟ خطرات ای وی اف تعیین جنسیت برای مادر و جنین چه خطراتی هستند؟ چطور می‌توان عوارض آن را کاهش داد؟ لقاح آزمایشگاهی",
                    'image' => url('storage/news/ivf.jpg'),

                ],
                [
                    'link' => 'https://drmehrnushamiri.com/%d8%af%d8%b1%d9%85%d8%a7%d9%86-%d9%86%d8%a7%d8%b2%d8%a7%db%8c%db%8c-%d8%a8%d8%a7-%d9%87%db%8c%d8%b3%d8%aa%d8%b1%d9%88%d8%b3%da%a9%d9%88%d9%be%db%8c/',
                    'date' => '1402/12/11',
                    'title' => "درمان نازایی با هیستروسکوپی",
                    'body'  => "روش های تشخیصی بسیاری به منظور تشخیص بیماری ها وجود دارد اما پس از بررسی و مشاهده علائم بالینی، سونوگرافی، سونوهیسترو و سونوگرافی",
                    'image' => url('storage/news/ivf1.jpg'),
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
