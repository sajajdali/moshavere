<div>
    <div>
        <div class="page-header">
            <div>
                <h1 class="page-title">لیست کد های تخفیف</h1>
            </div>
        </div>
        @include('admin::layouts.components.alert')
        <div class="row row-sm">
            <div class="col-lg-12">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between border-bottom">
                        <h3 class="card-title">مدیریت کد های تخفیف </h3>
                        <div class="card-options">
                            <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                                data-bs-target="#advanceSearch" aria-expanded="false" aria-controls="advanceSearch">
                                جست و جوی پیشرفته
                            </button>
                            @if (! empty($search))
                                <button class="btn btn-secondary ms-2" type="button" wire:click="resetProperties"
                                    data-bs-toggle="collapse" data-bs-target="#advanceSearch" aria-expanded="false"
                                    aria-controls="advanceSearch"
                                    wire:loading.class="bg-gray btn-loading disabled">نمایش همه
                                </button>
                            @endif
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="mb-5 collapse
                        @if (!empty($search['id']) || !empty($search['code']) || !empty($search['active'])) show @endif"
                            id="advanceSearch" wire:ignore>
                            <form class="form-horizontal example" autocomplete="off">
                                <div class="row mb-4">
                                    <label for="search-id" class="col-md-2 form-label">ایدی</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-id" wire:model="search.id"
                                             type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="search-id" class="col-md-2 form-label">ایدی کاربر</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-id" wire:model="search.user.id"
                                             type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="search-id" class="col-md-2 form-label">نام کاربر</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-id" wire:model="search.user.name"
                                             type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="search-id" class="col-md-2 form-label">شماره همراه کاربر</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-id" wire:model="search.user.mobile"
                                             type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="search-id" class="col-md-2 form-label">کد ملی کاربر</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-id" wire:model="search.user.nationalCode"
                                             type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label class="form-label col-md-2" for="default-dropdown">وضعیت تراکنش</label>
                                    <div class="col-md-10">
                                        <select wire:model='search.transaction.status' name="country"
                                            class="form-control form-select" id="default-dropdown"
                                            data-bs-transactionholder="انتخاب کنید..">
                                            <option value="-1" label="انتخاب کنید"></option>
                                            @foreach(Modules\Transaction\Enum\TransactionStatusEnum::cases() as $key => $value)
                                            <option value="{{$value->value}}"> {{$value->getName()}}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="search-id" class="col-md-2 form-label">ایدی نوبت</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-id" wire:model="search.appId"
                                             type="text">
                                    </div>
                                </div>
                                <button class="btn btn-primary" type="button" wire:click="startSearch"
                                    wire:loading.class="bg-gray btn-loading disabled">جست و
                                    جو
                                </button>
                            </form>
                        </div>
                        <div class="table-responsive mb-3">
                            <table class="table text-nowrap text-md-nowrap table-bordered text-center"
                                wire:loading.class="op-0-3">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">نام</th>
                                        <th scope="col">نوبت</th>
                                        <th scope="col">زمان پرداخت</th>
                                        <th scope="col">وضعیت</th>
                                        <th scope="col">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($transactions->isNotEmpty())
                                        @foreach ($transactions as $transaction)
                                            <tr class="text-center {{$transaction->status->rowClassColor()}}">
                                                <td>{{ $transaction->id }}</td>
                                                <td>
                                                    <a target="bank" href="{{route('admin.user.document',['user'=>$transaction->user->id])}}">
                                                        {{ $transaction->user->fullName }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.appointment_user.list', ['search' => ['appointment_id' => $transaction->appid()]]) }}">
                                                        {{ $transaction->appid() }}
                                                    </a>
                                                </td>
                                                <td>{{ verta($transaction->updated_at)->format('Y/m/d ساعت H:i') }}</td>
                                                <td>
                                                    <span class=" badge {{$transaction->status->badgeClass()}}">
                                                        {{ $transaction->status->getName() }}</td>
                                                    </span>
                                                <td>
                                                    @can('delete', $transaction)
                                                    <div class="btn-group mt-2 mb-2">
                                                        <button type="button" class="btn btn-primary dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                            عملیات <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu" role="menu">
                                                            @can('delete', $transaction)
                                                                <li><a class="delete_confirm_alert" data-label="حذف "
                                                                        data-id="{{ $transaction->id }}"
                                                                        href="#">حذف</a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </div>
                                                    @else
                                                    <div class="btn-group mt-2 mb-2">
                                                        <button type="button" class="btn btn-default dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                            عملیات <span class="caret"></span>
                                                        </button>
                                                    </div>
                                                    @endcanany
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="100%" class="text-center">
                                                <div class="alert alert-info">
                                                    هیچ موردی یافت نشد
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $transactions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
@endpush
