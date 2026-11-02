<?php

namespace Modules\service\app\Policies;

use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\service\app\Models\service;

class servicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('service') || $this->editContent($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('service.create');
    }

    // مسیرها با کلاس (نه نمونه) چک می شوند، پس مدل اختیاری است
    public function delete(User $user, ?Service $service = null): bool
    {
        return $user->hasPermissionTo('service.delete');
    }

    public function update(User $user, ?Service $service = null): bool
    {
        return $user->hasPermissionTo('service.update');
    }

    /**
     * توضیحات و تصویر صفحه بخش در سایت: همه کاربران دارای دسترسی مدیریت.
     * سایر فیلدهای بخش همچنان به «ویرایش بخش» نیاز دارند.
     */
    public function editContent(User $user, ?Service $service = null): bool
    {
        return $user->checkPermissionTo('ADMIN_ACCESS') || $user->checkPermissionTo('service.update');
    }
}
