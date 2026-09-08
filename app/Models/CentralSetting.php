<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentralSetting extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'support_renew_cost' => 'integer',
        'server_renew_cost' => 'integer',
    ];

    public function getConnectionName()
    {
        return config('tenancy.database.central_connection');
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(['id' => 1], [
            'support_renew_cost' => 0,
            'server_renew_cost' => (int) config('app.tenant_renew_cost', 0),
        ]);
    }
}
