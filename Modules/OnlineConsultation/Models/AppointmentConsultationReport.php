<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\User\Entities\User;

class AppointmentConsultationReport extends Model
{
    public const OUTCOMES = [
        'SUCCESSFUL' => 'مشاوره با موفقیت انجام شد',
        'FOLLOW_UP_REQUIRED' => 'نیازمند پیگیری یا جلسه بعدی',
        'PRESCRIPTION_PROVIDED' => 'توصیه یا نسخه ارائه شد',
        'REFERRED' => 'ارجاع به متخصص یا مرکز دیگر',
        'PATIENT_NO_ANSWER' => 'بیمار پاسخ نداد',
        'CONSULTANT_NO_ANSWER' => 'مشاور پاسخ نداد',
        'CANCELLED' => 'مشاوره لغو شد',
        'OTHER' => 'سایر موارد',
    ];

    protected $guarded = ['id'];
    protected $casts = ['follow_up_at' => 'datetime'];

    public function consultationCase() { return $this->belongsTo(AppointmentConsultationCase::class, 'case_id'); }
    public function appointment() { return $this->belongsTo(AppointmentUser::class); }
    public function author() { return $this->belongsTo(User::class, 'author_id'); }
}
