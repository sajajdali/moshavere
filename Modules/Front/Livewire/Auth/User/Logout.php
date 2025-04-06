<?php

namespace Modules\Front\Livewire\Auth\User;

use Livewire\Component;

class Logout extends Component
{
    public function mount() {
        auth()->logout();
        return redirect()->route('front.homePage');
    }
}
