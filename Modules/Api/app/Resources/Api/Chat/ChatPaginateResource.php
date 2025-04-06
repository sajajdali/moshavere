<?php

namespace Modules\Api\app\Resources\Api\Chat;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatPaginateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'chats' => ChatResource::collection($this),
            'paginate' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage()
            ],
        ];
    }
}
