<?php

namespace Modules\Front\Livewire\AboutUs;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('front::layouts.app')]
#[Title('درباره ما')]
class AboutUsLiveWire extends Component
{
    public function render()
    {
        return view('front::livewire.about-us.about-us-live-wire');
    }
}
