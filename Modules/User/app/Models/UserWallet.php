<?php

namespace Modules\User\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use Modules\User\Enum\UserWalletTypeEnum;
// use Modules\User\Database\Factories\UserWalletFactory;

class UserWallet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $casts = ['type' => UserWalletTypeEnum::class, 'detail' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
