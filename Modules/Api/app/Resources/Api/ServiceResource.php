<?php

namespace Modules\Api\app\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'priority'  => $this->priority,
            'icon'      => $this->icon ?? url('default/avatar.png'),
            'created_at'    => dateFormat($this->creted_at),
            'questions' => $this->questionList()
        ];
    }

    private function questionList()
    {
        if ($this->id == 2){
            return  [
                [
                    'id' => 1,
                    'title' => 'هفته دوم بارداری',
                ],
                [
                    'id' => 2,
                    'title' => 'هفته سوم بارداری'
                ],
                [
                    'id' => 4,
                    'title' => 'هفته چهارم بارداری'
                ],
                [
                    'id' => 5,
                    'title' => 'هفته پنجم بارداری'
                ],
            ];
        }
        return [];
    }
}
