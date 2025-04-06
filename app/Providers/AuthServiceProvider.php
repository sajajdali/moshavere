<?php

namespace App\Providers;

use Modules\User\Entities\User;
use Modules\Chat\app\Models\Chat;
use Modules\Front\app\Models\Faq;
use Spatie\Permission\Models\Role;
use Modules\Place\app\Models\Place;
use Illuminate\Support\Facades\Gate;
use Modules\Setting\Entities\Setting;
use Modules\User\Policies\RolePolicy;
use Modules\User\Policies\UserPolicy;
use Modules\Absence\app\Models\Absence;
use Modules\Service\app\Models\Service;
use Modules\Chat\app\Policies\ChatPolicy;
use Modules\Discount\app\Models\Discount;
use Modules\Front\app\Policies\FaqPolicy;
use Modules\Reminder\app\Models\Reminder;
use Modules\Place\app\Policies\PlacePolicy;
use Modules\Setting\Policies\SettingPolicy;
use Modules\Speciality\app\Models\Speciality;
use Modules\Absence\app\Policies\AbsencePolicy;
use Modules\Service\app\Policies\ServicePolicy;
use Modules\Discount\app\Policies\DiscountPolicy;
use Modules\Reminder\app\Policies\ReminderPolicy;
use Modules\Speciality\app\Policies\SpecialityPolicy;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\app\Policies\AppointmentUserPolicy;
use Modules\AppointmentSetting\app\Policies\AppointmentSettingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Role::class => RolePolicy::class,
        User::class => UserPolicy::class,
        Setting::class => SettingPolicy::class,
        Reminder::class => ReminderPolicy::class,
        Place::class => PlacePolicy::class,
        Service::class => ServicePolicy::class,
        Speciality::class => SpecialityPolicy::class ,
        AppointmentUser::class => AppointmentUserPolicy::class ,
        AppointmentSetting::class => AppointmentSettingPolicy::class ,
        Absence::class => AbsencePolicy::class ,
        Chat::class => ChatPolicy::class ,
        Discount::class => DiscountPolicy::class ,
        Faq::class => FaqPolicy::class ,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(static function ($user, $ability) {
            if ($user->hasPermissionTo('SUPER_ADMIN')) {
                return true;
            }

            return null;
        });
    }
}
