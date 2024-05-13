<?php

namespace Modules\Discount\app\Models;

use App\Enum\ActiveEnum;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Service\app\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Discount\Database\factories\DiscountFactory;

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
                $service_titles[] = Service::find($service)->title;
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
    private function getdoctorName($doctor_id): string
    {
        $doctor_names = [];
        if (!empty($doctor_id)) {
            foreach ($doctor_id as $key => $doctor) {
                $doctor_names[] = User::find($doctor)->fullName;
            }
            return implode(',', $doctor_names);
        }
        return '';
    }
    public function discountCanBeUsed($user)
    {
        if (
            $this->start_at > \now() &&
            $this->end_at < \now() &&
            $this->detail[Discount::DETAIL_TOTAL_USAGE] < $this->usage_counter
        ) {
            return true;
        }
        return false;
    }
}
