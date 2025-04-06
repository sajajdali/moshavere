<?php

namespace Modules\Api\app\Resources\Api\Appointments;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Api\app\Resources\Api\ServiceResource;

class DoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'speciality' => $this->getSpeciality(),
            'avatar' => $this->avatar,
            'services' => ServiceResource::collection($this->service()->whereNull('parent_id')->get()),
            'check_has_visited_or_not' => true,
            'biography' => $this->drBiography,
            'licenceNumber' => $this->drLicenceNumber,
            'gallery' => $this->gallery(),
            'address' => $this->drAddress,
            'banner' => $this->drBanner ?? url('storage/amniri_banner.jpeg'),
        ];
    }

    private function gallery()
    {
        $return =  [];
         if(isset($this->dr_gallery)) {
            $gall = json_decode($this->dr_gallery,true);
            foreach( $gall as $index => $content) {
                $return[] = [
                    'id' => $index ,
                    'file_address' => $content ,
                    'extension' => 'jpg' ,
                    'mime' => '' ,
                    'size' => '' ,
                    'created_at' => '' ,
                ] ;
            }
        }
        return $return ;
        // return [
        //     [
        //         'id' => 23,
        //         'original_name' => "image 98",
        //         'file_address' => 'https://amiri.selakteb.com/storage/appointment/online/1/VztbTXfMfmxuScWQp5elBuzJME0hJmUuj0791sSv.jpg',
        //         'extension' => "jpg",
        //         'mime' => "jpg",
        //         'size' => "86.76 KB",
        //         'created_at' => "1403/01/26 ساعت 20:24:10",
        //     ] ,
        //     [
        //         'id' => 24,
        //         'original_name' => "image 99",
        //         'file_address' => 'https://amiri.selakteb.com/storage/appointment/online/1/VztbTXfMfmxuScWQp5elBuzJME0hJmUuj0791sSv.jpg',
        //         'extension' => "jpg",
        //         'mime' => "jpg",
        //         'size' => "86.76 KB",
        //         'created_at' => "1403/01/26 ساعت 20:24:11",
        //     ]
        // ];
    }

    public function getSpeciality()
    {
        $speciality = '';
        if ($this->specialities->isNotEmpty()) {
            foreach ($this->specialities as $special) {
                if (strlen($speciality) < 1) {
                    $speciality .= $special->title;
                } else {
                    $speciality .= ' - ' . $special->title;
                }
            }
        } else {
            $speciality = 'بدون تخصص';
        }
        return $speciality;
    }
}
