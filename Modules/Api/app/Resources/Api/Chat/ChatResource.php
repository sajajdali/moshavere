<?php

namespace Modules\Api\app\Resources\Api\Chat;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Api\Transformers\UserResource;
use Modules\Chat\Enum\ChatStatusEnum;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => UserResource::make($this->user),
            'status' => $this->status->apiResult(),
            'new_message_by_user' => $this->new_message_by_user,
            'new_message_by_support' => $this->new_message_by_support,
            'can_send_message' => $this->ban == false || $this->status != ChatStatusEnum::CLOSED,
            'ban' => $this->ban,
            'created_at' => dateFormatComplete($this->created_at)
        ];
    }
}
