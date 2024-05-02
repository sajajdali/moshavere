<?php


//user routes
Route::get('user', \Modules\User\Livewire\Admin\User\UserList::class)->name('user.index')->can('viewAny', \Modules\User\Entities\User::class);
Route::get('user/create', \Modules\User\Livewire\Admin\User\UserCreateOrUpdate::class)->name('user.create')->can('create', \Modules\User\Entities\User::class);
Route::get('user/edit/{user}', \Modules\User\Livewire\Admin\User\UserCreateOrUpdate::class)->name('user.edit');//access should check in liviwire component edit part
Route::get('user/documents/{user}', \Modules\User\Livewire\Admin\User\UserDocument\Index::class)->name('user.document');
// doctor profile info
Route::get('user/doctor/info/{user}', \Modules\User\Livewire\Admin\User\DoctorInfo\UpdateOrCreate::class)->name('doctor.info')->can('viewAny', \Modules\User\Entities\User::class);
Route::get('user/doctor/gallery/{user}', \Modules\User\Livewire\Admin\User\DoctorGallery\UpdateOrCreate::class)->name('doctor.gallery')->can('viewAny', \Modules\User\Entities\User::class);
//role routes
Route::get('role', \Modules\User\Livewire\Admin\Role\RoleList::class)->name('role.index')->can('viewAny', \Spatie\Permission\Models\Role::class);
Route::get('role/create', \Modules\User\Livewire\Admin\Role\RoleCreateOrUpdate::class)->name('role.create')->can('create', \Spatie\Permission\Models\Role::class);
Route::get('role/edit/{role}', \Modules\User\Livewire\Admin\Role\RoleCreateOrUpdate::class)->name('role.edit');//access should check in liviwire component edit part
