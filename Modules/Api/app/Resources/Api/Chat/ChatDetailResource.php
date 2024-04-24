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
            'body'=> $this->content,
            'type' => $this->type->apiResult(),
            'seen'=> true,
            'answer_by' => null,
            'created_at' => dateFormat($this->created_at),
            'files' => ChatDetailFilesResource::collection( $this->files)
        ];
    }
}
