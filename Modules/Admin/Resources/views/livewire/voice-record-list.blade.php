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
                        <table class="table text-nowrap text-md-nowrap table-bordered table-hover align-middle text-center" wire:loading.class="op-0-3">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">وضعیت</th>
                                    <th scope="col">شماره تماس</th>
                                    <th scope="col">نام</th>
                                    <th scope="col">فایل صوتی</th>
                                    <th scope="col">تاریخ دریافت</th>
                                    <th scope="col">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($voiceRecords as $voiceRecord)
                                    @php
                                        $extension = strtolower(pathinfo($voiceRecord->file_path, PATHINFO_EXTENSION));
                                        $audioType = match ($extension) {
                                            'wav' => 'audio/wav',
                                            'mp3' => 'audio/mpeg',
                                            'ogg' => 'audio/ogg',
                                            default => $voiceRecord->mime_type ?: 'audio/wav',
                                        };
                                        $listened = ! is_null($voiceRecord->listened_at);
                                    @endphp
                                    <tr wire:key="voice-record-{{ $voiceRecord->id }}" @class(['table-warning' => ! $listened])>
                                        <td>{{ $voiceRecord->id }}</td>
                                        <td>
                                            @if ($listened)
                                                <span class="badge bg-success-transparent">
                                                    <i class="ri-check-double-line me-1"></i>شنیده شده
                                                </span>
                                                <div class="small text-muted mt-1">{{ verta($voiceRecord->listened_at)->format('Y/m/d H:i') }}</div>
                                            @else
                                                <span class="badge bg-warning-transparent">
                                                    <i class="ri-time-line me-1"></i>شنیده نشده
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $voiceRecord->incoming }}</td>
                                        <td>{{ $voiceRecord->name ?: '-' }}</td>
                                        <td>
                                            <audio controls preload="none" style="width: 240px; max-width: 100%;">
                                                <source src="{{ url($voiceRecord->file_path) }}" type="{{ $audioType }}">
                                            </audio>
                                            <div class="mt-2">
                                                <a href="{{ url($voiceRecord->file_path) }}" target="_blank" download>دانلود فایل</a>
                                            </div>
                                        </td>
                                        <td>{{ verta($voiceRecord->created_at)->format('Y/m/d H:i') }}</td>
                                        <td>
                                            <div class="d-flex flex-column gap-1 align-items-stretch">
                                                @unless ($listened)
                                                    <button type="button" class="btn btn-sm btn-success-light" wire:click="markAsListened({{ $voiceRecord->id }})" wire:loading.attr="disabled">
                                                        <i class="ri-check-line me-1"></i>علامت‌گذاری شنیده شد
                                                    </button>
                                                @endunless
                                                <button type="button" class="btn btn-sm btn-danger-light" wire:click="delete({{ $voiceRecord->id }})" wire:confirm="آیا از حذف این پیغام صوتی مطمئن هستید؟" wire:loading.attr="disabled">
                                                    <i class="ri-delete-bin-line me-1"></i>حذف
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
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
