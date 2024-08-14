<?php

namespace Modules\Api\app\Resources\Api\Appointment\online;

use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

class AppointmentOnlineMessagesFilesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'original_name' => $this->original_name,
            'file_address' => url(Storage::url( $this->disk . '/' . $this->path)),
            'extension' => $this->extension,
            'mime' => $this->mime,
            'size' => formatBytes($this->size),
            'created_at' =>dateFormatComplete($this->created_at),
        ];
    }
}
