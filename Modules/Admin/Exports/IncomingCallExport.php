<?php

namespace Modules\Admin\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomingCallExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $incomingCalls)
    {
    }

    public function collection(): Collection
    {
        return $this->incomingCalls;
    }

    public function headings(): array
    {
        return [
            '#',
            'شماره تماس',
            'تاریخ و ساعت تماس',
        ];
    }

    public function map($incomingCall): array
    {
        return [
            $incomingCall->id,
            $incomingCall->incoming,
            verta($incomingCall->created_at)->format('Y/m/d H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().$sheet->getHighestRow())
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->setRightToLeft(true);

        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'cae3ac'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
            'A' => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'cae3ac'],
                ],
            ],
        ];
    }
}
