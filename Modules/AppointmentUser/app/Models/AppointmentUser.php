<?php

namespace Modules\AppointmentUser\app\Models;

use Carbon\Carbon;
use App\Models\ShortLink;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Illuminate\Database\Eloquent\Model;
use Modules\Service\app\Models\Service;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Transaction\app\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class AppointmentUser extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    const DETAIL_APPOINTMENT_VIA = 'appointment_via';
    const DETAIL_PAYMENT_PRICE = 'price';
    const DETAIL_QUESTION = 'question';
    const DETAIL_DESCRIPTION = 'description';
    const DETAIL_SOMEONE = 'someone';
    const DETAIL_FOR_HIMSELF = 'for_himself';
    const DETAIL_PAYMENT = 'payment';
    const DISAPPROVED_DESCRIPTION = 'disapproved_description';

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $casts = [
        'type' => AppointmentUserTypeEnum::class,
        'kind' => AppointmentUserKindEnum::class,
        'status' => AppointmentUserStatusEnum::class,
        'date_visit' => 'datetime',
        'visited_at' => 'datetime',
        'details' => 'json',
    ];

    public function setting()
    {
        return $this->belongsTo(AppointmentSetting::class, 'appointment_setting_id');
    }

    public static function generateTrackingCode(): string
    {
        do {
            $uniqueCode = generateUniqueCode(8, true);
        } while (static::where('tracking_code', $uniqueCode)->exists());

        // Insert the unique code into the "transaction" table
        return $uniqueCode;
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id', 'id');
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }

    public function shortLink(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(ShortLink::class, 'shortlinkable');
    }

    public function getColor()
    {

        $color =  match ($this->status) {
            AppointmentUserStatusEnum::STATUS_SUCCESSFUL =>   $this->type == AppointmentUserTypeEnum::MAIN__APPOINTMENT ? 'table-success' : "table-info",
            AppointmentUserStatusEnum::STATUS_CANCEL => 'table-danger',
            AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT => 'table-warning',
            AppointmentUserStatusEnum::STATUS_ATTENDED => 'table-secondary',
            AppointmentUserStatusEnum::STATUS_NOT_ATTENDED => 'table-primary',
            AppointmentUserStatusEnum::STATUS_PENDING => 'table-warning',
            AppointmentUserStatusEnum::STATUS_DISAPPROVED => 'table-danger',
            AppointmentUserStatusEnum::STATUS_MONITORING => 'table-warning',
            default => '',
        };
        if ($this->type == AppointmentUserTypeEnum::BETWEEN_PATIENTS) {
            $color = 'table-info';
        }
        return $color;
    }
    public function getbage()
    {
        return match ($this->type) {
            AppointmentUserTypeEnum::MAIN__APPOINTMENT => '',
            AppointmentUserTypeEnum::BETWEEN_PATIENTS => ' <span class="badge bg-primary rounded-pill">بین مریض</span>',
        };
    }

    public function online(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AppointmentOnline::class);
    }

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date_visit', Carbon::today());
    }
    public function scopeState($query,AppointmentUserStatusEnum $appointmentUserStatusEnum)  {
        return $query->where('status' , $appointmentUserStatusEnum);
    }
    public function scopeSuccessful($query)  {
        return $query->state(AppointmentUserStatusEnum::STATUS_SUCCESSFUL);
    }
    public function scopeWaitpayment($query)  {
        return $query->state(AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT);
    }
    public function scopeCanceled($query)  {
        return $query->state(AppointmentUserStatusEnum::STATUS_CANCEL);
    }
    public function scopeDisApproved($query)  {
        return $query->state(AppointmentUserStatusEnum::STATUS_DISAPPROVED);
    }
    public function scopeDisabled($query)  {
        return $query->where('status',AppointmentUserStatusEnum::STATUS_CANCEL)->orWhere('status',AppointmentUserStatusEnum::STATUS_CANCEL);
    }

}
