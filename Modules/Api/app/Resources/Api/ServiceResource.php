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
        if ($this->id == 1){
            return  [
                'question' => 'در هفته چندم بارداری هستید' ,
                'options' => [
                    [
                        'id' => 1,
                        'title' => 'سه ماهه اول',
                    ],
                    [
                        'id' => 2,
                        'title' => 'سه ماهه دوم'
                    ],
                    [
                        'id' => 4,
                        'title' => 'سه ماهه سوم'
                    ],
                    // [
                    //     'id' => 5,
                    //     'title' => 'هفته پنجم بارداری'
                    // ],
                ]
            ];
        }
        return null;
    }
}
