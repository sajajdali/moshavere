<?php

namespace Modules\User\Livewire\Admin\User;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;

#[title('مدیریت کاربران')]
class UserList extends Component
{
    use withPagination;

    #[Url]
    public $search = [];

    public $searchPanel = '';

    #[On('delete')]
    public function delete(User $model)
    {
        $this->authorize('delete', $model);

        try {
            $model->delete();
        } catch (\Exception $e) {
        }

        return redirect()->route('admin.user.index')->with('success', 'کاربر با موفقیت حذف شد.');
    }

    public function startSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::when(auth()->user()?->cannot('user'), function ($query) {
            $query->whereHas('supporter', function ($q2) {
                $q2->where('support_id', auth()->id());
            });
        })
            ->when(isset($this->search['id']) && (int) $this->search['id'] !== 0, function ($query) {
                return $query->where('id', $this->search['id']);
            })
            ->when(isset($this->search['mobile']) && ! empty($this->search['mobile']), function ($query) {
                return $query->where('mobile', 'LIKE', "%{$this->search['mobile']}%");
            })
            ->when(isset($this->search['email']) && ! empty($this->search['email']), function ($query) {
                return $query->where('email', 'LIKE', "%{$this->search['email']}%");
            })
            ->when(isset($this->search['first_name']) && ! empty($this->search['first_name']), function ($query) {
                return $query->whereHas('metas', function ($q) {
                    $q->where([
                        ['meta_key', UserMetaEnum::FIRST_NAME],
                        ['meta_value', 'LIKE', "%{$this->search['first_name']}%"],
                    ]);
                });
            })
            ->when(isset($this->search['last_name']) && ! empty($this->search['last_name']), function ($query) {
                return $query->whereHas('metas', function ($q) {
                    $q->where([
                        ['meta_key', UserMetaEnum::LAST_NAME],
                        ['meta_value', 'LIKE', "%{$this->search['last_name']}%"],
                    ]);
                });
            })
            ->orderByDesc('id')->paginate(10);

        return view('user::livewire.admin.user.user-list', compact('users'));
    }
}
