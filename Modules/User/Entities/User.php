<?php

namespace Modules\User\Entities;

use Laravel\Sanctum\HasApiTokens;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\Chat\app\Models\Chat;
use Spatie\Permission\Models\Role;
use Modules\User\Enum\UserMetaEnum;
use Spatie\Permission\Traits\HasRoles;
use Modules\Service\app\Models\Service;
use Illuminate\Notifications\Notifiable;
use Modules\User\Traits\UserRelationTrait;
use Modules\User\Traits\MetaAttributeTrait;
use Modules\User\Traits\UserAttributeTrait;
use Illuminate\Database\Eloquent\Collection;
use Modules\User\Database\factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Absence\app\Models\Absence;
use Verta;

/**
 * Modules\User\Entities\User
 *
 * @property int $id
 * @property string|null $mobile
 * @property string|null $email
 * @property mixed $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Diet\Entities\DietRequest> $dietRequests
 * @property-read int|null $diet_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Core\Entities\Disease> $diseases
 * @property-read int|null $diseases_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Exercise\Entities\ExercisePlanRequest> $exercisePlanRequests
 * @property-read int|null $exercise_plan_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\User\Entities\UserMeta> $metas
 * @property-read int|null $metas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $my
 * @property-read int|null $my_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $supporter
 * @property-read int|null $supporter_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read int|null $user_devices_count
 * @method static \Modules\User\Database\factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasRoles, Notifiable, HasFactory, HasApiTokens, UserAttributeTrait, UserRelationTrait;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    protected $with = ['metas'];

    public static function adminSupportRoles(): array
    {
        $roles = [];
        $adminRoles = Role::whereHas('permissions', function ($query) {
            $query->where('name', 'ADMIN_ACCESS');
        })->get();
        foreach ($adminRoles as $adminRole) {
            if ($adminRole->id === 1) {
                continue;
            }
            $roles[$adminRole->id] = $adminRole->name;
        }
        return $roles;
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Specifies the user's FCM token
     *
     * @return string|array
     */
    public function routeNotificationForFcm(): array|string
    {
        return $this->userDevices()->pluck('fcm_token')->toArray();
    }


    public function getMeta(UserMetaEnum $metaKey): ?UserMeta
    {
        return $this->metas->where('meta_key', $metaKey)->last() ?? null;
    }
    public function getMetas(UserMetaEnum $metaKey): ?Collection
    {
        return $this->metas->where('meta_key', $metaKey) ?? null;
    }

    public function metas(): HasMany
    {
        return $this->hasMany(UserMeta::class);
    }

    public function appointmentSettings(): HasMany
    {
        return $this->hasMany(AppointmentSetting::class);
    }


    public function supporter()
    {
        return $this->belongsToMany(User::class, 'user_supports', 'user_id', 'support_id');
    }

    public function my(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_supports', 'support_id', 'user_id');
    }


    public function metaOptionsNames($meta_name): string
    {
        $values =  $this->metaoptionsValues($meta_name);
        if (isset($values) && is_int($values)) {
            return  $this->metaOptionsName($meta_name, $values);
        } elseif (isset($values) && is_array($values)) {
            $options_name = [];
            foreach ($values as $key  =>  $meta_values_option) {
                $options_name[]  =   $this->metaOptionsName($meta_name, $meta_values_option);
            }
            $returned_values = '';
            foreach ($options_name as $key =>  $options_name) {
                $returned_values .= ($key == 0  ? '' : ',') . $options_name;
            };
            return $returned_values;
        }
        return ' ---';
    }

    public function metaoptionsValues($meta_value)
    {

        if (isset($meta_value)) {
            return json_decode($this->$meta_value?->last()?->meta_value, true);
        }
    }
    public function metaOptionsName($meta_type, $metaOptions)
    {

        return  $this->$meta_type?->last()?->meta_key->getOptionName($metaOptions);
    }
    public function service()
    {
        return $this->belongsToMany(Service::class);
    }
    public function absence()
    {
        return $this->hasMany(Absence::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(AppointmentUser::class);
    }
    public static function doctors()
    {
        return Role::find(3)->users;
    }
    public static function doctors_query()
    {
        return Role::find(3)->users();
    }

    public function age(): int
    {
        $birthDayData = $this->birthday;
        if ($birthDayData == null) {
            return 0;
        }
        $birthDay = json_decode($birthDayData);
        $shamsiDate = Verta::parse("{$birthDay->year}/{$birthDay->month}/{$birthDay->day}");
        return $shamsiDate->diff(now())->y;
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

}
