<?php

namespace Modules\Front\app\Models;

use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Front\Database\factories\ContactusFactory;

class Contactus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $table = 'contact_us';
    protected $casts = ['detail' => 'json'];
    const DETAIL_REPORT_HANDEL = 'report_handel';


    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function getButtonColor(): string
    {
        if (isset($this->detail[self::DETAIL_REPORT_HANDEL])) {
            return match ($this->detail[self::DETAIL_REPORT_HANDEL]) {
                "true" => 'btn-success',
                "false" => 'btn-danger',
                default => 'btn-info',
            };
        }
        return 'btn-info';
    }
    public function getStatusName(): string
    {
        if (isset($this->detail[self::DETAIL_REPORT_HANDEL])) {
            return match ($this->detail[self::DETAIL_REPORT_HANDEL]) {
                "true" => 'بررسی شده',
                "false" => 'بررسی شده',
                default => 'بررسی نشده',
            };
        }
        return 'بررسی نشده';
    }
    public function getUserName() {
        if(isset($this->user)) {
            return $this->user->full_name ;
        }
        return $this->name ;
    }
    public function mobileNumber():string {
        if($this->mobile) {
            return $this->mobile ;
        }elseif($this->user) {
            return $this->user->mobile ;
        }
        return '';
    }
}
