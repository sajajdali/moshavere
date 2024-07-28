<?php

namespace Modules\Api\app\Resources\Api;

use Modules\Service\app\Models\Service;
use Modules\Api\Enum\ServiceQuestionEnum;
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
        if ($this->id == 1) {
              $returnService =  [
                  'question' => 'در هفته چندم بارداری هستید',
                  'options' => [],
                  ];
              foreach (ServiceQuestionEnum::cases() as $questionEnum) {
                  $returnService['options'][] = [
                      'id' => $questionEnum->value,
                      'title' => $questionEnum->getName(),
                  ];
              }
            return $returnService ;
        } elseif ($this->id == 3) {
            $services = \Modules\Service\app\Models\Service::where('parent_id', 3)->get();
            if (isset($services) && $services->isNotEmpty()) {
                $options = [];
                foreach ($services as  $service) {
                    $options[] = [
                        'id' => $service->id,
                        'title' => $service->title,
                    ];
                }
                return  [
                    'question' => 'لطفا بخش مورد نظر خود را انتخاب کنید',
                    'options' => $options
                ];
            }
        }
        return null;
    }
}
