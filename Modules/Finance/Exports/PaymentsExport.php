<?php

namespace Modules\Finance\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Hekmatinasser\Verta\Verta;

/** the rows come from FinanceReportService and are already decorated (names, labels) */
class PaymentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
        return ['تاریخ', 'ساعت', 'بیمار', 'موبایل', 'نوع', 'منبع', 'روش پرداخت', 'دلیل پرداخت', 'پزشک', 'مبلغ', 'شماره پیگیری', 'توضیحات'];
    }

    public function map($row): array
    {
        return [
            Verta::instance($row->paid_at_carbon)->format('Y/m/d'),
            $row->paid_at_carbon->format('H:i'),
            $row->patient_name,
            $row->patient_mobile,
            $row->is_refund ? 'بازگشت وجه' : 'دریافت',
            $row->is_manual ? 'ثبت دستی' : 'سیستمی',
            $row->method_label,
            $row->purpose_label,
            $row->doctor_name,
            (int) $row->amount,
            $row->reference,
            $row->description,
        ];
    }
}
