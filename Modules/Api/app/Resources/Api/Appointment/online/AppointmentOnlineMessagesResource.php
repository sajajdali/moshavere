<?php

namespace Modules\Api\app\Resources\Api\Appointment\online;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Api\Transformers\UserResource;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;

class AppointmentOnlineMessagesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'body' => $this->body,
            'type' => $this->type->apiResult(),
            'seen' => $this->seen == 1,
            'answer_by' => $this->answer_by ? UserResource::make($this->answerBy) : null,
            'created_at' => dateFormatComplete($this->created_at),
            'files' => AppointmentOnlineMessagesFilesResource::collection($this->messageFile),

        ];
    }
}
