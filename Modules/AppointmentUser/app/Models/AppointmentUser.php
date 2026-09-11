<?php

namespace Modules\AppointmentUser\app\Models;

use Carbon\Carbon;
use App\Models\ShortLink;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Illuminate\Database\Query\Builder;
use Modules\Front\app\Models\FeedBack;
use Illuminate\Database\Eloquent\Model;
use Modules\Service\app\Models\Service;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Transaction\app\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentSetting\app\Models\AppointmentSegmentItem;

class AppointmentUser extends Model
{

    use HasFactory, SoftDeletes, Notifiable;
    const DETAIL_APPOINTMENT_VIA = 'appointment_via';
    const DETAIL_SURVEY = 'SURVEY';
    const DETAIL_SURVEY_FEEDBACK_FILE = 'SURVEY_FEEDBACK_FILE';
    const STORE_FROM_APPLICATION = 'store_from_application';
    const DETAIL_PAYMENT_PRICE = 'price';
    const DETAIL_PAYMENT_PRICE_SOURCE = 'price_source';
    const DETAIL_PAYMENT_SOURCE_GENERAL = 'general_price';
    const DETAIL_PAYMENT_SOURCE_ADMIN_PANEL = 'admin_panel_price';
    const DETAIL_PAYMENT_SETTING_SNAPSHOT = 'setting_snapshot';
    const DETAIL_QUESTION = 'question';
    const DETAIL_DESCRIPTION = 'description';
    const DETAIL_SOMEONE = 'someone';
    const DETAIL_FOR_HIMSELF = 'for_himself';
    const DETAIL_PAYMENT = 'payment';
    const DETAIL_SEGMENTS = 'segments';
    const USER_MODEL = 'user_model';
    const DISAPPROVED_DESCRIPTION = 'disapproved_description';

    //تنظیمات ثبت حضور و یا عدم حضور بیمار در مطب
    const USRE_ATTENDED_STATUS = 'user_attenede_status';
    const PENDING_APPOINTMENT_BY_SECRETERY = 'pendding_appointment_by_secretery';

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
        'deadline_at' => 'datetime',
        'details' => 'json',
    ];

    public function hasFinalizedPatientNoShow(): bool
    {
        return \Modules\OnlineConsultation\Support\ConsultationAccess::schemaReady(['appointment_consultation_cases'])
            && $this->consultationCase?->state === 'PATIENT_NO_SHOW';
    }

    public function hasCompletedPhoneConsultation(): bool
    {
        return $this->kind === AppointmentUserKindEnum::VOIP
            && \Modules\OnlineConsultation\Support\ConsultationAccess::schemaReady(['appointment_consultation_cases'])
            && $this->consultationCase?->state === \Modules\OnlineConsultation\Models\AppointmentConsultationCase::STATE_COMPLETED;
    }

    public function save(array $options = [])
    {
        if (! $this->exists || ! $this->isDirty(['status', 'kind', 'user_id', 'doctor_id', 'date_visit', 'start_time', 'end_time'])) {
            return parent::save($options);
        }
        // Serialize cancellation/rescheduling against final attendance and financial confirmation.
        return \Illuminate\Support\Facades\DB::transaction(function () use ($options) {
            static::withTrashed()->lockForUpdate()->findOrFail($this->getKey());
            return parent::save($options);
        });
    }

    public function delete()
    {
        if (! $this->exists) return parent::delete();
        return \Illuminate\Support\Facades\DB::transaction(function () {
            static::withTrashed()->lockForUpdate()->findOrFail($this->getKey());
            $this->unsetRelation('consultationCase');
            return parent::delete();
        });
    }

    protected static function booted(): void
    {
        static::updating(function (self $appointment) {
            if ($appointment->isDirty(['status', 'kind', 'user_id', 'doctor_id', 'date_visit', 'start_time', 'end_time'])
                && $appointment->fresh()?->hasFinalizedPatientNoShow()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['appointment' => 'این نوبت بابت عدم حضور بیمار تسویه قطعی شده است؛ تغییر مشخصات، زمان یا لغو آن مجاز نیست.']);
            }
        });
        static::deleting(function (self $appointment) {
            if ($appointment->hasFinalizedPatientNoShow()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['appointment' => 'نوبت تسویه‌شده بابت عدم حضور بیمار برای حفظ سوابق مالی قابل حذف نیست.']);
            }
        });
    }

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

    public function appointmentVia(): ?AppointmentVia
    {
        $value = data_get($this->details, self::DETAIL_APPOINTMENT_VIA);

        if ($value instanceof AppointmentVia) {
            return $value;
        }

        return is_numeric($value) ? AppointmentVia::tryFrom((int) $value) : null;
    }

    public function isStoredFromVoip(): bool
    {
        return $this->appointmentVia() === AppointmentVia::VOIP;
    }

    public function surveyVoiceUrl(): ?string
    {
        $filename = data_get(
            $this->details,
            self::DETAIL_SURVEY . '.' . self::DETAIL_SURVEY_FEEDBACK_FILE
        );

        if (! is_string($filename) || trim($filename) === '') {
            return null;
        }

        $filename = basename(str_replace('\\', '/', trim($filename)));
        if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'wav') {
            return null;
        }

        return url('uploads/voip/' . rawurlencode($filename));
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

    public function consultationSmsDeliveries()
    {
        return $this->hasMany(\Modules\OnlineConsultation\Models\ConsultationSmsDelivery::class, 'appointment_id');
    }

    public function consultationCase()
    {
        return $this->hasOne(\Modules\OnlineConsultation\Models\AppointmentConsultationCase::class, 'appointment_id');
    }

    public function consultationReports()
    {
        return $this->hasMany(\Modules\OnlineConsultation\Models\AppointmentConsultationReport::class, 'appointment_id')->latest();
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

    public function ensureShortLink(): ShortLink
    {
        $shortLink = $this->shortLink()->first();

        if ($shortLink) {
            return $shortLink;
        }

        return $this->shortLink()->create([
            'link_code' => ShortLink::generateShortLinkCode(),
            'link_url' => route('front.setAppointment.detail', ['tracking_code' => $this->tracking_code]),
        ]);
    }

    public function shortLinkUrl(bool $tenantUrl = true): string
    {
        $path = '/s/'.$this->ensureShortLink()->link_code;

        return $tenantUrl ? tenant_url($path) : url($path);
    }

    public function getColor()
    {

        $color =  match ($this->status) {
            AppointmentUserStatusEnum::STATUS_SUCCESSFUL =>   $this->type == AppointmentUserTypeEnum::MAIN__APPOINTMENT ? 'table-success' : "table-info",
            AppointmentUserStatusEnum::STATUS_CANCEL => 'table-danger',
            AppointmentUserStatusEnum::STATUS_ONILNE_CLOSED => 'table-danger',
            AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT => 'table-warning',
            AppointmentUserStatusEnum::STATUS_ATTENDED => 'table-secondary',
            AppointmentUserStatusEnum::STATUS_NOT_ATTENDED => 'table-primary',
            AppointmentUserStatusEnum::STATUS_PENDING => 'table-warning',
            AppointmentUserStatusEnum::STATUS_DISAPPROVED => 'table-danger',
            AppointmentUserStatusEnum::STATUS_MONITORING => 'table-warning',
            default => 'table-danger',
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

    public function hasAgent(): bool
    {
        $registered_by_user_id =  $this->online()->first()?->details;
        if (
            isset($registered_by_user_id) &&
            isset($registered_by_user_id[AppointmentOnline::COFRIM_OR_REJECT_STATUS]) && isset($registered_by_user_id[AppointmentOnline::COFRIM_OR_REJECT_STATUS][AppointmentOnline::BY])
        ) {
            return true;
        }
        return false;
    }
    public function confirm_or_reject_by(): string
    {
        $registered_by_user_id =  $this->online()->first()?->details;
        if ($this->hasAgent()) {
            return 'تعیین وضعیت: ' . User::find($registered_by_user_id[AppointmentOnline::COFRIM_OR_REJECT_STATUS][AppointmentOnline::BY])->fullName;
        }
        return '';
    }
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date_visit', Carbon::today());
    }
    public function scopeState($query, AppointmentUserStatusEnum $appointmentUserStatusEnum)
    {
        return $query->where('status', $appointmentUserStatusEnum);
    }
    public function scopeSuccessful($query)
    {
        return $query->state(AppointmentUserStatusEnum::STATUS_SUCCESSFUL);
    }
    public function scopeWaitpayment($query)
    {
        return $query->state(AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT);
    }
    public function scopeCanceled($query)
    {
        return $query->state(AppointmentUserStatusEnum::STATUS_CANCEL);
    }
    public function scopeDisApproved($query)
    {
        return $query->state(AppointmentUserStatusEnum::STATUS_DISAPPROVED);
    }
    public function scopeDisabled($query)
    {
        return $query->where('status', AppointmentUserStatusEnum::STATUS_CANCEL)->orWhere('status', AppointmentUserStatusEnum::STATUS_CANCEL);
    }
    public function scopeDoctorPermittion($query)
    {
        if (! auth()->user()->isAdmin() && auth()->user()->can('appointment_user.own')) {
            return $query->where('doctor_id', auth()->user()->id);
        } else {
            return;
        }
    }
    public function scopeactiveAppointmentStatus($query)
    {
        return $query->whereNotIn('status', [
            AppointmentUserStatusEnum::STATUS_CANCEL,
            AppointmentUserStatusEnum::STATUS_DISAPPROVED,
        ]);
    }
    public function scopeOnlineAppointment($query)
    {
        return $this->where('kind', AppointmentUserKindEnum::ONLINE);
    }


    public function attendedStatus()
    {
        if (isset($this->details[self::USRE_ATTENDED_STATUS])) {
            if ($this->details[self::USRE_ATTENDED_STATUS]) {
                return ' <small class="badge bg-success rounded-pill">
                حضور
            </small>';
            } else {
                return ' <small class="badge bg-info rounded-pill">
                عدم حضور
            </small>';
            }
        } else {
            return ' <small class="badge bg-light rounded-pill">
            <i class="fa fa-minus-square me-1" aria-hidden="true"></i>
            نامشخص
        </small>';
        }
    }
    public function feedbacks()
    {
        return $this->hasMany(FeedBack::class);
    }
    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function callLogs()
    {
        return $this->hasMany(\Modules\OnlineConsultation\Models\AppointmentCallLog::class, 'appointment_id');
    }

    public function billingRecord()
    {
        return $this->hasOne(\Modules\OnlineConsultation\Models\AppointmentBillingRecord::class, 'appointment_id');
    }
    public function segmentItems()
    {
        return $this->hasMany(AppointmentSegmentItem::class);
    }
    public function getUnseenMessageBadge(): int
    {
        if ($this->kind == AppointmentUserKindEnum::ONLINE) {
            return $this->online->first()?->messages?->first()?->unReadedMessageCount() ?? 0;
        }
        return 0;
    }
    public function isAppForothers(): bool
    {
        if (
            $this->user?->id != $this->agent?->id &&
            $this->agent?->Hasrole('بیمار')
        ) {
            return true;
        }
        return false;
    }
    public function checkForRegisterForOthers()
    {
        // if appointment set for others and no mobile set for pation .
        if ($this->isAppForothers()) {
            return $this->agent->mobile;
        }
    }
    public function isOnline(): bool
    {
        if ($this->kind == AppointmentUserKindEnum::ONLINE) {
            return  true;
        }
        return false;
    }
    public function isAppActive(): bool
    {
        if ($this->status == AppointmentUserStatusEnum::STATUS_SUCCESSFUL) {
            return true;
        }
        return  false;
    }
    public function hasSegment()
    {
        if (isset($this->details[AppointmentUser::DETAIL_SEGMENTS])) {
            return true;
        }
        return false;
    }
    public function segmentsNames()
    {
        $segNames = '';
        if (isset($this->details[AppointmentUser::DETAIL_SEGMENTS])) {
            foreach ($this->details[AppointmentUser::DETAIL_SEGMENTS] as $segs) {
                $segNames .= ' - ' . $segs['title'];
            }
        }
        return $segNames;
    }
    public function getAppDescription(): string
    {
        if (isset($this->details[self::DETAIL_DESCRIPTION])) {
            return $this->details[self::DETAIL_DESCRIPTION];
        }
        return  '-';
    }
}
