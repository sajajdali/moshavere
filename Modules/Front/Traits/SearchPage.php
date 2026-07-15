<?php

namespace Modules\Front\Traits;

use App\Enum\ActiveEnum;
use Illuminate\Support\Arr;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Modules\User\Enum\UserMetaEnum;
use Modules\Service\app\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Modules\Speciality\app\Models\Speciality;

trait SearchPage
{ public function messages()
    {
        return [
            'query.required' => 'متن جست و جو را وارد کنید!',
            'query.string' => 'فرمت وارد شده صحیح نیست!',
            'query.max' => 'متن وارد شده از حداکثر کاراکتر مجاز بیشتر است!',
        ];
    }
    public function RenewSearch()
    {
        // $this->validate([
        //     'query' => 'required|string|max:225'
        // ]);

        // for load more bottom
        if (isset($this->fetchData['wholeContentLoaded'])) {
            unset($this->fetchData['wholeContentLoaded']);
        }
        if (isset($this->fetchData['set_appointment_message'])) {
            unset($this->fetchData['set_appointment_message']);
        }
        if (isset($this->fetchData['service_id'])) {
            unset($this->fetchData['service_id']);
        }
        $this->searchIn();
        $this->render();
    }
    public function searchIn($setAppointment_result = null)
    {
        $sanitizedInput = htmlspecialchars($this->query, ENT_QUOTES, 'UTF-8');
        // Initialize results array
        if (!empty($setAppointment_result)) {
            $result = $setAppointment_result;
        } else {
            $result = [];
            if ($sanitizedInput == 'پزشکان') {
                $doctors = User::doctors_query()
                    ->select('users.*', 'doctor_order_metas.meta_value as doctor_order_value') // Select the meta_value explicitly
                    ->whereHas('metas', function ($q) {
                        $q->where('meta_key', UserMetaEnum::ACTIVE_APPOINTMENT)
                            ->where('meta_value', true);
                    })
                    ->when(isset($this->filter['speciality']), function ($query) {
                        $query->whereHas('specialities', function ($qq) {
                            $qq->where('title', 'LIKE', "%{$this->filter['speciality']}%");
                        });
                    })
                    ->when(isset($this->filter['service']), function ($query) {
                        $query->whereHas('service', function ($q) {
                            $q->where('title', 'LIKE', "%{$this->filter['service']}%");
                        });
                    })
                    ->leftJoin('user_metas as doctor_order_metas', function ($join) {
                        $join->on('users.id', '=', 'doctor_order_metas.user_id')
                            ->where('doctor_order_metas.meta_key', UserMetaEnum::DOCTOR_ORDER);
                    })
                    ->orderByRaw('ISNULL(doctor_order_value), doctor_order_value ASC') // or DESC
                    ->get();

                if ($doctors->isNotEmpty()) {
                    $result['doctors'] = $doctors;
                }
            } elseif ($sanitizedInput == 'بخش ها') {
                $services = Service::all();
                if ($services->isNotEmpty()) {
                    $result['service'] = $services;
                }
            } elseif (isset($this->fetchData['service_id'])) {

                $service = Service::find($this->fetchData['service_id']);
                if (isset($service)) {
                    $this->fetchData['settApp']['service'] = $service->id;
                    $result['doctors'] = $this->doctorsAvailableForService($service);
                    $this->fetchData['set_appointment_message'] = 'لطفا یکی از پزشکان مربوط به این بخش را انتخاب کنید!';
                } else {
                    $this->fetchData['set_appointment_message'] = 'بخش مورد نظر یافت نشد!';
                }
            } elseif (isset($this->fetchData['province_id'])) {
                $place = Place::whereNotNull('detail')
                    ->whereJsonContains('detail->' . Place::DETAIL_PROVINCE, $this->fetchData['province_id'])
                    ->get();
                if (isset($place) && $place->isNotEmpty()) {
                    $result['place'] =  $place;
                    $this->fetchData['set_appointment_message'] = 'لطفا یکی از پزشکان مربوط به این بخش را انتخاب کنید!';
                } else {
                    $this->fetchData['set_appointment_message'] = 'در استان انتخابی مطبی یافت نشد!';
                }
                unset($this->fetchData['province_id']);
            } else {
                // Places query
                $places = Place::where('title', 'LIKE', '%' . $sanitizedInput . '%')->get();
                if ($places->isNotEmpty()) {
                    $result['place'] =  $places;
                }

                // Doctors query
                $doctors = User::doctors_query()->when(isset($this->filter['speciality']), function ($query) {
                    $query->whereHas('specialities', function ($qq) {
                        $qq->where('title', 'LIKE', "%{$this->filter['speciality']}%");
                    });
                })->whereHas('metas', function ($q) use ($sanitizedInput) {
                    $q->where(function ($q) use ($sanitizedInput) {
                        $q->where('meta_key', UserMetaEnum::FIRST_NAME)
                            ->where('meta_value', 'LIKE', "%{$sanitizedInput}%");
                    })->orWhere(function ($q) use ($sanitizedInput) {
                        $q->where('meta_key', UserMetaEnum::LAST_NAME)
                            ->where('meta_value', 'LIKE', "%{$sanitizedInput}%");
                    });
                })->get();
                if ($doctors->isNotEmpty()) {
                    $result['doctors'] =  $doctors;
                }

                // Services query
                $services = Service::where('title', 'LIKE', "%{$sanitizedInput}%")->get();
                if ($services->isNotEmpty()) {
                    $result['service'] = $services;
                }
                //apply filters
                if (isset($this->filter['speciality'])) {
                    if (isset($result['doctors'])) {
                        $result = array_filter($result, function ($key) {
                            return $key === 'doctors';
                        }, ARRAY_FILTER_USE_KEY);
                        return $result;
                    }
                    return [];
                }
                //apply filters
                if (isset($this->filter['service'])) {
                    if (isset($result['service'])) {
                        $result = array_filter($result, function ($key) {
                            return $key === 'service';
                        }, ARRAY_FILTER_USE_KEY);
                        return $result;
                    }
                    return [];
                }
            }
        }
        $result =  $this->paginateTheResult($result);
        if (isset($this->fetchData['reuslt'])) {
            unset($this->fetchData['reuslt']);
        }
        $this->fetchData['reuslt'] =  $result;
    }
    public function loadMoreResult()
    {
        $this->fetchData['result_iterator'] += 10;
        $this->searchIn();
    }
    public  function paginateTheResult($result)
    {
        if (! isset($this->fetchData['result_iterator'])) {
            $this->fetchData['result_iterator'] = 20;
        }
        $return_reslut = [];
        foreach ($result as $key => $collection) {
            if ($collection instanceof Collection) {
                foreach ($collection as $value) {
                    $return_reslut[$key][] = $value;
                    if (count(array_merge(...array_values($return_reslut))) >= $this->fetchData['result_iterator']) {
                        break 2; // break out of both foreach loops
                    }
                }
            } else {
                $return_reslut[$key] = $collection;
                if (count(array_values($return_reslut)) >= $this->fetchData['result_iterator']) {
                    break;
                }
            }
        }
        if (count(array_values(Arr::flatten($return_reslut))) === count(array_values(Arr::flatten($result)))) {
            $this->fetchData['wholeContentLoaded'] = true;
        }
        return $return_reslut;
    }
    public function fillTheFilters()
    {
        $this->fetchData['specilities'] =  Speciality::all();
        $this->fetchData['services']    =  Service::all();
    }
    public function applyFilter($name, $category)
    {
        $this->filter[$category] = $name;
        $this->searchIn();
    }
    public function removeFilter($item)
    {
        if ($item === 'all') {
            $this->dispatch('removeFilterAll', true);
            $this->filter = [];
        }
        if (!empty($this->filter) &&  in_array($item, $this->filter)) {
            $index = array_search($item, $this->filter);
            $this->dispatch('removeFilter', $index);
            unset($this->filter[$index]);
        }
        $this->searchIn();
    }

    // set  buttons appointment
    public function getAppFromService($service_id)
    {
        $service = Service::find($service_id);
        if (!empty($service)) {
            $this->fetchData['settApp']['service'] = $service->id;
            $this->fetchData['set_appointment_message'] = 'لطفا پزشک مورد نظر را انتخاب کنید';
            $result['doctors'] = $this->doctorsAvailableForService($service);
            $this->query = null;
            $this->searchIn($result);
        }
    }

    private function doctorsAvailableForService(Service $service): Collection
    {
        return $service->user()
            ->whereNotExists(function ($query) use ($service) {
                $query->selectRaw('1')
                    ->from('appointment_settings')
                    ->whereColumn('appointment_settings.user_id', 'users.id')
                    ->where('appointment_settings.service_id', $service->id)
                    ->where('appointment_settings.active', ActiveEnum::DEACTIVE->value)
                    ->whereNull('appointment_settings.deleted_at');
            })
            ->get();
    }

    public function placeSelected($place_id)
    {
        $place = Place::find($place_id);
        if (!empty($place)) {
            $this->fetchData['settApp']['place'] = $place->id;
            $this->fetchData['set_appointment_message'] = 'لطفا پزشک مورد نظر را انتخاب کنید';
            $result['doctors'] = $place->user;
            $this->query = null;
            $this->searchIn($result);
        }
    }

    public function getApp($doctor_id)
    {
        $doc = User::find($doctor_id);
        if (isset($doc)) {
            $selectedServiceId = data_get($this->fetchData, 'settApp.service')
                ?? data_get($this->fetchData, 'service_id');

            if ($selectedServiceId && isset($doc->active_appointment) && (int) $doc->active_appointment !== 1) {
                return;
            }

            $param['doctor_id'] = $doc->id;
            $param['doctor_name'] = str_replace(' ', '_', $doc->full_name);
            if (isset($this->fetchData['settApp']['place'])) {
                $param['place_id'] = $this->fetchData['settApp']['place'];
            }
            if ($selectedServiceId) {
                $param['service_id'] = $selectedServiceId;
            }
            return redirect()->route('front.doctor.profile', $param);
        }
    }

}
