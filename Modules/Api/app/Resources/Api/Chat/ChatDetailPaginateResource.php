<?php

namespace Modules\Api\app\Resources\Api\Chat;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatDetailPaginateResource extends JsonResource
{
    private function changeStructure(): array
    {
        $messagesCollect = $this;

        $collect = $messagesCollect->groupBy(function ($message) {
            return verta($message->created_at->toDateString())->format("d F Y");
        });
        $messagesWithDates = [];
        foreach ($collect as $date => $messages) {
            $messagesWithDates[] = [
                'date' => $date,
                'messages' => ChatDetailResource::collection($this)
            ];
        }

        return $messagesWithDates;
    }
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'message_list' => $this->changeStructure(),
            'paginate' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage()
            ],
        ];
    }
}
