<?php

namespace Modules\Api\app\Resources\Api\Appointment\online;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\AppointmentUser\app\Models\AppointmentOnlineMessage;

class AppointmentOnlineMessagesPaginateResource extends JsonResource
{
    private function changeStructure(): array
    {
        $collect =  $this->groupBy(function ($message) {
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
            'paginate' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage()
            ],
        ];
    }
}
