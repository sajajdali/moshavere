<?php

namespace Modules\User\Livewire\Admin\User;

use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;

#[Title('پزشکان')]
class DoctorList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $term = trim($this->search);

        $doctors = User::doctors_query()
            // مانند لیست کاربران: بدون دسترسی کامل، فقط کاربران تحت پشتیبانی خود فرد
            ->when(auth()->user()?->cannot('user'), function ($query) {
                $query->whereHas('supporter', fn ($q) => $q->where('support_id', auth()->id()));
            })
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($q) use ($term) {
                    $q->where('users.mobile', 'LIKE', "%{$term}%")
                        ->orWhereHas('metas', function ($meta) use ($term) {
                            $meta->whereIn('meta_key', [UserMetaEnum::FIRST_NAME, UserMetaEnum::LAST_NAME])
                                ->where('meta_value', 'LIKE', "%{$term}%");
                        });
                    if (ctype_digit($term)) {
                        $q->orWhere('users.id', (int) $term);
                    }
                });
            })
            ->with(['services:services.id,services.title'])
            ->orderByDesc('users.id')
            ->paginate(15);

        return view('user::livewire.admin.user.doctor-list', compact('doctors'));
    }
}
