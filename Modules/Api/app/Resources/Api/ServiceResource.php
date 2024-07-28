<?php

namespace Modules\Api\app\Resources\Api;

use Modules\Service\app\Models\Service;
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
            $pragnecyService =  Service::firstWhere('title', 'LIKE', "%{بارداری}%")?->id;
            if (isset($pragnecyService)) {
                $preagnencySubServices = Service::where('parent_id', $pragnecyService)->get();
                $returnService =  [];
                foreach ($preagnencySubServices as $service) {
                    $returnService[] = [
                        'id' => $service->id,
                        'title' => $service->title,
                    ];
                }
                return  $returnService;
            } else {
                // defult 
                return  [
                    'question' => 'در هفته چندم بارداری هستید',
                    'options' => [
                        [
                            'id' => 1,
                            'title' => 'هفته 4 تا 12 بارداری',
                        ],
                        [
                            'id' => 2,
                            'title' => 'هفته ۱۳ تا ۳۵ بارداری'
                        ],
                        [
                            'id' => 3,
                            'title' => '(هفته ۳۶ تا ۳۸ بارداری(اخرین ویزیت قبل از سزارین'
                        ],
                        // [
                        //     'id' => 5,
                        //     'title' => 'هفته پنجم بارداری'
                        // ],
                    ]
                ];
            }
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
