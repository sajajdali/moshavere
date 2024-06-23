<?php

namespace Modules\Discount\app\Models;

use App\Enum\ActiveEnum;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Service\app\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discount extends Model
{
    use HasFactory;
    const DETAIL_TOTAL_USAGE = 'total_usage';
    const DETAIL_MAXIMUM_USAGE_EACH_USER = 'maximum_usage_each_user';
    const DETAIL_MINIMUM_PRICE = 'minimum_price';
    const DETAIL_MAXIMUM_PRICE = 'maximum_price';
    const DETAIL_DISCOUNT_TYPE = 'discount_type';
    const DETAIL_DISCOUNT_AMOUNT = 'discount_amount';
    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $casts = [
        'service_id' => 'json',
        'doctor_id' => 'json',
        'detail' => 'json',
        'active' => ActiveEnum::class,
        'start_at' => 'date',
        'end_at' => 'date',
    ];

    public function services(): string
    {
        return match ($this->service_id) {
            null => 'تمامی بخش ها',
            default => $this->getServiceName($this->service_id),
        };
    }
    private function getServiceName($service_id): string
    {
        $service_titles = [];
        if (!empty($service_id)) {
            foreach ($service_id as $key => $service) {
                $service_titles[] = Service::find($service)?->title ?? 'سرویس حذف شده است';
            }
            return implode(',', $service_titles);
        }
        return '';
    }
    public function doctors(): string
    {
        return match ($this->doctor_id) {
            null => 'تمامی بخش ها',
            default => $this->getdoctorName($this->doctor_id),
        };
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'discount_user')
            ->withPivot('service_id', 'doctor_id')
            ->withTimestamps();
    }

    private function getdoctorName($doctor_id): string
    {
        $doctor_names = [];
        if (!empty($doctor_id)) {
            foreach ($doctor_id as $key => $doctor) {
                $doctor_names[] = User::find($doctor)?->fullName ?? 'پزشک حذف شده است!';
            }
            return implode(',', $doctor_names);
        }
        return '';
    }
    public function discountCanBeUsed($userId = null, $price = null): bool
    {
        if (
            $this->start_at > \now() &&
            $this->end_at < \now() &&
            $this->detail[Discount::DETAIL_TOTAL_USAGE] < $this->usage_counter

        ) {
            if (isset($userId)) {
                $this->user()->where('user_id', $userId)->exits();
                return false;
            }
            if (isset($price)) {
                if ($this->detail[Discount::DETAIL_MINIMUM_PRICE] < $price) {
                    return false;
                };
                if ($this->detail[Discount::DETAIL_MAXIMUM_PRICE] > $price) {
                    return false;
                };
            }
            return true;
        }
        return false;
    }
    public function discountIssue($userId = null, $price = null): string
    {
        $error = null;
        if ($this->start_at->gt(now())) {
            $error = 'زمان شروع استفاده از این کد تخفیف هنوز آغاز نشده است!';
        }
        if ($this->end_at->lt(now())) {
            $error = 'زمان استفاده از این کد تخفیف به پایان رسیده است!';
        }
        $detail  = $this->detail;
        if ($this->usage_counter >= $detail[Discount::DETAIL_TOTAL_USAGE]) {
            $error = 'تعداد قابل استفاده از این کد به اتمام رسیده است!';
        }
        if (isset($userId)) {
            $this->user()->where('user_id', $userId)->exits();
            $error = 'شما قبلا از این کد تخفیف استفاده کرده اید!';
        }
        if ($this->detail[Discount::DETAIL_MINIMUM_PRICE] < $price) {
            $error = 'مبلغ از حداقل مبلغ قابل استفاده برای این کد تخفیف خارج است!';
        };
        if ($this->detail[Discount::DETAIL_MAXIMUM_PRICE] > $price) {
            $error = 'مبلغ از حداکثر مبلغ قابل استفاده برای این کد تخفیف خارج است!';
        };
        if (isset($error)) {
            return $error;
        }
        return  'لطفا دوباره امتحان کنید';
    }
    public function caculatePrice($price)
    {
        $isPersentage  = $this->detail[Discount::DETAIL_DISCOUNT_TYPE] == 'percentage';
        if ($isPersentage) {
            $caculated_price =  ($price * $this->detail[Discount::DETAIL_DISCOUNT_AMOUNT]) / 100;
        } else {
            $caculated_price =  $price - $this->detail[Discount::DETAIL_DISCOUNT_AMOUNT];
        }
        return $caculated_price;
    }
}
