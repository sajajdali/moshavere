<?php

namespace Modules\Api\app\Resources\Api\Chat;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'chat_id' => $this->chat->id,
            'body'=> $this->content,
            'type' => $this->type->apiResult(),
            'seen'=> true,
            'answer_by' => null,
            'created_at' => verta($this->created_at)->format('H:i'),
            'files' => ChatDetailFilesResource::collection( $this->files)
        ];
    }
}
