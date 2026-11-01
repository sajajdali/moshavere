<div>
    <style>
        #doctorServiceLinksModal .modal-content{border:0;border-radius:18px;overflow:hidden;box-shadow:0 24px 60px rgba(16,24,40,.22)}
        #doctorServiceLinksModal .modal-header{padding:18px 22px;border-bottom:1px solid #eef0f3;background:linear-gradient(135deg,#f0fdfa 0%,#fff 72%)}
        #doctorServiceLinksModal .modal-title{font-size:16px;font-weight:700;margin:0}
        #doctorServiceLinksModal .dsl-sub{font-size:12px;color:#667085;margin-top:3px}
        .dsl-row{border:1px solid #e6e8ec;border-radius:12px;padding:12px 14px;margin-bottom:10px;background:#fff}
        .dsl-head{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:9px}
        .dsl-head strong{font-size:13.5px;color:#101828}
        .dsl-head small{font-size:12px;color:#667085}
        .dsl-warn{font-size:11.5px;color:#b54708;background:#fffaeb;border:1px solid #fedf89;border-radius:7px;padding:2px 8px}
        .dsl-field{display:flex;align-items:stretch;direction:ltr;border:1px solid #d0d5dd;border-radius:9px;overflow:hidden}
        .dsl-field span{display:flex;align-items:center;padding:0 9px;background:#f2f4f7;color:#667085;font-size:12.5px;white-space:nowrap}
        .dsl-field input{flex:1;min-width:0;border:0;padding:8px 10px;font-size:13px;outline:none;direction:ltr}
        .dsl-actions{display:flex;gap:6px;margin-top:8px;flex-wrap:wrap}
        .dsl-error{font-size:11.5px;color:#d92d20;margin-top:5px}
        .dsl-visits{font-size:11.5px;color:#475467}
    </style>

    <div wire:ignore.self class="modal fade" id="doctorServiceLinksModal" tabindex="-1" aria-labelledby="doctorServiceLinksTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" dir="rtl">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="doctorServiceLinksTitle"><i class="fa fa-link me-1"></i> لینک خدمت مشخص — {{ $doctorName }}</h5>
                        <div class="dsl-sub">با باز کردن لینک، پروفایل پزشک با همان بخش و مطب نمایش داده می‌شود.</div>
                    </div>
                    <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body" wire:loading.class="op-0-3">
                    @forelse ($rows as $row)
                        <div class="dsl-row" wire:key="dsl-{{ $row['key'] }}">
                            <div class="dsl-head">
                                <div>
                                    <strong>{{ $row['service_title'] }}</strong>
                                    <small> — {{ $row['place_title'] }}</small>
                                </div>
                                @unless ($row['has_setting'])
                                    <span class="dsl-warn"><i class="fa fa-exclamation-triangle"></i> تنظیمات نوبت‌دهی فعال ندارد</span>
                                @endunless
                                @if ($row['link_id'])
                                    <span class="dsl-visits"><i class="fa fa-eye"></i> {{ number_format($row['visits']) }} بازدید</span>
                                @endif
                            </div>

                            @if ($row['link_id'])
                                <div class="dsl-field">
                                    <input type="text" readonly value="{{ $row['url'] }}" onclick="this.select()">
                                </div>
                                <div class="dsl-actions">
                                    <button type="button" class="btn btn-sm btn-primary dsl-copy" data-url="{{ $row['url'] }}"><i class="fa fa-copy"></i> کپی لینک</button>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ $row['url'] }}" target="_blank" rel="noopener"><i class="fa fa-external-link"></i> باز کردن</a>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        wire:click="deleteLink({{ $row['link_id'] }})"
                                        wire:confirm="با حذف، این لینک برای همه کسانی که آن را دارند از کار می‌افتد. حذف شود؟">
                                        <i class="fa fa-trash"></i> حذف
                                    </button>
                                </div>
                            @else
                                <div class="dsl-field">
                                    <span>{{ request()->getSchemeAndHttpHost() }}/s/</span>
                                    <input type="text" wire:model="codes.{{ $row['key'] }}" placeholder="dr-name-service">
                                </div>
                                @error('codes.'.$row['key'])
                                    <div class="dsl-error">{{ $message }}</div>
                                @enderror
                                <div class="dsl-actions">
                                    <button type="button" class="btn btn-sm btn-success"
                                        wire:click="createLink('{{ $row['key'] }}')"
                                        wire:loading.attr="disabled" wire:target="createLink('{{ $row['key'] }}')">
                                        <i class="fa fa-plus"></i> ساخت لینک
                                    </button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="alert alert-info mb-0">
                            برای این پزشک بخش یا مطب فعالی ثبت نشده است.
                        </div>
                    @endforelse
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('show-doctor-service-links-modal', () => {
                const el = document.getElementById('doctorServiceLinksModal');
                if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show();
            });

            // روی http (مثل محیط محلی) Clipboard API در دسترس نیست
            document.getElementById('doctorServiceLinksModal').addEventListener('click', async (e) => {
                const btn = e.target.closest('.dsl-copy');
                if (!btn) return;
                const url = btn.dataset.url;
                try {
                    await navigator.clipboard.writeText(url);
                } catch {
                    const ta = Object.assign(document.createElement('textarea'), { value: url });
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    ta.remove();
                }
                const label = btn.innerHTML;
                btn.innerHTML = '<i class="fa fa-check"></i> کپی شد';
                setTimeout(() => { btn.innerHTML = label; }, 1500);
            });
        </script>
    @endscript
</div>
