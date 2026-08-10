<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">پیغام های ضبط شده</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">خانه</a></li>
                <li class="breadcrumb-item active" aria-current="page">پیغام های ضبط شده</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">فهرست پیغام های ضبط شده</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="search">جست‌وجو</label>
                            <input id="search" type="search" class="form-control" wire:model.live.debounce.300ms="search" placeholder="جست‌وجو بر اساس شماره تماس یا نام">
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table text-nowrap text-md-nowrap table-bordered text-center" wire:loading.class="op-0-3">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">شماره تماس</th>
                                    <th scope="col">نام</th>
                                    <th scope="col">فایل صوتی</th>
                                    <th scope="col">حجم</th>
                                    <th scope="col">تاریخ دریافت</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($voiceRecords as $voiceRecord)
                                    <tr>
                                        <td>{{ $voiceRecord->id }}</td>
                                        <td>{{ $voiceRecord->incoming }}</td>
                                        <td>{{ $voiceRecord->name ?: '-' }}</td>
                                        <td>
                                            <audio controls preload="none" style="width: 240px; max-width: 100%;">
                                                <source src="{{ url($voiceRecord->file_path) }}" type="{{ $voiceRecord->mime_type ?: 'audio/mpeg' }}">
                                            </audio>
                                            <div class="mt-2">
                                                <a href="{{ url($voiceRecord->file_path) }}" target="_blank" download>دانلود فایل</a>
                                            </div>
                                        </td>
                                        <td>
                                            @if($voiceRecord->size)
                                                {{ number_format($voiceRecord->size / 1024, 1) }} KB
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ verta($voiceRecord->created_at)->format('Y/m/d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="alert alert-info mb-0">پیغام ضبط شده‌ای یافت نشد.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $voiceRecords->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
