<?php

namespace Modules\Service\Livewire;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Modules\Service\app\Models\Service;

class ServiceList extends Component
{
    use WithPagination;
    public  $search = [
        'id' => null,
        'title' => null,
        'active' => null,
    ];
    public array $fetchData = [];
    public $searchPanel = "";
    public function startSearch()
    {
        $this->render();
    }
    public function resetProperties()
    {
        $this->search = [];
        $this->searchPanel = "";
    }

    public function passModalData(Service $service)
    {
        $this->fetchData['modal'] = $service->subSection();
    }

    #[On('delete')]
    public function delete($model)
    {
        $ser = Service::find($model);
        $ser->delete();
        return redirect()->route('admin.service.list')->with('success', 'با موفقیت حذف شد');
    }

    public function render()
    {
        $query =  Service::orderBy('priority', 'asc');
        $searchCriteria = [
            'idSearch' => [
                'condition' => isset($this->search['id']),
                'callback' => function ($query) {
                    return $query->whereId($this->search['id']);
                },
            ],
            'ServiceName' => [
                'condition' => isset($this->search['title']),
                'callback' => function ($query) {
                    return $query->where('title', 'LIKE', '%' . $this->search['title'] . '%');
                },
            ],
            'activeStatus' => [
                'condition' => isset($this->search['active']),
                'callback' => function ($query) {
                    return $query->where('active', ActiveEnum::tryFrom((int) $this->search['active']));
                },
            ],
            'showHomePage' => [
                'condition' => isset($this->search['showHomePage']),
                'callback' => function ($query) {
                    return $query->where('show_type', 'LIKE', '%' . $this->search['showHomePage'] . '%');
                },
            ],
        ];

        foreach ($searchCriteria as $property => $config) {
            $condition = $config['condition'];
            $callback = $config['callback'];
            if ($condition) {
                $query->when($condition, $callback);
            }
        }


        return view(
            'service::livewire.service-list',
            ['services' => $query->paginate(10)]
        );
    }
}
