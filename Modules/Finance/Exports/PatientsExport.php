<?php

namespace Modules\Finance\Exports;

use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/** one row per patient (see FinanceReportService::patientsQuery) */
class PatientsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['بیمار', 'موبایل', 'تعداد پرداخت', 'پرداخت سیستمی', 'پرداخت دستی', 'بازگشت وجه', 'خالص پرداخت', 'آخرین پرداخت'];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->mobile,
            (int) $row->payments_count,
            (int) $row->system_total,
            (int) $row->manual_total,
            (int) $row->refund_total,
            (int) $row->net,
            $row->last_paid_at ? Verta::parse($row->last_paid_at)->format('Y/m/d H:i') : '',
        ];
    }
}
