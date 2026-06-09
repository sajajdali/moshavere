<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">تمدید هزینه هاست، سرور و پشتیبانی</h1>
        </div>

        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">خانه</a></li>
                <li class="breadcrumb-item active" aria-current="page">تمدید</li>
            </ol>
        </div>
    </div>

    @include('admin::layouts.components.alert')

    @if ($transactionResult)
        @php
            $isSuccessful = (int) $transactionResult['status'] === \Modules\Transaction\Enum\TransactionStatusEnum::SUCCESSFUL->value;
        @endphp

        <div class="row">
            <div class="col-xl-6 col-lg-8 col-md-10 mx-auto">
                <div class="card text-center">
                    <div class="card-body py-5">
                        <div class="mb-4">
                            <span class="avatar avatar-xl rounded-circle {{ $isSuccessful ? 'bg-success' : 'bg-danger' }} text-white">
                                <i class="fa {{ $isSuccessful ? 'fa-check' : 'fa-times' }}"></i>
                            </span>
                        </div>

                        <h3 class="mb-3">{{ $isSuccessful ? 'پرداخت موفق' : 'پرداخت ناموفق' }}</h3>
                        <p class="text-muted mb-4">{{ $transactionResult['message'] }}</p>

                        @unless ($isSuccessful)
                            <button
                                type="button"
                                class="btn btn-primary"
                                wire:click="redirectTransactionToBank({{ (int) $transactionResult['transaction_id'] }})"
                                wire:loading.class="btn-loading disabled"
                            >
                                تلاش مجدد پرداخت
                            </button>
                        @endunless
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-xl-8 col-lg-10 col-md-12 mx-auto">
                <div class="card">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">صورتحساب تمدید سالیانه</h3>
                        <span class="badge bg-primary">یک ساله</span>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap text-md-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th>شرح</th>
                                        <th class="text-center">مبلغ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-primary">
                                        <td class="fw-semibold">جمع کل</td>
                                        <td class="text-center fw-semibold">{{ number_format($totalCost) }} تومان</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <button
                            type="button"
                            class="btn btn-primary"
                            wire:click="redirectToBank"
                            wire:loading.class="btn-loading disabled"
                        >
                            انتقال به بانک برای پرداخت
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
