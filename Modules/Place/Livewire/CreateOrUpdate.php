<?php

namespace Modules\Place\Livewire;

use App\Enum\ActiveEnum;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Modules\Place\app\Models\Place;
use Spatie\Permission\Models\Role;

class CreateOrUpdate extends Component
{
    use AuthorizesRequests;

    public ?Place $place = null;
    public int $counter = 1;
    public array $form = [
        'active'    => true,
        'doctors' => [] ,
        'loc' => [
            'lat' => '35.7219',
            'lng' => '51.3347',
        ]
    ];
    public array $fetchData = [];


    public $isEdited = false;

    protected $rules = [
        'form.name' => 'required|string',
        'form.doctors' => [],
        'form.priority' => 'nullable|numeric',
    ];

    protected $messages = [
        'form.name.required' => 'نام مطب یا کلینیک را وارد کنید',
        'form.priority.numeric' => 'مقدار وارد شده اولیت نمایش باید از نوع عدد باشد',
    ];

    public function addCounter()
    {
        $this->counter = ++$this->counter;
        $this->render();
    }
    public function removeCounter()
    {
        $this->counter = --$this->counter;
        array_pop($this->form['numbers']);
        $this->render();
    }
    public function updateOrCreate()
    {

        $this->validate();

        $modelCreateOrUpdate = [
            'title' => $this->form['name'] ?? '',
            'priority' => $this->computData['priority'] ?? 1,
            'active' => $this->form['active'],
        ];

        if ($this->isEdited) {
            $detail = $this->place['detail'];
        } else {
            $detail = null;
        }
        if (isset($this->form['numbers'])) {
            $detail[Place::DETAIL_KEY_NUMBERS] = $this->form['numbers'];
        }
        if (isset($this->form['place']['loc']['lat']) && isset($this->form['place']['loc']['lng'])) {
            $detail[Place::DETAIL_KEY_LOCATION] = [
                Place::DETAIL_KEY_LOCATION_LAT => $this->form['place']['loc']['lat'],
                Place::DETAIL_KEY_LOCATION_LNG => $this->form['place']['loc']['lng'],
            ];
        }
        $modelCreateOrUpdate['detail'] = $detail;

        if ($this->isEdited) {
            $this->place->update($modelCreateOrUpdate);
            $message = 'لوکیشن با موفقیت ویرایش شد';
        } else {

            $this->place = Place::create($modelCreateOrUpdate);

            $message = 'لوکیشن با موفقیت اضافه شد';
        }
        if (isset($this->form['doctors'])) {
            $syncArr = [];
            foreach ($this->form['doctors'] as $userId => $value) {
                //check if check box checked
                if ($value) {
                    $syncArr[] = $userId;
                }
            }
            $this->place->user()->sync($syncArr);
        }
        return redirect()->route('admin.place.list')->with('success', $message);
    }
    public function mount()
    {
        $this->fetchData['doctors'] = Role::find(3)->users;

        $place = request()->route('place');
        if ($place instanceof Place) {
            $this->authorize('update', $place);
            $this->isEdited = true;
            $this->place = $place;
            $this->form['name'] = $place['title'];
            $this->form['numbers'] = $place['detail']['numbers'] ?? '';
            $this->counter = count($this->form['numbers']);
            $this->form['priority'] = $place['priority'];
            $this->form['active'] = $place['active'] == ActiveEnum::ACTIVE;
            $doctores = $this->place->user->pluck('id')->toArray();
            foreach ($doctores as $value) {
                $this->form['doctors'][$value] = true ;
            }
            $this->form['loc']['lat'] = $place->detail['location']['location_lat'];
            $this->form['loc']['lng'] =  $place->detail['location']['location_lng'];
        } else {
            $this->form['priority'] = Place::maxPriority();
        }
    }
    public function render()
    {
        return view('place::livewire.create-or-update');
    }
}
