<?php

namespace Modules\Finance\app\Models;

use Illuminate\Database\Eloquent\Model;

class FinancePaymentPurpose extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean'];

    public function payments()
    {
        return $this->hasMany(FinancePayment::class, 'purpose_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }
}
