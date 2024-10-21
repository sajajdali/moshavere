<?php

namespace Modules\User\Entities;

use Verta;
use App\Enum\ActiveEnum;
use Laravel\Sanctum\HasApiTokens;
use Modules\Chat\app\Models\Chat;
use Spatie\Permission\Models\Role;
use Modules\Place\app\Models\Place;
use Modules\User\Enum\UserMetaEnum;
use Illuminate\Support\Facades\Cache;
use Modules\Front\app\Models\Province;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Modules\User\Traits\UserRelationTrait;
use Modules\User\Traits\UserAttributeTrait;
use Illuminate\Database\Eloquent\Collection;
use Modules\Transaction\app\Models\Transaction;
use Modules\User\Database\factories\UserFactory;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;

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

    const USER_GENDER_MALE = 'male';
    const USER_GENDER_FEMALE = 'female';

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

    public function userDevices(): HasMany
    {
        return $this->hasMany(\Modules\Api\Entities\UserDevice::class);
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
        return $this->userDevices()->whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
    }

    public function getMeta(UserMetaEnum $metaKey): ?UserMeta
    {
        return $this->metas()->where('meta_key', $metaKey)->get()->last() ?? null;
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

    public function isAdmin()
    {
        return $this->roles()->where('id', 1)->count() > 0;
    }
    public static function doctors()
    {
        return Role::find(3)?->users;
    }
    public static function operators()
    {
        return Role::find(4)?->users;
    }
    public static function doctors_query()
    {
        return Role::find(3)?->users();
    }
    public static function operators_query()
    {
        return Role::find(4)?->users();
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

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
    public function specialServiceseCount()
    {
        return  AppointmentSetting::where('user_id', $this->id)->whereNotNull('service_id')->count();
    }
    public function DocSpecialities(): string
    {
        $specialities = $this->specialities;

        if ($specialities->isNotEmpty()) {
            // Join the speciality titles with a comma and a space
            return $specialities->pluck('title')->implode(' , ');
        }

        return '';
    }
    public function DocProvinces()
    {
        $provinceIds = $this->places
            ->filter(function ($place) {
                return isset($place->detail[Place::DETAIL_PROVINCE]);
            })
            ->pluck('detail.' . Place::DETAIL_PROVINCE)
            ->toArray();
        $provinces = Province::whereIn('id', $provinceIds)->pluck('title')->toArray();
        return implode(',', $provinces);
    }
    public function scopeEmergencyDoctors($query)
    {
        return $query->whereHas('metas', function ($q) {
            $q->where('meta_key', UserMetaEnum::DR_ENEMRGENCY_STATUS)
                ->where('meta_value', true);
        })->whereHas('metas', function ($q) {
            $q->where('meta_key', UserMetaEnum::BAN_USER)
                ->where(function ($qqq) {
                    $qqq->where('meta_value', false)->orWhereNull('meta_value');
                });
        })->whereHas('metas', function ($q) {
            $q->where('meta_key', UserMetaEnum::ACTIVE_APPOINTMENT)
                ->where(function ($qqq) {
                    $qqq->where('meta_value', true);
                });
        });
    }

    public function scopeIntroductionDoctors($query)
    {
        return $query->whereHas('metas', function ($q) {
            $q->where('meta_key', UserMetaEnum::DR_INFO_STATUS)
                ->where('meta_value', true);
        })->whereHas('metas', function ($q) {
            $q->where('meta_key', UserMetaEnum::BAN_USER)
                ->where(function ($qqq) {
                    $qqq->where('meta_value', false)->orWhereNull('meta_value');
                });
        })->whereHas('metas', function ($q) {
            $q->where('meta_key', UserMetaEnum::ACTIVE_APPOINTMENT)
                ->where(function ($qqq) {
                    $qqq->where('meta_value', true);
                });
        });
    }

    public function scopeNewestDocs()
    {
        return $this->doctors_query()
            ->whereHas('metas', function ($q) {
                $q->where('meta_key', UserMetaEnum::BAN_USER)
                    ->where(function ($qqq) {
                        $qqq->where('meta_value', false)->orWhereNull('meta_value');
                    });
            })->whereHas('metas', function ($q) {
                $q->where('meta_key', UserMetaEnum::ACTIVE_APPOINTMENT)
                    ->where(function ($qqq) {
                        $qqq->where('meta_value', true);
                    });
            });
    }
    public function getUserBadge()
    {
        return match ($this->roles->first()->id) {
            1 => 'bg-info',
            2 => 'bg-success',
            3 => 'bg-warning',
            default => 'bg-primary',
        };
    }
    public function IsDoctor()
    {
        if ($this->hasrole('پزشک')) {
            return true;
        } else {
            return false;
        }
    }
    public static function generatePassword()
    {
        $pass =  bin2hex(random_bytes(16));;
        return $pass;
    }
    public static function generateDocumentNumber()
    {
        $documentNumber =  mt_rand(100000, 999999);
        return $documentNumber;
    }
    public function isDoctorActive()
    {
        if (isset($this->ban_user) && $this->ban_user == true) {
            return   false;
        }
        if (isset($this->active_appointment) && $this->active_appointment != true) {
            return false;
        }
        if (!$this->services()->exists()) {
            return  false;
        }
        if (!$this->places()->exists()) {
            return false;
        }
        return true;
    }

    public function scopeNewRegistredDoctor()
    {
        return $this->doctors_query()->whereHas('metas', function ($q) {
            $q->where([
                ['meta_key', UserMetaEnum::BAN_USER],
                ['meta_value', true],
            ]);
        })
            ->orderByDesc('id');
    }
    public function activeServices(): collection
    {
        return $this->services()->where('active', ActiveEnum::ACTIVE)->get();
    }
    public function activePlaces(): collection
    {
        return $this->places()->where('active', ActiveEnum::ACTIVE)->get();
    }
    public function onlineAppointmentNewMessageCount(): int
    {
        return $this->appointmentOnlineMessage()?->unReadedMessageCount() ?? 0;
    }
    public function unReadedMessageCount($query)
    {
        $query->where('seen', \Modules\AppointmentUser\Enum\AppointmentOnlineMessageSeenEnum::UNSEEN);
    }
    public function appointmentOnlineMessage()
    {
        return AppointmentOnline::where('user_id', $this->id)
            ->whereIn(
                'status',
                [
                    AppointmentOnlineStatusEnum::ACCEPTED,
                    AppointmentOnlineStatusEnum::REPLY_BY_USER,
                    AppointmentOnlineStatusEnum::ANSWER_BY_DOCTOR,
                    AppointmentOnlineStatusEnum::REACTIVATED,
                ]
            )->whereHas('messages')
            ->first()?->messages?->first();
    }
    public function onlineAppIdforRoute()
    {
        return AppointmentOnline::where('user_id', $this->id)
            ->whereIn(
                'status',
                [
                    AppointmentOnlineStatusEnum::ACCEPTED,
                    AppointmentOnlineStatusEnum::REPLY_BY_USER,
                    AppointmentOnlineStatusEnum::ANSWER_BY_DOCTOR,
                    AppointmentOnlineStatusEnum::REACTIVATED,
                ]
            )->whereHas('messages')?->first();
    }
}
