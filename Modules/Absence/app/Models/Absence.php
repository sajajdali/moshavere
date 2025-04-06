<?php

namespace Modules\Absence\app\Models;

use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Service\app\Models\Service;

class Absence extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];


    public function user() :BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function service() :BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function checkForService() {
        if($this->service_id == null) {
            return '<span class="badge bg-success ">همه بخش ها</span>';
        }else {
            return '<span class="badge bg-info">' . $this->service->title . '</span>' ;
        }
    }
}
