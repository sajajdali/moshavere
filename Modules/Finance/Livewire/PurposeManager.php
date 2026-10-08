<?php

namespace Modules\Finance\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Finance\app\Models\FinancePaymentPurpose;

#[Title('دلایل پرداخت')]
class PurposeManager extends Component
{
    public ?int $editingId = null;

    public string $title = '';

    public $sort = '';

    private function authorizeManage(): void
    {
        abort_unless(auth()->user()?->can('finance.purposes'), 403);
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:120|unique:finance_payment_purposes,title' . ($this->editingId ? ',' . $this->editingId : ''),
            'sort' => 'nullable|integer|min:0|max:65000',
        ];
    }

    protected function validationAttributes(): array
    {
        return ['title' => 'عنوان', 'sort' => 'ترتیب نمایش'];
    }

    public function edit(int $id): void
    {
        $this->authorizeManage();
        $purpose = FinancePaymentPurpose::findOrFail($id);
        $this->editingId = $purpose->id;
        $this->title = $purpose->title;
        $this->sort = (string) $purpose->sort;
        $this->resetValidation();
    }

    public function cancel(): void
    {
        $this->reset('editingId', 'title', 'sort');
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorizeManage();
        $this->validate();
        $data = ['title' => trim($this->title), 'sort' => $this->sort === '' ? (int) FinancePaymentPurpose::max('sort') + 1 : (int) $this->sort];
        if ($this->editingId) {
            FinancePaymentPurpose::findOrFail($this->editingId)->update($data);
        } else {
            FinancePaymentPurpose::create($data + ['is_active' => true]);
        }
        $this->cancel();
        $this->dispatch('showAlert', message: 'دلیل پرداخت ذخیره شد');
    }

    public function toggle(int $id): void
    {
        $this->authorizeManage();
        $purpose = FinancePaymentPurpose::findOrFail($id);
        $purpose->update(['is_active' => ! $purpose->is_active]);
    }

    /** a purpose that was used stays: it is only deactivated, so the old reports keep their names */
    public function delete(int $id): void
    {
        $this->authorizeManage();
        $purpose = FinancePaymentPurpose::withCount('payments')->findOrFail($id);
        if ($purpose->payments_count > 0) {
            $this->dispatch('error', message: 'این دلیل برای پرداخت ها استفاده شده است؛ به جای حذف، آن را غیرفعال کنید');

            return;
        }
        $purpose->delete();
        $this->dispatch('showAlert', message: 'دلیل پرداخت حذف شد');
    }

    public function render()
    {
        return view('finance::livewire.purpose-manager', [
            'purposes' => FinancePaymentPurpose::withCount('payments')->ordered()->get(),
        ]);
    }
}
