<?php

namespace Modules\Place\Livewire;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Modules\Place\app\Models\Place;

class PlaceList extends Component
{
    use WithPagination;

    public $search = [
        'id' => null,
        'placeName' => null,
        'active' => null,
    ];
    public $searchPanel = "";

    public array $fetchdata = [];
    public array $form = [];


    public function resetProperties()
    {
        $this->search = [
            'id' => null,
            'placeName' => null,
            'active' => null,
        ];
        $this->searchPanel = null;
    }
    public function startSearch()
    {
        $this->render();
    }

    #[On('delete')]
    public function deletePlace(Place $model)
    {
        $model->delete();
        return redirect()->route('admin.place.list')->with('success', 'مطب با موفقیت حذف شد');
    }

    public function mount()
    {
    }
    public function render()
    {
        $query =  Place::orderByDesc('priority');
        $searchCriteria = [
            'idSearch' => [
                'condition' => $this->search['id'],
                'callback' => function ($query) {
                    return $query->whereId($this->search['id']);
                },
            ],
            'placeName' => [
                'condition' => $this->search['placeName'],
                'callback' => function ($query) {
                    return $query->where('title', 'LIKE', '%' . $this->search['placeName'] . '%');
                },
            ],
            'activeStatus' => [
                'condition' => $this->search['active'],
                'callback' => function ($query) {
                    return $query->where('active', ActiveEnum::tryFrom((int) $this->search['active']));
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
            'place::livewire.place-list',
            ['places' => $query->paginate(10)]
        );
    }
}
