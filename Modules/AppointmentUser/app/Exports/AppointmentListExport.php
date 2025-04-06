<?php

namespace Modules\AppointmentUser\app\Exports;

use Modules\User\Entities\User;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\AppointmentUser\app\Models\AppointmentUser;

class AppointmentListExport implements FromCollection, WithMapping, WithHeadings, WithStyles, ShouldAutoSize
{
    public  $rowColors ;

    public function __construct(public Collection $data)
    {
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->data;
    }
    public function map($app): array
    {
        return [
            $app->id,
            $app->tracking_code,
            $app->status->getName(),
            isset($app->details[AppointmentUser::DETAIL_APPOINTMENT_VIA]) ? User::find($app->details[AppointmentUser::DETAIL_APPOINTMENT_VIA])->full_name : 'بیمار',
            $app->user->full_name,
            $app->user->mobile,
            $app->user->document_number ?? '---',
            $app->doctor->full_name,
            $app->service?->title,
            verta($app->start_time)->format('H:i'),
            verta($app->date_visit)->format('Y/m/d'),
            verta($app->created_at)->format('Y/m/d'),

        ];
    }
    public function headings(): array
    {
        return [
            '#',
            'شماره پیگیری',
            'نوع نوبت',
            'ثبت شده توسط',
            'نام بیمار',
            'شماره موبایل',
            'شماره پرونده',
            'نام پزشک',
            'بخش',
            'زمان نوبت',
            'تاریخ نوبت',
            'تاریخ ثبت نوبت',
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
         $sheet->setRightToLeft(true);
        return [
            // Style the first row as bold text.
            1    => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'cae3ac'],
                ],
                'alignment' =>
                [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
            ],
            'A'    => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'cae3ac'],
                ]
            ],
        ];
    }
}
