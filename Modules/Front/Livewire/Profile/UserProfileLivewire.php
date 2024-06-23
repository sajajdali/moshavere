<?php

namespace Modules\Front\Livewire\Profile;

use Livewire\Component;

#[Layout('front::layouts.app')]
#[Title('پروفایل')]
class UserProfileLivewire extends Component
{
    public function render()
    {
        return view('front::livewire.profile.user-profile-livewire');
    }
}
