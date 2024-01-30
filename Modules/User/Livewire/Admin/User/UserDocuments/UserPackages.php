<?php

namespace Modules\User\Livewire\Admin\User\UserDocuments;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Modules\Package\Entities\PackageUser;
use Modules\User\Entities\User;

class UserPackages extends Component
{
    public User $user ;
    public Collection $packageUsers ;

    public function removePackage( PackageUser $id)
    {
        $id->delete() ;
        session()->flash('success','بسته با موفقبت حذف شد');
        return redirect()->route('admin.user.document',$this->user);

    }
    public function mount($user)
    {
        $this->user = $user ;
        $this->packageUsers=$this->user->packages()->orderByDesc('id')->get() ;
    }

    public function render()
    {
        return view('user::livewire.admin.user.user-documents.user-packages');
    }
}
