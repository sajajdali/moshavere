@once
@push('styles')
<style>
    .oc-module {
        --oc-primary: #0070bb;
        --oc-ink: #25364b;
        --oc-muted: #65768a;
        --oc-border: #dfe6ee;
        --oc-surface: #fff;
        color: var(--oc-ink);
        direction: rtl;
        text-align: right;
        font-size: 14px;
        line-height: 1.8;
        min-width: 0;
        max-width: 100%;
        padding: 26px 0 32px;
    }
    .oc-module *, .oc-module *::before, .oc-module *::after { box-sizing: border-box; }
    .oc-module h1, .oc-module h2, .oc-module h3, .oc-module p { margin: 0; }
    .oc-module a { text-decoration: none; }
    .oc-module a:focus-visible, .oc-module button:focus-visible, .oc-module input:focus-visible,
    .oc-module select:focus-visible, .oc-module textarea:focus-visible {
        outline: 3px solid #71b9ed;
        outline-offset: 3px;
    }
    .oc-module .oc-header { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 24px; }
    .oc-module .oc-heading { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .oc-module .oc-heading-icon, .oc-module .oc-stat-icon {
        display: grid; place-items: center; flex: 0 0 auto;
        width: 48px; height: 48px; border-radius: 14px;
        background: #e9f4fc; color: var(--oc-primary); font-size: 21px;
    }
    .oc-module .oc-eyebrow { color: var(--oc-muted); font-size: 12px; margin-bottom: 3px; }
    .oc-module .oc-title { font-size: 23px; font-weight: 700; line-height: 1.6; color: var(--oc-ink); }
    .oc-module .oc-subtitle { color: var(--oc-muted); font-size: 13px; margin-top: 3px; }
    .oc-module .oc-tabs {
        display: flex; flex-wrap: wrap; gap: 6px;
        padding: 6px; margin-bottom: 24px; border: 1px solid var(--oc-border);
        background: var(--oc-surface); border-radius: 12px;
    }
    .oc-module .oc-tab { display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 10px 18px; border-radius: 8px; color: var(--oc-muted); font-weight: 600; }
    .oc-module .oc-tab:hover { color: var(--oc-primary); background: #f3f8fc; }
    .oc-module .oc-tab[aria-current="page"] { color: var(--oc-primary); background: #e9f4fc; }
    .oc-module .oc-stack { display: grid; gap: 22px; min-width: 0; }
    .oc-module .oc-panel { min-width: 0; background: var(--oc-surface); border: 1px solid var(--oc-border); border-radius: 14px; box-shadow: 0 3px 12px #25364b04; overflow: hidden; }
    .oc-module .oc-panel-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 18px 24px; border-bottom: 1px solid var(--oc-border); }
    .oc-module .oc-panel-title { display: flex; align-items: center; gap: 9px; font-size: 16px; font-weight: 700; line-height: 1.8; }
    .oc-module .oc-panel-title > i { color: var(--oc-primary); font-size: 17px; }
    .oc-module .oc-panel-body { padding: 24px; min-width: 0; }
    .oc-module .oc-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 28px; row-gap: 22px; }
    .oc-module .oc-full { grid-column: 1 / -1; }
    .oc-module .oc-field { min-width: 0; }
    .oc-module .oc-label { display: block; font-size: 13px; font-weight: 600; margin: 0 0 8px; color: var(--oc-ink); }
    .oc-module .oc-required { color: #b3293b; margin-inline-start: 3px; }
    .oc-module .oc-input {
        display: block; width: 100%; min-width: 0; height: 44px;
        padding: 9px 12px; border: 1px solid #ccd6e1; border-radius: 8px;
        background: #fff; color: var(--oc-ink); font: inherit; line-height: 1.6;
        box-shadow: none; text-align: start;
    }
    .oc-module .oc-input:focus { border-color: var(--oc-primary); }
    .oc-module .oc-input[readonly] { background: #f4f7fa; color: var(--oc-muted); }
    .oc-module textarea.oc-input { height: auto; min-height: 110px; resize: vertical; }
    .oc-module select.oc-input { appearance: auto; padding-inline-end: 12px; cursor: pointer; }
    .oc-module .oc-input[aria-invalid="true"] { border-color: #c73449; background-color: #fffafb; }
    .oc-module .oc-help { display: block; color: var(--oc-muted); margin-top: 7px; font-size: 12px; line-height: 1.8; overflow-wrap: anywhere; }
    .oc-module .oc-error { display: block; color: #b3293b; font-size: 12px; margin-top: 6px; }
    .oc-module .oc-check {
        display: flex; align-items: flex-start; gap: 11px;
        padding: 15px; border: 1px solid var(--oc-border); border-radius: 10px;
        background: #f9fbfd; margin: 0; cursor: pointer; min-width: 0;
    }
    .oc-module input.oc-check-input {
        appearance: auto; -webkit-appearance: checkbox; position: static;
        width: 18px; height: 18px; min-width: 18px; flex: 0 0 18px;
        margin: 4px 0 0; float: none; opacity: 1; accent-color: var(--oc-primary); cursor: pointer;
    }
    .oc-module .oc-check-title { display: block; font-size: 13px; font-weight: 600; }
    .oc-module .oc-check .oc-help { margin-top: 2px; font-weight: 400; }
    .oc-module .oc-secret { display: grid; align-content: center; gap: 8px; }
    .oc-module .oc-secret .oc-check { background: transparent; padding: 10px 12px; }
    .oc-module .oc-actions, .oc-module .oc-toolbar, .oc-module .oc-inline { display: flex; align-items: center; flex-wrap: wrap; gap: 12px; }
    .oc-module .oc-toolbar { justify-content: space-between; }
    .oc-module .oc-btn {
        display: inline-flex; justify-content: center; align-items: center; gap: 8px;
        min-height: 42px; padding: 9px 17px; border: 1px solid #cbd9e6; border-radius: 8px;
        background: #fff; color: var(--oc-primary); font: inherit; font-size: 13px;
        font-weight: 600; line-height: 1.7; cursor: pointer; text-align: center;
    }
    .oc-module .oc-btn:hover { background: #edf6fc; border-color: #94b9d5; color: #005e9d; }
    .oc-module .oc-btn-primary { background: var(--oc-primary); border-color: var(--oc-primary); color: #fff; }
    .oc-module .oc-btn-primary:hover { background: #005e9d; border-color: #005e9d; color: #fff; }
    .oc-module .oc-btn-danger { color: #ad3446; border-color: #e5bfc6; }
    .oc-module .oc-btn-small { min-height: 35px; padding: 5px 12px; font-size: 12px; }
    .oc-module .oc-savebar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; padding: 18px 22px; border: 1px solid var(--oc-border); border-radius: 12px; background: #fff; }
    .oc-module .oc-savebar p { color: var(--oc-muted); font-size: 12px; }
    .oc-module .oc-notice { display: flex; align-items: flex-start; gap: 12px; padding: 16px 20px; margin-bottom: 22px; border: 1px solid #cce2f2; border-radius: 10px; background: #f0f8fe; color: #365e7c; font-size: 13px; }
    .oc-module .oc-notice > i { margin-top: 5px; flex-shrink: 0; }
    .oc-module .oc-notice-success { color: #216449; background: #effaf5; border-color: #c3e6d4; }
    .oc-module .oc-notice-error { color: #a22f42; background: #fff5f6; border-color: #efcbd2; }
    .oc-module .oc-notice ul { margin: 8px 0 0; padding-inline-start: 22px; }
    .oc-module .oc-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
    .oc-module .oc-stat { display: flex; align-items: center; gap: 14px; padding: 22px 18px; }
    .oc-module .oc-stat strong { display: block; font-size: 28px; font-weight: 700; line-height: 1.5; font-variant-numeric: tabular-nums; }
    .oc-module .oc-stat span { color: var(--oc-muted); font-size: 12px; }
    .oc-module .oc-stat-green .oc-stat-icon { background: #eaf7ef; color: #258456; }
    .oc-module .oc-stat-purple .oc-stat-icon { background: #f1ecfa; color: #7d57b4; }
    .oc-module .oc-stat-orange .oc-stat-icon { background: #fff3e8; color: #b36c26; }
    .oc-module .oc-badge { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; background: #eff3f7; color: #66768b; border-radius: 20px; font-size: 12px; white-space: nowrap; }
    .oc-module .oc-badge-success { color: #24724e; background: #eaf7ef; }
    .oc-module .oc-badge-warning { color: #956217; background: #fff5df; }
    .oc-module .oc-search { display: flex; align-items: end; gap: 10px; flex: 0 1 420px; min-width: 0; }
    .oc-module .oc-search .oc-field { flex: 1; }
    .oc-module .oc-search .oc-btn { flex-shrink: 0; }
    .oc-module .oc-results { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
    .oc-module .oc-selected-account { background: #edf7fc; border: 1px solid #d5e9f5; border-radius: 8px; padding: 12px 16px; }
    .oc-module .oc-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; max-width: 100%; }
    .oc-module .oc-table { width: 100%; margin: 0; border-collapse: collapse; font-size: 13px; text-align: right; }
    .oc-module .oc-table th { background: #f7f9fc; color: #5c6f85; font-weight: 600; padding: 13px 20px; white-space: nowrap; border-bottom: 1px solid var(--oc-border); }
    .oc-module .oc-table td { padding: 17px 20px; vertical-align: middle; border-bottom: 1px solid #edf1f6; }
    .oc-module .oc-table tbody tr:last-child td { border-bottom: 0; }
    .oc-module .oc-table .oc-person-name { font-weight: 600; color: var(--oc-ink); }
    .oc-module .oc-table .oc-btn { white-space: nowrap; }
    .oc-module .oc-table .oc-empty-cell { padding: 0; }
    .oc-module .oc-empty { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 45px 20px; text-align: center; }
    .oc-module .oc-empty > i { display: grid; place-items: center; width: 58px; height: 58px; font-size: 25px; background: #f0f5fa; color: #7693ad; border-radius: 18px; }
    .oc-module .oc-empty h3 { font-size: 15px; font-weight: 600; }
    .oc-module .oc-empty p { color: var(--oc-muted); font-size: 13px; max-width: 480px; }
    .oc-module .oc-pagination:empty { display: none; }
    .oc-module .oc-pagination:not(:empty) { padding: 16px 20px; border-top: 1px solid var(--oc-border); }
    .oc-module .oc-schedule .oc-input { width: 145px; direction: ltr; text-align: center; }
    .oc-module .oc-schedule td:first-child { min-width: 105px; font-weight: 600; }
    .oc-module .oc-schedule label { display: inline-flex; align-items: center; gap: 7px; margin: 0; cursor: pointer; white-space: nowrap; }
    .oc-module .oc-schedule input.oc-check-input { margin: 0; }
    .oc-module .oc-ltr { direction: ltr; unicode-bidi: isolate; }
    @media (max-width: 1199px) { .oc-module .oc-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767px) {
        .oc-module { padding-top: 18px; }
        .oc-module .oc-header { align-items: flex-start; flex-direction: column; gap: 14px; }
        .oc-module .oc-title { font-size: 20px; }
        .oc-module .oc-tabs { gap: 4px; }
        .oc-module .oc-tab { flex: 1 1 auto; font-size: 12px; padding: 9px 10px; }
        .oc-module .oc-panel-header { padding: 16px; }
        .oc-module .oc-panel-body { padding: 18px 16px; }
        .oc-module .oc-grid { grid-template-columns: minmax(0, 1fr); gap: 20px; }
        .oc-module .oc-stats { gap: 10px; }
        .oc-module .oc-stat { padding: 16px 12px; flex-direction: column; align-items: flex-start; gap: 10px; }
        .oc-module .oc-stat-icon { width: 36px; height: 36px; font-size: 17px; border-radius: 10px; }
        .oc-module .oc-stat strong { font-size: 25px; }
        .oc-module .oc-toolbar { align-items: stretch; flex-direction: column; }
        .oc-module .oc-search { flex: 1 1 auto; width: 100%; }
        .oc-module .oc-savebar { padding: 16px; }
        .oc-module .oc-savebar .oc-actions { width: 100%; }
        .oc-module .oc-savebar .oc-btn { flex: 1; }
        .oc-module .oc-table th, .oc-module .oc-table td { padding: 13px 14px; }
        .oc-module .oc-notice { padding: 14px; }
    }
    @media (prefers-reduced-motion: reduce) { .oc-module * { scroll-behavior: auto; transition: none; } }
</style>
@endpush
@endonce
