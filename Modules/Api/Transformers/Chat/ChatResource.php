<?php

namespace Modules\Api\Transformers\Chat;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        //get user chat details grouped by date and add paginate
        $chatDetails = $this->chatDetails->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        });
        $chatItems = null;
        foreach ($chatDetails as $date => $itemInDate){
            $timePassed = Carbon::parse($date)->diffForHumans();
            $chatItems[$timePassed] = ChatDetailResource::collection($itemInDate);
        }
        return [
            'id' => $this->id,
            'is_banned' => $this->ban,
            'chats' => $chatItems,
        ];
    }
}
