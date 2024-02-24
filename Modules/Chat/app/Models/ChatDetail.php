<?php

namespace Modules\Chat\app\Models;

use Modules\User\Entities\User;
use Modules\Chat\app\Models\Chat;
use Illuminate\Database\Eloquent\Model;
use Modules\Chat\Enum\ChatDetailTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Chat\Database\factories\ChatDetailFactory;

class ChatDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $guarded = ['id'];

    protected $casts = [
        'type' => ChatDetailTypeEnum::class,
    ];

    public function chat(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIsQuestionAttribute(): bool
    {
        return $this->user_id === $this->chat?->user_id;
    }

    public function getIsAnswerAttribute(): bool
    {
        return $this->user_id !== $this->chat?->user_id;
    }

    public function getIsMessageAttribute(): bool
    {
        return $this->type === ChatDetailTypeEnum::MESSAGE;
    }

    public function getIsAttachAttribute(): bool
    {
        return $this->type === ChatDetailTypeEnum::ATTACH;
    }

}
