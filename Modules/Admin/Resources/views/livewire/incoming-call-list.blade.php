<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">تماس‌های ورودی</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">خانه</a></li>
                <li class="breadcrumb-item active" aria-current="page">تماس‌های ورودی</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">فهرست تماس‌های ورودی</h3>
                    
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="incoming">شماره تماس</label>
                            <input id="incoming" type="search" class="form-control" wire:model.live.debounce.300ms="incoming" placeholder="جست‌وجو بر اساس شماره تماس">
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table text-nowrap text-md-nowrap table-bordered text-center" wire:loading.class="op-0-3">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">شماره تماس</th>
                                    <th scope="col">تاریخ و ساعت تماس</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($incomingCalls as $incomingCall)
                                    <tr>
                                        <td>{{ $incomingCall->id }}</td>
                                        <td>{{ $incomingCall->incoming }}</td>
                                        <td>{{ verta($incomingCall->created_at)->format('Y/m/d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">
                                            <div class="alert alert-info mb-0">تماس ورودی یافت نشد.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $incomingCalls->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
