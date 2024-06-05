<?php

namespace Modules\Front\Livewire\HomePage;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('front::layouts.app')]
class HomePageLivewire extends Component
{
    public function render()
    {
        return view('front::livewire.home-page.home-page-livewire');
    }
}
