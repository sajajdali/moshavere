<?php

namespace Modules\AppointmentSetting\app\Policies;

use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class AppointmentSettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('AppointmentSetting');
    }

    public function update(User $user, AppointmentSetting $appointmentSetting): bool
    {
        return $user->hasPermissionTo('AppointmentSetting.edit');
    }

}
