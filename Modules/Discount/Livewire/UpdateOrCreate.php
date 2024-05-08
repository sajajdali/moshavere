<?php

namespace Modules\Discount\Livewire;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Modules\User\Entities\User;
use Hekmatinasser\Verta\Facades\Verta;
use Livewire\Attributes\Locked;
use Modules\Service\app\Models\Service;
use Modules\Discount\app\Models\Discount;

class UpdateOrCreate extends Component
{
    public array $form = [
        'active' => true,
        'maximum_usage_each_user' => 1,
        'payment' => ['type' => 'percentage'],
    ];
    public array $fetchData = [];
    public bool $isEdited = false;
    #[Locked]
    public $discount;

    public function rules()
    {

        return [
            'form.code' => 'required|string',
            'form.payment.type'   => 'required',
            'form.payment.value'   => 'required',
        ];
    }
    public function messages()
    {
        return [
            'form.code.required' => 'لطفا کد تخفیف را وارد کنید!',
            'form.code.string' => 'فرمت وارد شده مورد قبول نیست!',
            'form.payment.type.required' => 'لطفا مقدار را انتخاب کنید',
            'form.payment.value.required' => 'لطفا مقدار را انتخاب کنید',
        ];
    }
    public function sotrediscount()
    {

        $this->validate();
        $detail = [
            Discount::DETAIL_TOTAL_USAGE => $this->form['maximum_usage_totall'] ?? null,
            Discount::DETAIL_MAXIMUM_USAGE_EACH_USER => $this->form['maximum_usage_each_user'] ?? null,
            Discount::DETAIL_MINIMUM_PRICE => $this->form['amount']['minimum'] ?? null,
            Discount::DETAIL_MAXIMUM_PRICE => $this->form['amount']['maximum'] ?? null,
            Discount::DETAIL_DISCOUNT_TYPE => $this->form['payment']['type'] ?? null,
            Discount::DETAIL_DISCOUNT_AMOUNT => $this->form['payment']['value'] ?? null,
        ];
        $active = $this->form['active'] == true  ? ActiveEnum::ACTIVE : ActiveEnum::DEACTIVE;
        $model = [
            'service_id' => isset($this->form['services']) ? $this->form['services'] : null,
            'doctor_id' => isset($this->form['doctors']) ? $this->form['doctors'] : null,
            'code' => $this->form['code'],
            'start_at' => isset($this->form['startDate']) ? Verta::parse($this->form['startDate'])->toCarbon() : null,
            'end_at' =>  isset($this->form['endDate']) ? Verta::parse($this->form['endDate'])->toCarbon() : null,
            'active' => $active,
            'detail' => $detail,
        ];
        if($this->isEdited) {
            $this->discount->update($model);
        }else{
            Discount::create($model);
        }
        return redirect()->route('admin.discount.list')->with('success', 'کد تخفیف با موفقیت اضافه شد');
    }
    private function fillTheInputs()
    {
        if (isset($this->discount->detail[Discount::DETAIL_TOTAL_USAGE])) {
            $this->form['maximum_usage_totall'] = $this->discount->detail[Discount::DETAIL_TOTAL_USAGE];
        }
        if (isset($this->discount->detail[Discount::DETAIL_MAXIMUM_USAGE_EACH_USER])) {
            $this->form['maximum_usage_each_user'] = $this->discount->detail[Discount::DETAIL_MAXIMUM_USAGE_EACH_USER];
        }
        if (isset($this->discount->detail[Discount::DETAIL_MINIMUM_PRICE])) {
            $this->form['amount']['minimum'] = $this->discount->detail[Discount::DETAIL_MINIMUM_PRICE];
        }
        if (isset($this->discount->detail[Discount::DETAIL_MAXIMUM_PRICE])) {
            $this->form['amount']['maximum']  = $this->discount->detail[Discount::DETAIL_MAXIMUM_PRICE];
        }
        $this->form['payment']['type'] = $this->discount->detail[Discount::DETAIL_DISCOUNT_TYPE];
        $this->form['payment']['value']  = $this->discount->detail[Discount::DETAIL_DISCOUNT_AMOUNT];
        $this->form['services'] = $this->discount->service_id ?? null;
        $this->form['doctors'] = $this->discount->doctor_id ?? null;
        $this->form['code'] =  $this->discount->code;
        if (isset($this->discount->start_at)) {
            $this->form['startDate'] =  verta($this->discount->start_at)->format('Y/m/d');
        }
        if (isset($this->discount->end_at)) {
            $this->form['endDate'] =  verta($this->discount->end_at)->format('Y/m/d');
        }
        if ($this->discount->active == ActiveEnum::ACTIVE) {
            $this->form['active'] = true;
        } else {
            $this->form['active'] = false;
        }
    }
    public function mount()
    {
        $this->fetchData['services'] = Service::all();
        $this->fetchData['doctors'] = User::doctors();
        if (! empty(request()->route('discount'))) {
            $this->isEdited = true;
            $this->discount = Discount::find(request()->route('discount'));
            $this->fillTheInputs();
        }
    }
    public function render()
    {
        return view('discount::livewire.update-or-create');
    }
}
