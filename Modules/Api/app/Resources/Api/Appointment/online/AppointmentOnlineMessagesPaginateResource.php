<?php

namespace Modules\Api\app\Resources\Api\Appointment\online;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\AppointmentUser\app\Models\AppointmentOnlineMessage;

class AppointmentOnlineMessagesPaginateResource extends JsonResource
{
    private function changeStructure(): array
    {
        $messagesCollect = $this['messages'];

        $collect = $messagesCollect->groupBy(function ($message) {
            return verta($message->created_at->toDateString())->format("d F Y");
        });
        $messagesWithDates = [];
        foreach ($collect as $date => $messages) {
            $messagesWithDates[] = [
                'date' => $date,
                'messages' => AppointmentOnlineMessagesResource::collection($messages)
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
            'messageList' => $this->changeStructure(),
            'accessibility' => $this['accessibility'],
            'paginate' => [
                'current_page' => $this['messages']->currentPage(),
                'per_page' => $this['messages']->perPage(),
                'total' => $this['messages']->total(),
                'last_page' => $this['messages']->lastPage()
            ],
        ];
    }
}
