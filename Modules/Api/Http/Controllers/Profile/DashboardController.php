<?php

namespace Modules\Api\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\Api\app\Resources\Api\Appointments\AppointmentUserResource;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\Api\Transformers\Exercise\ExerciseRequestWithOutDetailResource;
use Modules\Api\Transformers\Notification\NotificationResource;
use Modules\Api\Transformers\Package\PackageUserResource;
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
        ]);
    }

}
