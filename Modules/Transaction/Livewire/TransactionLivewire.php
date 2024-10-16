<?php

namespace Modules\Transaction\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Modules\User\Enum\UserMetaEnum;
use Modules\Transaction\app\Models\Transaction;
use Modules\Transaction\Enum\TransactionStatusEnum;

class TransactionLivewire extends Component
{
    use WithPagination;

    #[Url]
    public array $search = [];
    public array $fetchData = [];
    public function startSearch()
    {
        $this->render();
    }
    public function resetProperties() {
        $this->search = [] ;
        return $this->render();
    }
    public function render()
    {
        $query =  Transaction::query();
        $searchCriteria = [
            'idSearch' => [
                'condition' => isset($this->search['id']),
                'callback' => function ($query) {
                    return $query->whereId($this->search['id']);
                },
            ],
            'search.transaction.status' => [
                'condition' => isset($this->search['transaction']['status']),
                'callback' => function ($query) {
                    return $query->where('status', TransactionStatusEnum::tryFrom($this->search['transaction']['status']));
                },
            ],
            'search-user-id' => [
                'condition' => isset($this->search['user']['id']),
                'callback' => function ($query) {
                    $query->whereHas('user', function ($qq) {
                        return $qq->where('id', $this->search['user']['id']);
                    });
                },
            ],
            'search-user-name' => [
                'condition' => isset($this->search['user']['name']),
                'callback' => function ($query) {
                    return $query->whereHas('user', function ($qq) {
                        return $qq->whereHas('metas', function ($qqq) {
                            $qqq->where(function ($qqqq) {
                                return $qqqq->where('meta_key', UserMetaEnum::FIRST_NAME)
                                    ->where('meta_value', 'LIKE', "%{$this->search['user']['name']}%");
                            })->orwhere(function ($qqqq) {
                                return $qqqq->where('meta_key', UserMetaEnum::LAST_NAME)
                                    ->where('meta_value', 'LIKE', "%{$this->search['user']['name']}%");
                            });
                        });
                    });
                },
            ],
            'search-user-mobile' => [
                'condition' => isset($this->search['user']['mobile']),
                'callback' => function ($query) {
                    return $query->whereHas('user', function ($qq) {
                        return $qq->where('mobile', 'LIKE', "%{$this->search['user']['mobile']}%");
                    });
                },
            ],
            'search-user-nationalCode' => [
                'condition' => isset($this->search['user']['nationalCode']),
                'callback' => function ($query) {
                    return $query->whereHas('user', function ($qq) {
                        return $qq->whereHas('metas', function ($qqq) {
                            $qqq->where('meta_key', UserMetaEnum::NATIONAL_CODE)->where('meta_value', 'LIKE', "%{$this->search['user']['nationalCode']}%");
                        });
                    });
                },
            ],
            'search-appId' => [
                'condition' => isset($this->search['appId']),
                'callback' => function ($query) {
                    return $query->where('transactionable_id', 'LIKE', "%{$this->search['appId']}%");
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
            'transaction::livewire.transaction-livewire',

            ['transactions' => $query->orderByDesc('id')->paginate(10)]
        );
    }
}
