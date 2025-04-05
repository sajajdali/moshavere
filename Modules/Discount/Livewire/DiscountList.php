<?php

namespace Modules\Discount\Livewire;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Modules\Discount\app\Models\Discount;

class DiscountList extends Component
{
    use WithPagination;

    #[Url]
    public $search = [];

    public array $fetchdata = [];
    public array $form = [];

    public function startSearch()
    {
        $this->render();
    }
    public function resetProperties() {
        $this->search = [];
        $this->render();
    }
    #[On('delete')]
    public function deletePlace(Discount $model)
    {
        $model->delete();
        return redirect()->route('admin.discount.list')->with('success', 'کد تخفیف با موفقیت حذف شد');
    }



    public function render()
    {
        $query =  Discount::query();
        $searchCriteria = [
            'idSearch' => [
                'condition' => isset($this->search['id']),
                'callback' => function ($query) {
                    return $query->whereId($this->search['id']);
                },
            ],
            'search.code' => [
                'condition' => isset($this->search['search.code']),
                'callback' => function ($query) {
                    return $query->where('code', 'LIKE', '%' . $this->search['code'] . '%');
                },
            ],
            'activeStatus' => [
                'condition' => isset($this->search['active']),
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
            'discount::livewire.discount-list',
            ['discounts' => $query->paginate(10)]
        );
    }
}
