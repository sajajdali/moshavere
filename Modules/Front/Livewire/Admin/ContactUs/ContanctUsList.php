<?php

namespace Modules\Front\Livewire\Admin\ContactUs;

use Livewire\Component;
use Livewire\Attributes\On;
use Modules\User\Enum\UserMetaEnum;
use Modules\Front\app\Models\Contactus;

class ContanctUsList extends Component
{
    public ?string $alertMessage = null;
    public array $fetchData = [];
    public array $form = [];


    public function markfordone(ContactUs $contactUs, $condition)
    {
        $contactUs->update([
            'detail' =>
            [Contactus::DETAIL_REPORT_HANDEL => $condition],
        ]);
        $this->alertMessage = 'وضعیت فرم به بررسی نشده تغییر پیدا کردن';
        if ($condition) {
            $this->alertMessage = 'وضعیت فرم به بررسی شده تغییر پیدا کردن';
        }
    }
    public function openModal(ContactUs $contactUs)
    {
        $this->fetchData['modal']['commentCody'] = $contactUs->body;
    }

    public function startSearch()
    {
        $this->render();
    }
    public function resetProperties()
    {
        unset($this->form['search']);
        $this->render();
    }
    #[On('delete')]
    public function deletePlace(ContactUs  $model)
    {
        $model->delete();
        return redirect()->route('admin.contactus')->with('success', 'نظر حذف شد');
    }


    public function render()
    {
        $query =  Contactus::query();
        $searchCriteria = [
            'idSearch' => [
                'condition' => isset($this->form['search']['id']),
                'callback' => function ($query) {
                    return $query->whereId($this->form['search']['id']);
                },
            ],
            'name' => [
                'condition' => isset($this->form['search']['name']),
                'callback' => function ($query) {
                    return $query->whereHas('user', function ($q) {
                        return $q->whereHas('metas', function ($qq) {
                            $qq->where([
                                ['meta_key', UserMetaEnum::FIRST_NAME],
                                ['meta_value', 'LIKE', "%{$this->form['search']['name']}%"],
                            ])->orwhere([
                                ['meta_key', UserMetaEnum::LAST_NAME],
                                ['meta_value', '<>', true],
                            ]);
                        });
                    });
                },
            ],
        ];
        foreach ($searchCriteria as $property => $config) {
            $condition = $config['condition'];
            $callback = $config['callback'];
            if ($condition) {
                $query->when($condition, $callback);
            }
        }


        return view('front::livewire.admin.contact-us.contanct-us-list', ['contactForms' => $query->orderByDesc('id')->paginate(10)]);
    }
}
