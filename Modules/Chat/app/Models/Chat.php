<?php

namespace Modules\Chat\app\Models;

use App\Models\ShortLink;
use Modules\User\Entities\User;
use Modules\Chat\Enum\ChatStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Modules\Chat\app\Models\ChatDetail;
use Modules\Chat\Database\factories\ChatFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $table = 'chats';
    const DETAIL_QUESTION = 'question';

    protected $casts = [
        'status' => ChatStatusEnum::class,
        'ban' => 'boolean',
        'detail' => 'json'
    ];

    protected $with = ['user', 'chatDetails'];

    public static function getRoom($user_id) : Chat
    {
        $chat = Chat::where('user_id', $user_id)->first();
        if ($chat == null) {
            $chat = Chat::create([
                'user_id' => $user_id,
                'status' => ChatStatusEnum::JUST_CREATED,
                'new_message_by_user'=> 0,
                'new_message_by_support'=> 0,
            ]);
        }else{
            $chat->new_message_by_support = 0;
            $chat->save();
        }
        return $chat;
    }

    public static function badgeCount()
    {
        return self::whereIn('status',[ChatStatusEnum::JUST_CREATED,ChatStatusEnum::USER_SEND_QUESTION])->count();
    }

    protected static function newFactory(): ChatFactory
    {
        //return ChatFactory::new();
    }

    public function chatDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChatDetail::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLatestMessageAgoAttribute(): ?string
    {
        return $this->chatDetails->last()?->created_at->diffForHumans();
    }

    public function getLatestMessageExcerptAttribute(): ?string
    {
        //get excerpt of latest message
        return $this->chatDetails->last()?->content;
    }
    public function shortLink(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {related:
        return $this->morphOne(ShortLink::class, 'shortlinkable');
    }

}
