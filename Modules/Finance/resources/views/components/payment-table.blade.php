{{-- $payments: paginator of decorated rows; $showPatient, $canEdit, $canDelete --}}
@if ($payments->isEmpty())
    <div class="fin-empty"><i class="fa fa-credit-card" aria-hidden="true"></i>پرداختی با این فیلترها پیدا نشد</div>
@else
    <div class="table-responsive">
        <table class="table table-hover fin-table text-nowrap text-center mb-0">
            <thead>
                <tr>
                    <th>تاریخ</th>
                    @if ($showPatient)
                        <th>بیمار</th>
                    @endif
                    <th>منبع</th>
                    <th>روش پرداخت</th>
                    <th>دلیل پرداخت</th>
                    <th>پزشک</th>
                    <th>مبلغ (تومان)</th>
                    <th>شماره پیگیری</th>
                    @if ($canEdit || $canDelete)
                        <th></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $row)
                    <tr wire:key="pay-{{ $row->source }}-{{ $row->row_id }}">
                        <td>
                            {{ verta($row->paid_at_carbon)->format('Y/m/d') }}
                            <span class="fin-sub" dir="ltr">{{ $row->paid_at_carbon->format('H:i') }}</span>
                        </td>
                        @if ($showPatient)
                            <td>
                                <a class="fin-link" href="{{ route('admin.finance.patient', $row->user_id) }}">{{ $row->patient_name }}</a>
                                @if ($row->patient_mobile)
                                    <span class="fin-sub" dir="ltr">{{ $row->patient_mobile }}</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            <span class="fin-badge-src {{ $row->source }}">{{ $row->is_manual ? 'ثبت دستی' : 'سیستمی' }}</span>
                            @if ($row->is_refund)
                                <span class="badge bg-danger ms-1">بازگشت وجه</span>
                            @endif
                        </td>
                        <td>{{ $row->method_label }}</td>
                        <td>
                            {{ $row->purpose_label ?: '-' }}
                            @if ($row->description)
                                <span class="fin-sub" title="{{ $row->description }}">{{ \Illuminate\Support\Str::limit($row->description, 40) }}</span>
                            @endif
                        </td>
                        <td>{{ $row->doctor_name ?: '-' }}</td>
                        <td><span class="fin-amount {{ $row->is_refund ? 'is-refund' : '' }}">{{ number_format($row->amount) }}</span></td>
                        <td class="text-muted" dir="ltr">{{ $row->reference ?: '-' }}</td>
                        @if ($canEdit || $canDelete)
                            <td>
                                @if ($row->is_manual)
                                    @if ($canEdit)
                                        <button type="button" class="btn btn-sm btn-outline-primary" title="ویرایش"
                                            wire:click="$dispatch('finance-open-form', { paymentId: {{ $row->row_id }} })"><i class="fa fa-pencil"></i></button>
                                    @endif
                                    @if ($canDelete)
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="حذف"
                                            wire:click="deletePayment({{ $row->row_id }})" wire:confirm="این پرداخت حذف شود؟"><i class="fa fa-trash"></i></button>
                                    @endif
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $payments->links() }}</div>
@endif
