<?php

namespace Modules\User\Livewire\Admin\User\UserDocument;

use App\Models\Comment;
use Livewire\Component;
use Livewire\Attributes\On;
use Modules\User\Entities\User;

class Index extends Component
{
    public $user ;

    public array $fetchData = [];
    public array $form = [];

    public function addComment() {
        $this->validate([
            'form.comment' => 'required',
        ]);
        $this->user->comments()->create([
            'body' => $this->form['comment'] ,
        ]);
        $this->fetchData['comments'] = $this->user->comments ;
        $this->render();
    }

    #[On('delete')]
    public function delete(Comment $model)
    {
        $this->authorize('delete', $model);
        try {
            $model->delete();
        } catch (\Exception $e) {
        }

        return redirect()->route('admin.user.document',['user' => $this->user->id])->with('success', 'یادداشت  با موفقیت حذف شد.');
    }
    public function mount()
    {
        $this->user = User::find(request()->route('user'));
        $this->fetchData['appointments'] = $this->user->appointments ;
        $this->fetchData['comments'] = $this->user->comments ;
    }
    public function render()
    {
        return view('user::livewire.admin.user.user-document.index');
    }
}
