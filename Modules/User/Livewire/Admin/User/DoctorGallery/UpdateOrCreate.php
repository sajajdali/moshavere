<?php

namespace Modules\User\Livewire\Admin\User\DoctorGallery;

use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\User\Entities\User;

class UpdateOrCreate extends Component
{

    public array $form = ['count_items' => 1];
    public array $fetchData = [];
    #[Locked]
    public  $user;
    public string $message = 'false';

    public function addItem()
    {
        ++$this->form['count_items'];
    }
    public function removeCounter($i)
    {
        --$this->form['count_items'];
        if (isset($this->form['items'][$i])) {
            unset($this->form['items'][$i]);
            $this->form['items'] = array_values($this->form['items']);
        }
    }
    public function rules()
    {
        for ($i = 0; $i < $this->form['count_items']; $i++) {
            $rules["form.items.$i"] = 'required';
        }
        return $rules;
    }
    public function messages()
    {
        return [
            'form.items.*.required' => 'لطفا تصویر مورد نظر را انتخاب کنید',
        ];
    }
    public function storeGalleryiespics()
    {
        $this->validate();
        if (isset($this->user->dr_gallery)) {
            $gallerts = array_merge(json_decode($this->user->dr_gallery, true), $this->form['items']);
        } else {
            $gallerts = $this->form['items'];
        }
        $this->user->dr_gallery = json_encode($gallerts);
        return redirect()->route('admin.user.index')->with('success', 'گالری با موفقیت اضافه شد');
    }
    public function removePick($image)
    {
        $galleries = json_decode($this->user->dr_gallery, true);
        if (isset($galleries[$image])) {
            unset($galleries[$image]);
            $this->message = 'با موفقیت حذف شد';
        }
        $this->user->dr_gallery   = json_encode($galleries);
        $this->fetchData['items'] = $galleries;
    }

    public function mount()
    {
        $this->user = User::find(request()->route('user'));
        // if (empty($this->user)) {
        //     return redirect()->route('admin.user.index')->with('error', 'کاربرمورد نظر پیدا نشد لطفا دوباره سعی کنید');
        // }
        if (isset($this->user->dr_gallery)) {
            $this->fetchData['items'] = json_decode($this->user->dr_gallery);
        }
    }
    public function render()
    {


        return view('user::livewire.admin.user.doctor-gallery.update-or-create');
    }
}
