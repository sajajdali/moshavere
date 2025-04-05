<?php

namespace Modules\Front\Livewire\Profile;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Modules\User\Entities\User;

#[Layout('front::layouts.app')]
#[Title('پروفایل')]
class UserProfileLivewire extends Component
{

    public array $fetchData = [];
    public array $form = [];
    public User $user;

    public function changePersonalInfo()
    {
        $this->validate([
            'form.first_name'        => 'required|string|max:225',
            'form.last_name'         => 'required|string|max:225',
            'form.gender'            => 'required|string|max:225',
            'form.national_code'     => 'nullable|string|max:225',
            'form.email'             => 'nullable|string|max:225',
        ]);
        $this->user->first_name = $this->form['first_name'];
        $this->user->last_name = $this->form['last_name'];
        $this->user->gender = $this->form['gender'];
        if (isset($this->form['national_code'])) {
            $this->user->national_code = $this->form['national_code'];
        }
        if (isset($this->form['email'])) {
            $userMOdel = User::find($this->user->id);
            $userMOdel->update(['email' => $this->form['email']]);
        }
        $this->dispatch('swalSuccess', true);
    }

    public function removeFromFavarite($docId)
    {
        if (User::find($docId)->exists()) {
            $privius_docs = auth()->user()->favorite_dr;
            // Retrieve the current user's favorite doctors
            $user = auth()->user();
            $privius_docs = $user->favorite_dr;

            // Ensure $privius_docs is an array
            if (!is_array($privius_docs)) {
                $privius_docs = json_decode($privius_docs, true);

                if (!is_array($privius_docs)) {
                    $privius_docs = [];
                }
            }

            if (($key = array_search($docId, $privius_docs)) !== false) {
                unset($privius_docs[$key]);
            }
            // Re-index the array to avoid potential issues with JSON encoding
            $privius_docs = array_values($privius_docs);
            $user->favorite_dr = $privius_docs;
            $this->dispatch('swalSuccess', true);
            $this->render();
        }
    }

    public function mount(): void
    {
        $this->user = auth()->user();

        $this->form = [
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'gender' => $this->user->gender,
            'national_code' => $this->user->national_code,
            'email' => $this->user->email,
            'mobile' => $this->user->mobile,
            'appointments' => $this->user->appointments()->orderByDesc('id')->get(),
            'comments' =>  $this->user->comments,
        ];
        if (isset($this->user->favorite_dr)) {
            $arr = ($this->user->favorite_dr);
            foreach ($arr as $doctorId) {
                $this->form['favorite_doctors'][] = User::find($doctorId);
            }
        }
    }
    public function render()
    {
        return view('front::livewire.profile.user-profile-livewire');
    }
}
