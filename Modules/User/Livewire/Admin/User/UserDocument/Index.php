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

    public function updateUserMbile($userid, $value)
    {
        if(! is_null($value) && strlen($value) == 11) {
            User::find($userid)->update(['mobile' => $value]);
            $this->dispatch('showAlert', message: 'شماره موبایل به روز رسانی شد');
        }else{
            $this->dispatch('error', message: 'شماره موبایل باید 11 رقم باشد');
        }
    }
    public function updateNationalCode($userid, $value)
    {
        if(! is_null($value) && strlen($value) == 10) {
            User::find($userid)->nationalCode = $value;
            $this->dispatch('showAlert', message: 'کد ملی به روز رسانی شد');
        }else{
            $this->dispatch('error', message: 'کد ملی باید 10 رقم باشد');
        }
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
