<div>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">ترتیب نمایش پزشکان</h1>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    <div class="card">
        <div class="card-header border-bottom">
            <div>
                <h3 class="card-title mb-1">
                    ترتیب پزشکان
                    <span class="doctor-order-count">{{ number_format($doctors->count()) }} پزشک</span>
                </h3>
                <small class="text-muted">
                    پزشکان را بکشید و رها کنید یا از دکمه‌های بالا و پایین استفاده کنید. این ترتیب در صفحه ثبت نوبت و صفحه تنظیمات زمان‌های حضور اعمال می‌شود.
                </small>
            </div>
        </div>
        <div class="card-body">
            @if ($doctors->isEmpty())
                <div class="text-center text-muted py-5">پزشکی یافت نشد.</div>
            @else
                <ol class="doctor-order-list" id="doctorOrderList" wire:loading.class="opacity-50">
                    @foreach ($doctors as $doctor)
                        <li class="doctor-order-item" data-id="{{ $doctor->id }}" draggable="true" wire:key="order-{{ $doctor->id }}">
                            <span class="doctor-order-handle" title="برای جابجایی بکشید"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></span>
                            <span class="doctor-order-rank">{{ $loop->iteration }}</span>
                            <div class="doctor-order-name">
                                <strong>دکتر {{ $doctor->full_name ?: 'بدون نام' }}</strong>
                                <small>{{ $doctor->specialities->pluck('title')->filter()->implode('، ') ?: 'تخصص ثبت نشده' }}</small>
                            </div>
                            <div class="doctor-order-actions">
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="moveToTop({{ $doctor->id }})"
                                    @disabled($loop->first) title="انتقال به اول">
                                    <i class="fa-solid fa-angles-up" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="moveUp({{ $doctor->id }})"
                                    @disabled($loop->first) title="یک ردیف بالاتر">
                                    <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="moveDown({{ $doctor->id }})"
                                    @disabled($loop->last) title="یک ردیف پایین‌تر">
                                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>
</div>
@push('styles')
    <style>
        .doctor-order-count {
            margin-inline-start: 8px; padding: 2px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
            color: var(--primary-bg-color, #0070bb); background: color-mix(in srgb, var(--primary-bg-color, #0070bb) 10%, transparent);
        }
        .doctor-order-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; transition: opacity .15s; }
        .doctor-order-item {
            display: flex; align-items: center; gap: 14px; padding: 10px 14px; background: inherit;
            border: 1px solid rgba(128, 128, 160, .22); border-radius: 12px;
        }
        .doctor-order-item.is-dragging { opacity: .4; }
        .doctor-order-item.is-over { border-color: var(--primary-bg-color, #0070bb); box-shadow: 0 0 0 2px color-mix(in srgb, var(--primary-bg-color, #0070bb) 25%, transparent); }
        .doctor-order-handle { cursor: grab; opacity: .45; padding: 4px; }
        .doctor-order-rank {
            flex: 0 0 34px; height: 34px; display: grid; place-items: center; border-radius: 50%; font-weight: 700;
            color: var(--primary-bg-color, #0070bb); background: color-mix(in srgb, var(--primary-bg-color, #0070bb) 12%, transparent);
        }
        .doctor-order-name { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
        .doctor-order-name small { opacity: .65; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .doctor-order-actions { display: flex; gap: 6px; }
    </style>
@endpush
@push('scripts')
    <script>
        (function () {
            if (window.doctorOrderDragBound) return;
            window.doctorOrderDragBound = true;
            let dragged = null;
            const list = () => document.getElementById('doctorOrderList');
            const clearOver = () => list()?.querySelectorAll('.is-over').forEach(el => el.classList.remove('is-over'));

            document.addEventListener('dragstart', function (e) {
                const item = e.target.closest?.('.doctor-order-item');
                if (!item) return;
                dragged = item;
                item.classList.add('is-dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', item.dataset.id);
            });
            document.addEventListener('dragover', function (e) {
                const item = e.target.closest?.('.doctor-order-item');
                if (!dragged || !item || item === dragged) return;
                e.preventDefault();
                clearOver();
                item.classList.add('is-over');
            });
            document.addEventListener('drop', function (e) {
                const item = e.target.closest?.('.doctor-order-item');
                if (!dragged || !item || item === dragged) return;
                e.preventDefault();
                const items = Array.from(list().children);
                const from = items.indexOf(dragged);
                const to = items.indexOf(item);
                // dropping onto a row takes that row's place
                item.parentNode.insertBefore(dragged, from < to ? item.nextSibling : item);
                const ids = Array.from(list().children).map(el => parseInt(el.dataset.id, 10));
                clearOver();
                const root = list().closest('[wire\\:id]');
                if (root) Livewire.find(root.getAttribute('wire:id')).call('reorder', ids);
            });
            document.addEventListener('dragend', function () {
                dragged?.classList.remove('is-dragging');
                dragged = null;
                clearOver();
            });
        })();
    </script>
@endpush
