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
            'chat_id'=> $this->chat_id,
            'type' => $this->type->apiResult(),
            'content'=> $this->content,
            'created_at' => dateFormat($this->created_at),
            'files' => ChatDetailFilesResource::collection( $this->files)
        ];
    }
}
