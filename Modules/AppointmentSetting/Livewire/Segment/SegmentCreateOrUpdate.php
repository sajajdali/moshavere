<?php

namespace Modules\AppointmentSetting\Livewire\Segment;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\AppointmentSetting\app\Models\AppointmentSegment;

class SegmentCreateOrUpdate extends Component
{
    use AuthorizesRequests;

    public ?AppointmentSegment $appointmentSegment = null;
    public array $form = [
        'multiple_choice' => '1',
        'active' => true,
        'count_items' => 0

    ];
    public $isEdited = false;

    protected $rules = [
        'form.title' => 'required|string',
        'form.items.*.title' => 'required|string',
        'form.items.*.time' => 'required',
    ];

    protected $messages = [
        'form.title.required' => 'نام گروه بندی را وارد کنید',
        'form.items.*.title.required' => 'لطفا نام  گروه بندی را وارد کنید',
        'form.items.*.time.required' => 'لطفا زمان مورد نیاز را وارد کنید',
    ];

    public function mount()
    {
        $segment = request()->route('segment');
        if ($segment instanceof AppointmentSegment) {
            $this->isEdited = true;
            $this->authorize('update', $segment);
            $this->appointmentSegment = $segment;
            $this->form['title'] = $segment->title;
            $this->form['multiple_choice'] = $segment->multiple_choice;
            // items
                $this->form['items'] = $segment->items->toArray();
                $this->form['count_items'] = count($this->form['items']) - 1;
            // items
        } else {
            $this->form['items'][0]['display_on_site'] = 1;
        }
    }


    public function addItem()
    {
        ++$this->form['count_items'];
        $counter = $this->form['count_items'];
        $this->form['items'][$counter]['display_on_site'] = 1;

    }

    public function removeItem($i)
    {
        --$this->form['count_items'];
        unset($this->form['items'][$i]);
        $this->form['items'] = array_values($this->form['items']);

    }

    public function updateOrCreate()
    {
        $this->validate();
        $segmentCreateOrUpdate = [
            'title' => $this->form['title'],
            'active'    => $this->form['active'],
            'multiple_choice' => $this->form['multiple_choice'],
        ];
        if ($this->isEdited) {

            $this->appointmentSegment->update($segmentCreateOrUpdate);
            $message = 'تنظیمات با موفقیت ویرایش شد';
            // remove item when click to remove by use
            $listId = collect($this->form['items'])->pluck('id');
            $this->appointmentSegment->items()->whereNotIn('id', $listId)->delete();
            foreach ($this->form['items'] as $item) {

                if (isset($item['id'])){
                    unset($item['created_at']);
                    unset($item['updated_at']);
                    $this->appointmentSegment->items()->whereId($item['id'])->update($item);
                } else {
                    $this->appointmentSegment->items()->create($item);
                }
            }
            // remove item when click to remove by use
        } else {
            $this->appointmentSegment = AppointmentSegment::create($segmentCreateOrUpdate);
            $message = 'تنظیمات با موفقیت اضافه شد';
            $this->appointmentSegment->items()->createMany($this->form['items'] );
        }
        return redirect()->route('admin.appointment.segment.list')->with('success', $message);

    }

    public function render()
    {
        $title = (!is_null($this->appointmentSegment) && $this->appointmentSegment->exists) ? 'ویرایش بخش بندی' : 'ایجاد بخش بندی جدید';

        return view('appointmentsetting::livewire.segment.segment-create-or-update')->title($title);
    }
}
