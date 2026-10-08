@once
    @push('styles')
        <style>
            .fin-page { --fin-border: #e6e9ef; --fin-muted: #6b7a8c; --fin-soft: #f4f7fb; --fin-accent: #2f80c9; --fin-green: #1f9d55; --fin-red: #d64545; --fin-amber: #c98a0c; }
            .fin-card { background: #fff; border: 1px solid var(--fin-border); border-radius: 14px; box-shadow: 0 1px 3px rgba(16, 24, 40, .05); margin-bottom: 20px; }
            .fin-card-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding: 14px 20px; border-bottom: 1px solid var(--fin-border); }
            .fin-card-title { font-size: 15px; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px; }
            .fin-card-title i { color: var(--fin-muted); }
            .fin-card-body { padding: 18px 20px; }
            .fin-filters { padding: 16px 20px; }
            .fin-filter-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 12px; }
            .fin-filter-grid label { font-size: 12px; color: var(--fin-muted); margin-bottom: 4px; display: block; }
            .fin-ranges { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
            .fin-range { border: 1px solid var(--fin-border); background: var(--fin-soft); color: inherit; border-radius: 20px; padding: 4px 14px; font-size: 12.5px; cursor: pointer; }
            .fin-range:hover { border-color: var(--fin-accent); color: var(--fin-accent); }
            .fin-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; margin-bottom: 20px; }
            .fin-kpi { background: #fff; border: 1px solid var(--fin-border); border-radius: 14px; padding: 16px 18px; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 3px rgba(16, 24, 40, .05); }
            .fin-kpi-icon { flex: 0 0 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
            .fin-kpi-icon.blue { background: #e3f0fb; color: var(--fin-accent); }
            .fin-kpi-icon.green { background: #dcf3e6; color: var(--fin-green); }
            .fin-kpi-icon.red { background: #fde4e4; color: var(--fin-red); }
            .fin-kpi-icon.amber { background: #fdf0d3; color: var(--fin-amber); }
            .fin-kpi-icon.gray { background: #eceff3; color: #5c6b7d; }
            .fin-kpi-value { font-size: 19px; font-weight: 700; line-height: 1.25; }
            .fin-kpi-unit { font-size: 11px; font-weight: 400; color: var(--fin-muted); margin-inline-start: 4px; }
            .fin-kpi-label { font-size: 12px; color: var(--fin-muted); }
            .fin-table th { font-size: 12px; font-weight: 600; color: var(--fin-muted); background: var(--fin-soft); white-space: nowrap; }
            .fin-table td { vertical-align: middle; font-size: 13.5px; }
            .fin-table .fin-sub { display: block; font-size: 11.5px; color: var(--fin-muted); }
            .fin-table a.fin-link { color: inherit; font-weight: 600; text-decoration: none; }
            .fin-table a.fin-link:hover { color: var(--fin-accent); }
            .fin-amount, .fin-bar-value, .fin-kpi-value { unicode-bidi: isolate; }
            .fin-amount { font-weight: 700; white-space: nowrap; direction: ltr; display: inline-block; }
            .fin-amount.is-refund { color: var(--fin-red); }
            .fin-th-sort { cursor: pointer; user-select: none; }
            .fin-th-sort:hover { color: var(--fin-accent); }
            .fin-bar-row { display: grid; grid-template-columns: 150px 1fr auto; gap: 10px; align-items: center; margin-bottom: 10px; font-size: 13px; }
            .fin-bar-track { background: var(--fin-soft); border-radius: 6px; height: 10px; overflow: hidden; }
            .fin-bar-fill { height: 100%; background: linear-gradient(90deg, #2f80c9, #5ab0e8); border-radius: 6px; }
            .fin-bar-fill.green { background: linear-gradient(90deg, #1f9d55, #52c98a); }
            .fin-bar-fill.amber { background: linear-gradient(90deg, #c98a0c, #e8b84a); }
            .fin-bar-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .fin-bar-value { font-weight: 700; white-space: nowrap; direction: ltr; text-align: end; }
            .fin-days { display: flex; align-items: flex-end; gap: 4px; height: 150px; overflow-x: auto; padding-bottom: 4px; }
            .fin-day { flex: 1 0 22px; max-width: 46px; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; height: 100%; }
            .fin-day-bar { width: 100%; border-radius: 5px 5px 0 0; background: linear-gradient(180deg, #5ab0e8, #2f80c9); min-height: 3px; }
            .fin-day-label { font-size: 10px; color: var(--fin-muted); margin-top: 4px; white-space: nowrap; }
            .fin-empty { text-align: center; padding: 40px 10px; color: var(--fin-muted); }
            .fin-empty i { font-size: 34px; opacity: .5; display: block; margin-bottom: 10px; }
            .fin-badge-src { font-size: 11px; padding: 2px 9px; border-radius: 20px; font-weight: 600; }
            .fin-badge-src.system { background: #e3f0fb; color: #1d5f99; }
            .fin-badge-src.manual { background: #fdf0d3; color: #8a5d00; }
            .fin-amount-hint { font-size: 12px; color: var(--fin-muted); margin-top: 4px; }
            .fin-method-choices { display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 8px; }
            .fin-method-choice { border: 1px solid var(--fin-border); border-radius: 10px; padding: 10px 6px; text-align: center; cursor: pointer; font-size: 13px; background: #fff; }
            .fin-method-choice i { display: block; font-size: 18px; margin-bottom: 4px; color: var(--fin-muted); }
            .fin-method-choice.is-active { border-color: var(--fin-accent); background: #eef6fd; color: var(--fin-accent); }
            .fin-method-choice.is-active i { color: var(--fin-accent); }
            .dark-mode .fin-page { --fin-border: #2f3f55; --fin-muted: #a9b6c6; --fin-soft: #1f2a3a; }
            .dark-mode .fin-card, .dark-mode .fin-kpi, .dark-mode .fin-method-choice { background: #17202e; }
            .dark-mode .fin-method-choice.is-active { background: #1e3350; }
            @media (max-width: 640px) { .fin-bar-row { grid-template-columns: 100px 1fr auto; } }
        </style>
    @endpush

    @push('scripts')
        {{-- embedded in the patient file: that page already loads the alerts and registers their handlers --}}
        @unless ($embedded ?? false)
            <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
            <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
        @endunless
        <script>
            $(document).ready(function() {
                if (window.jalaliDatepicker) {
                    jalaliDatepicker.startWatch({ zIndex: 99999 });
                }
                @unless ($embedded ?? false)
                    Livewire.on('showAlert', param => showSwalSuccess(param.message));
                    Livewire.on('error', param => showSwalError(param.message));
                @endunless
            });
        </script>
    @endpush
@endonce
