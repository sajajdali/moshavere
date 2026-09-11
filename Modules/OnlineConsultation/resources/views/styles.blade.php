@once
@push('styles')
<style>
    body.oc-is-loading { overflow: hidden; }
    .oc-page-loader { position:fixed;inset:0;z-index:999999;display:grid;place-items:center;padding:20px;background:rgba(20,39,60,.38);backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px);opacity:0;visibility:hidden;pointer-events:none;transition:opacity .22s ease,visibility .22s ease;direction:rtl; }
    .oc-page-loader.is-visible { opacity:1;visibility:visible;pointer-events:all; }
    .oc-loader-card { position:relative;width:min(330px,calc(100vw - 40px));padding:30px 28px 24px;border:1px solid rgba(255,255,255,.75);border-radius:22px;background:rgba(255,255,255,.96);box-shadow:0 24px 70px rgba(22,44,67,.24);text-align:center;overflow:hidden;transform:translateY(10px) scale(.98);transition:transform .22s ease;font-family:inherit; }
    .oc-page-loader.is-visible .oc-loader-card { transform:translateY(0) scale(1); }
    .oc-loader-visual { position:relative;width:86px;height:86px;margin:0 auto 15px;display:grid;place-items:center; }
    .oc-loader-visual i { position:relative;z-index:3;display:grid;place-items:center;width:52px;height:52px;border-radius:17px;background:linear-gradient(145deg,#087bc5,#005b9b);color:#fff;font-size:23px;box-shadow:0 8px 22px rgba(0,112,187,.28);animation:ocLoaderFloat 1.5s ease-in-out infinite; }
    .oc-loader-visual span { position:absolute;inset:5px;border:2px solid rgba(0,112,187,.18);border-top-color:#0070bb;border-radius:50%;animation:ocLoaderSpin 1.05s linear infinite; }
    .oc-loader-visual span:nth-child(2) { inset:0;animation-duration:1.7s;animation-direction:reverse;border-color:transparent;border-right-color:#60b5e9; }
    .oc-loader-visual span:nth-child(3) { inset:12px;animation-duration:.8s;border-color:transparent;border-bottom-color:#7d57b4; }
    .oc-loader-card strong,.oc-loader-card small { display:block; }.oc-loader-card strong{color:#25364b;font-size:16px}.oc-loader-card small{margin-top:4px;color:#718196;font-size:12px}
    .oc-loader-progress { height:4px;margin-top:19px;border-radius:10px;background:#e8eef4;overflow:hidden;direction:ltr; }.oc-loader-progress span{display:block;width:42%;height:100%;border-radius:inherit;background:linear-gradient(90deg,#0070bb,#68bde9);animation:ocLoaderProgress 1.2s ease-in-out infinite;}
    @keyframes ocLoaderSpin { to { transform:rotate(360deg); } } @keyframes ocLoaderFloat { 50% { transform:translateY(-3px); } } @keyframes ocLoaderProgress { 0%{transform:translateX(-115%)} 100%{transform:translateX(340%)} }
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
    .oc-module .oc-stats-five { grid-template-columns: repeat(5, minmax(0, 1fr)); }
    .oc-module .oc-stats-seven { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .oc-module .oc-stat { display: flex; align-items: center; gap: 14px; padding: 22px 18px; }
    .oc-module .oc-stat strong { display: block; font-size: 28px; font-weight: 700; line-height: 1.5; font-variant-numeric: tabular-nums; }
    .oc-module .oc-stat span { color: var(--oc-muted); font-size: 12px; }
    .oc-module .oc-stat-green .oc-stat-icon { background: #eaf7ef; color: #258456; }
    .oc-module .oc-stat-purple .oc-stat-icon { background: #f1ecfa; color: #7d57b4; }
    .oc-module .oc-stat-orange .oc-stat-icon { background: #fff3e8; color: #b36c26; }
    .oc-module .oc-badge { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; background: #eff3f7; color: #66768b; border-radius: 20px; font-size: 12px; white-space: nowrap; }
    .oc-module .oc-badge-success { color: #24724e; background: #eaf7ef; }
    .oc-module .oc-badge-warning { color: #956217; background: #fff5df; }
    .oc-module .oc-badge-info { color: #175cd3; background: #eaf2ff; }
    .oc-module .oc-badge-purple { color: #6941c6; background: #f1ebff; }
    .oc-module .oc-badge-muted { color: #5f6c7b; background: #eef1f4; }
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
    .oc-module .oc-clickable-table tbody tr[data-href] { cursor: pointer; transition: background-color .15s ease; }
    .oc-module .oc-clickable-table tbody tr[data-href]:hover { background: #f5faff; }
    .oc-module .oc-table .oc-person-name { font-weight: 600; color: var(--oc-ink); }
    .oc-module .oc-table .oc-btn { white-space: nowrap; }
    .oc-module .oc-table .oc-empty-cell { padding: 0; }
    .oc-module .oc-empty { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 45px 20px; text-align: center; }
    .oc-module .oc-empty > i { display: grid; place-items: center; width: 58px; height: 58px; font-size: 25px; background: #f0f5fa; color: #7693ad; border-radius: 18px; }
    .oc-module .oc-empty h3 { font-size: 15px; font-weight: 600; }
    .oc-module .oc-empty p { color: var(--oc-muted); font-size: 13px; max-width: 480px; }
    .oc-module .oc-pagination { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px 20px; border-top: 1px solid var(--oc-border); background: #fbfcfe; direction: rtl; }
    .oc-module .oc-pagination-summary { margin: 0; color: var(--oc-muted); font-size: 12px; white-space: nowrap; }
    .oc-module .oc-pagination-summary strong { color: var(--oc-ink); font-weight: 700; font-variant-numeric: tabular-nums; }
    .oc-module .oc-pager, .oc-module .oc-page-list { display: flex; align-items: center; gap: 6px; }
    .oc-module .oc-pager { min-width: 0; }
    .oc-module .oc-page-link, .oc-module .oc-page-current, .oc-module .oc-page-gap, .oc-module .oc-page-nav { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; margin: 0; border: 1px solid #d8e1eb; border-radius: 9px; background: #fff; color: #506176; font-size: 12px; line-height: 1; text-decoration: none; font-variant-numeric: tabular-nums; }
    .oc-module .oc-page-link, .oc-module .oc-page-nav { transition: border-color .15s ease, background-color .15s ease, color .15s ease, box-shadow .15s ease; }
    .oc-module .oc-page-link:hover, .oc-module .oc-page-nav:hover { border-color: #8bbbd9; background: #f0f8fd; color: var(--oc-primary); box-shadow: 0 2px 7px #25364b10; }
    .oc-module .oc-page-link:focus-visible, .oc-module .oc-page-nav:focus-visible { outline: 2px solid #77b7dd; outline-offset: 2px; }
    .oc-module .oc-page-current { border-color: var(--oc-primary); background: var(--oc-primary); color: #fff; font-weight: 700; box-shadow: 0 3px 9px #287fac2e; }
    .oc-module .oc-page-gap { min-width: 24px; border-color: transparent; background: transparent; color: #98a2b3; }
    .oc-module .oc-page-nav { gap: 7px; min-width: 76px; padding: 0 12px; font-weight: 600; }
    .oc-module .oc-page-nav.is-disabled { border-color: #e8edf2; background: #f6f8fa; color: #a8b2bf; cursor: not-allowed; box-shadow: none; }
    .oc-module .oc-schedule .oc-input { width: 145px; direction: ltr; text-align: center; }
    .oc-module .oc-schedule td:first-child { min-width: 105px; font-weight: 600; }
    .oc-module .oc-schedule label { display: inline-flex; align-items: center; gap: 7px; margin: 0; cursor: pointer; white-space: nowrap; }
    .oc-module .oc-schedule input.oc-check-input { margin: 0; }
    .oc-module .oc-ltr { direction: ltr; unicode-bidi: isolate; }
    .oc-module .oc-cell-sub { display: block; margin-top: 3px; color: var(--oc-muted); font-size: 11px; }
    .oc-module .oc-count-badge { display: inline-grid; place-items: center; min-width: 34px; height: 30px; padding: 0 8px; border-radius: 9px; background: #e9f4fc; color: var(--oc-primary); font-size: 15px; font-weight: 700; }
    .oc-module .oc-text-success { display: block; color: #24724e; font-weight: 600; }
    .oc-module .oc-text-danger { color: #b42318; font-weight: 600; }
    .oc-module .oc-text-warning { color: #956217; font-weight: 600; }
    .oc-module .oc-consultant-card { display:grid;grid-template-columns:260px 1fr;color:inherit;transition:.15s ease; }
    .oc-module .oc-consultant-card:hover { border-color:#91bddb;box-shadow:0 7px 22px #25364b12;color:inherit; }
    .oc-module .oc-consultant-person { display:flex;align-items:center;gap:13px;padding:22px;border-inline-end:1px solid var(--oc-border); }
    .oc-module .oc-consultant-person img { width:58px;height:58px;border-radius:50%;object-fit:cover;background:#eef3f7; }
    .oc-module .oc-consultant-person div { flex:1;min-width:0 }.oc-module .oc-consultant-person strong,.oc-module .oc-consultant-person span{display:block}.oc-module .oc-consultant-person span{font-size:12px;color:var(--oc-muted)}
    .oc-module .oc-consultant-metrics { display:grid;grid-template-columns:repeat(5,minmax(115px,1fr)); }
    .oc-module .oc-metric { padding:16px;border-inline-end:1px solid #edf1f6;border-bottom:1px solid #edf1f6 }.oc-module .oc-metric span{display:block;color:var(--oc-muted);font-size:11px}.oc-module .oc-metric strong{display:block;margin-top:3px;font-size:15px}.oc-module .oc-metric.is-danger{background:#fff1f2;color:#b42318}.oc-module .oc-metric.is-good{color:#24724e}
    .oc-module .oc-dashboard-filter { display:grid;grid-template-columns:repeat(6,minmax(130px,1fr));gap:13px;align-items:end }.oc-module .oc-filter-checks{grid-column:1/-1;display:flex;gap:18px;flex-wrap:wrap}.oc-module .oc-filter-checks label{margin:0;cursor:pointer}
    .oc-module .oc-dashboard-stats { grid-template-columns:repeat(7,minmax(120px,1fr)) }.oc-module .oc-dashboard-stats .oc-stat{padding:16px}.oc-module .oc-dashboard-stats .oc-stat strong{font-size:20px}.oc-module .oc-stat-danger{border-color:#f2b8bf;background:#fff4f5}.oc-module .oc-stat-danger strong{color:#b42318}
    .oc-module .oc-stat-filter { position: relative; color: inherit; cursor: pointer; transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; }
    .oc-module .oc-stat-filter:hover { color: inherit; border-color: #8dbbd8; box-shadow: 0 7px 20px #25364b14; transform: translateY(-2px); }
    .oc-module .oc-stat-filter small { display: flex; align-items: center; gap: 5px; margin-top: 8px; color: var(--oc-primary); font-size: 10px; font-weight: 600; }
    .oc-module .oc-alert{display:flex;align-items:center;gap:10px;padding:16px 20px;border:1px solid #efb7bf;border-radius:12px;background:#fff1f2;color:#a7192e}.oc-module .oc-row-danger{background:#fff3f4!important}.oc-module .oc-badge-danger{background:#d92d20;color:#fff}
    .oc-module .oc-period-panel{overflow:visible}.oc-module .oc-period-tabs{display:grid;grid-template-columns:1fr 1fr;padding:6px;background:#f2f6fa;border-bottom:1px solid var(--oc-border);gap:6px}.oc-module .oc-period-tab{display:flex;justify-content:center;align-items:center;gap:9px;padding:12px;border-radius:9px;color:var(--oc-muted);font-weight:700}.oc-module .oc-period-tab.is-active{background:#fff;color:var(--oc-primary);box-shadow:0 2px 8px #25364b12}.oc-module .oc-day-navigator{display:grid;grid-template-columns:130px minmax(220px,360px) 130px;justify-content:center;align-items:end;gap:18px;padding:22px}.oc-module .oc-nav-arrow{display:flex;align-items:center;justify-content:center;gap:9px;height:44px;border:1px solid #cbd9e6;border-radius:9px;color:var(--oc-primary);font-weight:600}.oc-module .oc-nav-arrow:hover{background:#edf6fc}.oc-module .oc-nav-arrow.is-disabled{pointer-events:none;opacity:.4}.oc-module .oc-date-picker-form label{display:block;text-align:center;font-size:12px;color:var(--oc-muted);margin-bottom:6px}.oc-module .oc-date-picker-form .oc-input{text-align:center;font-size:16px;font-weight:700}.oc-module .oc-month-picker{display:flex;justify-content:center;align-items:end;gap:12px;padding:22px}.oc-module .oc-month-picker .oc-field{width:180px}.oc-module .oc-period-summary{display:flex;justify-content:center;align-items:center;gap:9px;padding:11px;background:#f8fbfd;border-top:1px solid var(--oc-border);color:var(--oc-muted)}
    .oc-module .oc-filter-grid { display: grid; grid-template-columns: repeat(3, minmax(180px, 1fr)); gap: 14px; align-items: end; }
    .oc-module .oc-filter-actions { flex-wrap: nowrap; padding-bottom: 1px; }
    .oc-module .oc-date-field { position: relative; }
    .oc-module .oc-date-field > i { position: absolute; z-index: 1; top: 50%; inset-inline-start: 13px; transform: translateY(-50%); color: #98a2b3; pointer-events: none; }
    .oc-module .oc-date-field .oc-input { padding-inline-start: 39px; cursor: pointer; }
    .oc-module .oc-detail-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
    .oc-module .oc-detail-grid > div { min-width: 0; padding: 15px 17px; border: 1px solid #e4eaf1; border-radius: 10px; background: #f9fbfd; }
    .oc-module .oc-detail-grid span, .oc-module .oc-detail-grid small { display: block; color: var(--oc-muted); font-size: 12px; }
    .oc-module .oc-detail-grid strong { display: block; margin-top: 4px; font-size: 14px; }
    .oc-module .oc-break { overflow-wrap: anywhere; }
    .oc-module .oc-timeline { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0; margin-bottom: 24px; }
    .oc-module .oc-timeline-item { position: relative; display: grid; justify-items: center; gap: 7px; color: #8897a9; text-align: center; }
    .oc-module .oc-timeline-item::before { content: ''; position: absolute; top: 20px; inset-inline-start: 50%; width: 100%; height: 2px; background: #dfe6ee; z-index: 0; }
    .oc-module .oc-timeline-item:last-child::before { display: none; }
    .oc-module .oc-timeline-item > i { z-index: 1; display: grid; place-items: center; width: 41px; height: 41px; border-radius: 50%; background: #eff3f7; }
    .oc-module .oc-timeline-item.is-done > i { color: #fff; background: var(--oc-primary); }
    .oc-module .oc-timeline-item span { font-size: 12px; }
    .oc-module .oc-timeline-item strong { font-size: 12px; font-weight: 600; color: var(--oc-ink); }
    .oc-module .oc-duration-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .oc-module .oc-duration-grid > div { padding: 15px; border-radius: 10px; background: #f4f8fb; text-align: center; }
    .oc-module .oc-duration-grid span { display: block; color: var(--oc-muted); font-size: 12px; }
    .oc-module .oc-duration-grid strong { display: block; margin-top: 3px; font-size: 18px; }
    .oc-module .oc-json-panel summary { display: flex; align-items: center; gap: 9px; padding: 17px 22px; cursor: pointer; font-weight: 600; }
    .oc-module .oc-json-panel summary > i { color: var(--oc-primary); }
    .oc-module .oc-json-panel pre { max-height: 500px; overflow: auto; margin: 0; padding: 20px; border-top: 1px solid var(--oc-border); background: #192536; color: #dce9f5; direction: ltr; text-align: left; font-size: 12px; line-height: 1.7; white-space: pre-wrap; overflow-wrap: anywhere; }
    .oc-module .oc-finance-panel { border-color: #c9e0d5; }
    .oc-module .oc-finance-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .oc-module .oc-finance-grid > div { padding: 16px; border: 1px solid #e1e9e5; border-radius: 10px; background: #f8fcfa; }
    .oc-module .oc-finance-grid span { display: block; color: var(--oc-muted); font-size: 12px; }
    .oc-module .oc-finance-grid strong { display: block; margin-top: 5px; font-size: 15px; }
    .oc-module .oc-billing-actions { display: flex; align-items: end; justify-content: space-between; gap: 18px; margin-top: 22px; padding-top: 20px; border-top: 1px solid var(--oc-border); }
    .oc-module .oc-approval-form { display: grid; grid-template-columns: 190px minmax(260px, 1fr) auto; align-items: end; gap: 12px; flex: 1; }
    .oc-module .oc-refund-complete { display: flex; align-items: center; gap: 13px; margin-top: 20px; padding: 17px; border: 1px solid #bce2ce; border-radius: 11px; background: #effaf5; color: #216449; }
    .oc-module .oc-refund-complete > i { font-size: 24px; }
    .oc-module .oc-refund-complete p { color: #507464; font-size: 12px; margin-top: 3px; }
    .oc-module .oc-correction { margin-top: 18px; padding: 15px 17px; border: 1px dashed #cbd9e6; border-radius: 10px; background: #fbfcfd; }
    .oc-module .oc-correction summary { cursor: pointer; color: var(--oc-primary); font-weight: 600; }
    .oc-module .oc-correction .oc-approval-form { margin-top: 16px; }
    .oc-module .oc-billing-modal { overflow: hidden; border: 0; border-radius: 16px; box-shadow: 0 24px 70px #172b3f33; }
    .oc-module .oc-billing-modal .modal-header, .oc-module .oc-billing-modal .modal-footer { padding: 18px 22px; border-color: var(--oc-border); }
    .oc-module .oc-billing-modal .modal-body { display: grid; gap: 20px; padding: 22px; }
    .oc-module .oc-billing-modal .modal-title { display: flex; align-items: center; gap: 9px; font-size: 18px; font-weight: 700; }
    .oc-module .oc-billing-modal .oc-approval-form { grid-template-columns: minmax(180px, .65fr) minmax(260px, 1.35fr); }
    .oc-module .oc-confirmation-grid > div:nth-last-child(-n+2) { border-color: #add9c2; background: #effaf5; }
    .oc-module .oc-input-group { display: flex; align-items: stretch; }
    .oc-module .oc-input-group .oc-input { border-radius: 0 9px 9px 0; }
    .oc-module .oc-input-group > span { display: grid; place-items: center; padding: 0 13px; border: 1px solid #ccd8e4; border-right: 0; border-radius: 9px 0 0 9px; background: #f4f7fa; color: var(--oc-muted); }
    .oc-module .oc-final-confirm { display: flex; align-items: flex-start; gap: 10px; padding: 15px 17px; border: 1px solid #efc27a; border-radius: 10px; background: #fff9ed; cursor: pointer; line-height: 1.8; }
    .oc-module .oc-final-confirm input { width: 18px; height: 18px; margin-top: 4px; accent-color: var(--oc-primary); flex: 0 0 auto; }
    .oc-module .oc-final-confirm.has-error { border-color: #e5484d; background: #fff1f2; box-shadow: 0 0 0 3px #e5484d1a; }
    .oc-module .oc-consent-error { display: flex; align-items: center; gap: 8px; margin-top: -10px; color: #b42318; font-size: 13px; font-weight: 600; }
    .oc-module .oc-consent-error[hidden] { display: none; }
    .oc-module .oc-adjustment-preview { padding: 13px 15px; border: 1px solid #f2c36b; border-radius: 10px; background: #fff8e8; color: #8a4b08; line-height: 1.8; }
    .oc-module .oc-financial-adjustment { margin-top: 7px; padding: 6px 8px; border-radius: 7px; background: #fff3d6; color: #8a4b08; font-weight: 600; line-height: 1.6; }
    .oc-module .oc-consultant-income { margin-top: 5px; color: #24724e; font-weight: 700; }
    .oc-module .oc-financial-filter { display:grid;grid-template-columns:1.25fr 1fr 1fr 1fr 1fr auto;gap:13px;align-items:end }
    .oc-module .oc-report-period { display:flex;align-items:center;justify-content:center;gap:8px;padding:12px 18px;border-top:1px solid var(--oc-border);background:#f8fbfd;color:var(--oc-muted) }.oc-module .oc-report-period i{color:var(--oc-primary)}
    .oc-module .oc-executive-grid { display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px }
    .oc-module .oc-executive-card { display:flex;align-items:center;gap:13px;min-width:0;padding:18px;border:1px solid var(--oc-border);border-radius:14px;background:#fff;box-shadow:0 4px 15px #25364b08 }.oc-module .oc-executive-card>i{display:grid;place-items:center;flex:0 0 45px;width:45px;height:45px;border-radius:13px;background:#edf5fb;color:var(--oc-primary);font-size:18px}.oc-module .oc-executive-card span,.oc-module .oc-executive-card small{display:block;color:var(--oc-muted);font-size:11px}.oc-module .oc-executive-card strong{display:block;margin:2px 0;font-size:18px;line-height:1.45}.oc-module .oc-executive-card.is-green>i{background:#eaf7ef;color:#258456}.oc-module .oc-executive-card.is-danger>i{background:#fff0f1;color:#b42318}.oc-module .oc-executive-card.is-purple>i{background:#f1ecfa;color:#7651a8}.oc-module .oc-executive-card.is-orange>i{background:#fff3e8;color:#b36c26}
    .oc-module .oc-call-quality-grid { display:grid;grid-template-columns:repeat(6,minmax(115px,1fr));gap:10px }.oc-module .oc-call-quality-grid>div{display:grid;grid-template-columns:34px 1fr;column-gap:8px;padding:13px;border:1px solid #e6edf3;border-radius:10px;background:#fbfcfe}.oc-module .oc-call-quality-grid i{grid-row:1/3;display:grid;place-items:center;width:32px;height:32px;border-radius:9px;background:#edf5fb;color:var(--oc-primary)}.oc-module .oc-call-quality-grid span{color:var(--oc-muted);font-size:10px}.oc-module .oc-call-quality-grid strong{font-size:15px}
    .oc-module .oc-result-breakdown { display:flex;flex-wrap:wrap;gap:8px;padding:0 24px 22px }.oc-module .oc-result-breakdown>div{display:flex;align-items:center;gap:9px;padding:7px 11px;border-radius:9px;background:#f1f5f9;color:var(--oc-muted);font-size:11px}.oc-module .oc-result-breakdown strong{color:var(--oc-ink);font-size:13px}
    .oc-module .oc-financial-table { min-width:1850px }.oc-module .oc-financial-table th,.oc-module .oc-financial-table td{padding-inline:13px}.oc-module .oc-financial-table .oc-net-income{color:#157347;font-weight:800}
    .oc-module .oc-report-columns { display:grid;grid-template-columns:1.45fr .75fr;gap:18px;align-items:start }.oc-module .oc-daily-chart{max-height:540px;overflow:auto}.oc-module .oc-daily-row{display:grid;grid-template-columns:85px minmax(160px,1fr) 130px 120px;align-items:center;gap:12px;padding:11px 18px;border-bottom:1px solid #edf1f6}.oc-module .oc-daily-row>span,.oc-module .oc-daily-row>small{color:var(--oc-muted);font-size:11px}.oc-module .oc-daily-row>strong{font-size:12px}.oc-module .oc-bars{display:grid;gap:4px}.oc-module .oc-bars i,.oc-module .oc-bars b{display:block;width:var(--bar);height:7px;border-radius:6px;background:linear-gradient(90deg,#2b8bc8,#6cc2eb);min-width:3px}.oc-module .oc-bars b{height:5px;background:linear-gradient(90deg,#7651a8,#b59adc)}.oc-module .oc-chart-legend{display:flex;gap:18px;padding:13px 18px;color:var(--oc-muted);font-size:10px}.oc-module .oc-chart-legend span{display:flex;align-items:center;gap:6px}.oc-module .oc-chart-legend i,.oc-module .oc-chart-legend b{width:18px;height:6px;border-radius:5px;background:#2b8bc8}.oc-module .oc-chart-legend b{background:#7651a8}
    .oc-module .oc-daily-row{grid-template-columns:120px 90px minmax(360px,1fr);gap:14px;padding:15px 18px}.oc-module .oc-daily-date{display:grid;gap:5px}.oc-module .oc-daily-date strong{font-size:13px;color:var(--oc-ink)}.oc-module .oc-daily-date small{font-size:10px;color:var(--oc-muted);line-height:1.7}.oc-module .oc-daily-finance{display:grid;grid-template-columns:repeat(3,minmax(110px,1fr));gap:7px}.oc-module .oc-daily-finance>div{display:grid;gap:3px;padding:8px 9px;border:1px solid #e4ebf1;border-radius:9px;background:#f8fafc}.oc-module .oc-daily-finance span{font-size:9px;color:#6c7f91}.oc-module .oc-daily-finance strong{font-size:11px;color:#263a4d;white-space:nowrap}.oc-module .oc-daily-finance .is-danger{background:#fff7f7;border-color:#f4d9d9}.oc-module .oc-daily-finance .is-danger strong{color:#bd3c4b}.oc-module .oc-daily-finance .is-warning{background:#fffaf0;border-color:#f4e4bd}.oc-module .oc-daily-finance .is-warning strong{color:#a96d00}.oc-module .oc-daily-finance .is-expert{background:#faf7ff;border-color:#e7dcf6}.oc-module .oc-daily-finance .is-expert strong{color:#7650a5}.oc-module .oc-daily-finance .is-profit{background:#f3fbf7;border-color:#d4ebdd}.oc-module .oc-daily-finance .is-profit strong{color:#19784a}
    .oc-module .oc-report-insights{gap:18px}.oc-module .oc-daily-panel{overflow:hidden;background:linear-gradient(180deg,#fff 0,#f8fbfd 100%)}.oc-module .oc-daily-heading{background:linear-gradient(135deg,#f3f9fd,#fff);border-bottom:1px solid #dfeaf2}.oc-module .oc-daily-heading>div:first-child{display:grid;gap:4px}.oc-module .oc-daily-legend{display:flex;align-items:center;gap:16px;flex-wrap:wrap}.oc-module .oc-daily-legend span{display:flex;align-items:center;gap:6px;color:var(--oc-muted);font-size:11px}.oc-module .oc-daily-legend i{width:10px;height:10px;border-radius:3px}.oc-module .oc-daily-legend .is-refund i{background:#e36b75}.oc-module .oc-daily-legend .is-expert i{background:#8565bc}.oc-module .oc-daily-legend .is-profit i{background:#38a36d}.oc-module .oc-daily-chart{display:grid;gap:14px;max-height:760px;padding:18px;overflow:auto}.oc-module .oc-daily-card{border:1px solid #dce7ee;border-radius:15px;background:#fff;box-shadow:0 7px 22px rgba(31,64,89,.06);overflow:hidden;transition:border-color .2s,box-shadow .2s,transform .2s}.oc-module .oc-daily-card:hover{border-color:#b8d5e6;box-shadow:0 11px 28px rgba(31,64,89,.1);transform:translateY(-1px)}.oc-module .oc-daily-card-head{display:grid;grid-template-columns:minmax(170px,1fr) auto auto;align-items:center;gap:18px;padding:17px 18px 13px}.oc-module .oc-daily-date{display:flex;align-items:center;gap:11px}.oc-module .oc-daily-date>i{display:grid;place-items:center;width:39px;height:39px;border-radius:11px;background:#eaf5fc;color:var(--oc-primary);font-size:17px}.oc-module .oc-daily-date>div{display:grid;gap:2px}.oc-module .oc-daily-date strong{font-size:15px}.oc-module .oc-daily-date small{font-size:10px;color:var(--oc-muted)}.oc-module .oc-daily-activity{display:flex;gap:7px;flex-wrap:wrap;justify-content:center}.oc-module .oc-daily-activity span{display:flex;align-items:center;gap:5px;padding:6px 8px;border-radius:8px;background:#f1f5f8;color:#63778a;font-size:10px}.oc-module .oc-daily-activity i{color:#5186a7}.oc-module .oc-daily-total{text-align:left;padding-inline-start:18px;border-inline-start:1px solid #e5edf2}.oc-module .oc-daily-total span{display:block;color:var(--oc-muted);font-size:10px}.oc-module .oc-daily-total strong{display:block;margin-top:2px;color:#183b52;font-size:17px;white-space:nowrap}.oc-module .oc-daily-composition-wrap{display:grid;gap:6px;padding:0 18px 14px}.oc-module .oc-daily-composition-scale{height:14px;border-radius:20px;background:#edf2f5;overflow:hidden}.oc-module .oc-daily-composition-scale>div{display:flex;height:100%;min-width:3px;border-radius:inherit;overflow:hidden;background:#dbe8f0}.oc-module .oc-daily-composition-scale span{display:block;height:100%;min-width:0}.oc-module .oc-daily-composition-scale .is-refund{background:linear-gradient(90deg,#d95663,#ef8a91)}.oc-module .oc-daily-composition-scale .is-expert{background:linear-gradient(90deg,#7353a7,#9b7bcd)}.oc-module .oc-daily-composition-scale .is-profit{background:linear-gradient(90deg,#238957,#51bd82)}.oc-module .oc-daily-composition-wrap>small{color:#8695a4;font-size:9px}.oc-module .oc-daily-finance{grid-template-columns:repeat(7,minmax(115px,1fr));gap:8px;padding:13px 18px;background:#f7fafc;border-block:1px solid #e8eff3}.oc-module .oc-daily-finance>div{display:grid;grid-template-columns:31px minmax(0,1fr);column-gap:8px;align-items:center;padding:9px 10px;background:#fff;border-radius:10px}.oc-module .oc-daily-finance>div>i{grid-row:1/3;display:grid;place-items:center;width:29px;height:29px;border-radius:8px;background:#eef4f7;color:#52748a}.oc-module .oc-daily-finance span{font-size:9px}.oc-module .oc-daily-finance strong{font-size:11px;overflow:hidden;text-overflow:ellipsis}.oc-module .oc-daily-finance .is-gross>i{background:#e9f4fb;color:#277fae}.oc-module .oc-daily-finance .is-confirmed>i,.oc-module .oc-daily-finance .is-refund>i{background:#fff0f1;color:#c44854}.oc-module .oc-daily-finance .is-warning>i{background:#fff6e8;color:#ae741e}.oc-module .oc-daily-finance .is-net>i{background:#edf7fb;color:#287b9e}.oc-module .oc-daily-finance .is-expert>i{background:#f1ecfa;color:#7651a8}.oc-module .oc-daily-finance .is-profit>i{background:#eaf7ef;color:#258456}.oc-module .oc-daily-equation{display:flex;align-items:center;justify-content:flex-end;gap:7px;flex-wrap:wrap;padding:11px 18px;color:#7b8b9a;font-size:10px}.oc-module .oc-daily-equation strong{color:#314b5d}.oc-module .oc-daily-equation i{font-size:9px;color:#9aa8b4}
    .oc-module .oc-survey-summary{text-align:center;padding:8px 0 20px}.oc-module .oc-survey-summary strong{display:block;color:#b36c26;font-size:42px;line-height:1.2}.oc-module .oc-survey-summary span,.oc-module .oc-survey-summary small{display:block;color:var(--oc-muted);font-size:11px}.oc-module .oc-survey-bars{display:grid;gap:10px}.oc-module .oc-survey-bars>div{display:grid;grid-template-columns:55px 1fr 65px;align-items:center;gap:8px;font-size:11px}.oc-module .oc-survey-bars i{height:8px;border-radius:8px;background:#edf1f5;overflow:hidden}.oc-module .oc-survey-bars b{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#f0aa3c,#ffd276)}.oc-module .oc-survey-bars strong{text-align:left}.oc-module .oc-survey-bars small{color:var(--oc-muted);font-weight:400}
    .oc-module .oc-survey-call-score { display:flex;align-items:center;justify-content:center;gap:10px;min-height:72px;color:#b36c26 }.oc-module .oc-survey-call-score>strong{font-size:34px;line-height:1}.oc-module .oc-survey-call-score>span{color:var(--oc-muted);font-size:12px}.oc-module .oc-survey-call-score>div{display:flex;gap:4px;font-size:18px}
    .oc-module .oc-case-panel { overflow: hidden; border-color: #bad6e8; }
    .oc-module .oc-case-hero { display:flex;align-items:center;justify-content:space-between;gap:18px;padding:20px 22px;border-bottom:1px solid var(--oc-border); }
    .oc-module .oc-case-hero.is-open { background:linear-gradient(135deg,#eef8ff,#f9fcff); }.oc-module .oc-case-hero.is-completed{background:linear-gradient(135deg,#edf9f3,#fafffc);border-bottom-color:#cce5d8}
    .oc-module .oc-case-state{display:flex;align-items:center;gap:13px}.oc-module .oc-case-state>i{display:grid;place-items:center;width:48px;height:48px;border-radius:14px;background:#fff;color:var(--oc-primary);font-size:21px;box-shadow:0 4px 14px #263b5012}.oc-module .oc-case-hero.is-completed .oc-case-state>i{color:#24724e}.oc-module .oc-case-state small,.oc-module .oc-case-state span{display:block;color:var(--oc-muted);font-size:12px}.oc-module .oc-case-state strong{display:block;margin:2px 0;font-size:17px}
    .oc-module .oc-case-form{padding:22px}.oc-module .oc-case-form-title,.oc-module .oc-case-form-actions{display:flex;align-items:center;justify-content:space-between;gap:18px}.oc-module .oc-case-form-title>div{display:grid;grid-template-columns:auto 1fr;column-gap:9px}.oc-module .oc-case-form-title i{grid-row:1/3;color:var(--oc-primary);font-size:20px}.oc-module .oc-case-form-title small{color:var(--oc-muted);font-size:12px}.oc-module .oc-case-form-grid{display:grid;grid-template-columns:1fr 1.4fr 1fr;gap:14px;margin-top:20px}.oc-module .oc-case-report-field{grid-column:1/-1}.oc-module .oc-report-counter{display:flex;justify-content:space-between;color:var(--oc-muted);font-size:11px;margin-top:6px}.oc-module .oc-case-form-actions{margin-top:17px;padding-top:17px;border-top:1px solid var(--oc-border)}.oc-module .oc-case-form-actions>span{color:var(--oc-muted);font-size:12px}.oc-module .oc-case-form-actions>span i{color:#24724e;margin-left:5px}
    .oc-module .oc-case-history{display:grid;gap:9px;padding:16px 20px}.oc-module .oc-case-report{border:1px solid #dfe7ef;border-radius:11px;background:#fff;overflow:hidden}.oc-module .oc-case-report.is-previous{background:#fbfcfe}.oc-module .oc-case-report summary{display:grid;grid-template-columns:42px minmax(0,1fr) auto 18px;align-items:center;gap:12px;padding:14px 16px;cursor:pointer;list-style:none}.oc-module .oc-case-report summary::-webkit-details-marker{display:none}.oc-module .oc-case-report-icon{display:grid;place-items:center;width:40px;height:40px;border-radius:11px;background:#eaf5fc;color:var(--oc-primary)}.oc-module .oc-case-report-main strong,.oc-module .oc-case-report-main small{display:block}.oc-module .oc-case-report-main small,.oc-module .oc-case-report time{color:var(--oc-muted);font-size:11px}.oc-module .oc-case-report summary>i{color:#8b9bad;transition:transform .2s}.oc-module .oc-case-report[open] summary>i{transform:rotate(180deg)}.oc-module .oc-case-report-body{padding:17px 20px;border-top:1px solid #e6edf3;background:#f9fbfd}.oc-module .oc-case-report-body p{margin:15px 0 0;white-space:pre-wrap;line-height:2;color:#344054}.oc-module .oc-case-report-meta{display:flex;gap:10px;flex-wrap:wrap}.oc-module .oc-case-report-meta span{padding:6px 9px;border-radius:7px;background:#edf3f7;color:var(--oc-muted);font-size:11px}.oc-module .oc-case-confirm-summary{display:grid;grid-template-columns:1fr 1fr;gap:10px}.oc-module .oc-case-confirm-summary span{padding:12px;border-radius:9px;background:#f4f7fa;color:var(--oc-muted)}
    .oc-module .oc-appointment-page { gap: 18px; }
    .oc-module .oc-appointment-page > .oc-panel { border-color: #d9e4ed; box-shadow: 0 8px 28px rgba(37,54,75,.055); }
    .oc-module .oc-appointment-page .oc-detail-grid > div { position: relative; padding: 16px 18px; border: 1px solid #e2eaf1; border-radius: 12px; background: linear-gradient(145deg,#fff,#f8fbfd); }
    .oc-module .oc-appointment-page .oc-detail-grid > div::before { content: ''; position: absolute; inset-block: 14px; inset-inline-start: 0; width: 3px; border-radius: 4px; background: #8dc8eb; }
    .oc-module .oc-follow-up-fields { display: grid; grid-template-columns: minmax(150px,1fr) 105px; gap: 8px; }
    .oc-module .oc-follow-up-fields .oc-date-field { min-width: 0; }
    .oc-module .oc-follow-up-fields .oc-date-field .oc-input { padding-inline-start: 38px; }
    .oc-module .oc-note-panel { border-color: #d8dfef; background: linear-gradient(145deg,#fff,#fcfbff); }
    .oc-module .oc-note-form { padding: 20px 22px; }
    .oc-module .oc-note-view { display: grid; grid-template-columns: minmax(190px,.35fr) minmax(0,1fr); gap: 20px; padding: 20px 22px; }
    .oc-module .oc-note-author { display: flex; align-items: center; gap: 11px; padding-inline-end: 20px; border-inline-end: 1px solid #e2e8f0; }
    .oc-module .oc-note-author strong,.oc-module .oc-note-author small { display: block; }
    .oc-module .oc-note-author small { margin-top: 3px; color: var(--oc-muted); font-size: 11px; }
    .oc-module .oc-note-view > p { padding: 13px 16px; border-radius: 10px; background: #f6f4fb; color: #344054; line-height: 2; white-space: pre-wrap; }
    .oc-module .oc-note-empty { padding-block: 28px; }
    .oc-module .oc-appointment-stats { grid-template-columns: repeat(4,minmax(0,1fr)); gap: 12px; }
    .oc-module .oc-appointment-stats .oc-stat { min-height: 98px; padding: 15px 16px; gap: 12px; overflow: visible; }
    .oc-module .oc-appointment-stats .oc-stat-icon { width: 40px; height: 40px; border-radius: 12px; font-size: 17px; }
    .oc-module .oc-appointment-stats .oc-stat-content { min-width: 0; }
    .oc-module .oc-appointment-stats .oc-stat strong { margin-top: 2px; font-size: 19px; line-height: 1.35; overflow-wrap: anywhere; }
    .oc-module .oc-appointment-stats .oc-stat-detail { display: block; margin-top: 4px; color: var(--oc-muted); font-size: 10px; line-height: 1.6; white-space: nowrap; }
    .oc-module .oc-finance-panel { border-color: #cfe3d8; }
    .oc-module .oc-finance-panel > .oc-panel-header { background: linear-gradient(135deg,#f0faf5,#fbfefd); }
    .oc-module .oc-finance-grid > div { position: relative; min-height: 82px; padding: 15px 17px; border-color: #dbe9e2; background: linear-gradient(145deg,#fff,#f5faf7); overflow: hidden; }
    .oc-module .oc-finance-grid > div::after { content: ''; position: absolute; inset-inline-start: 0; inset-block: 13px; width: 3px; border-radius: 3px; background: #7bc09c; }
    .oc-module .oc-finance-grid strong { font-size: 14px; color: #254437; }
    @media (max-width: 1199px) { .oc-module .oc-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }.oc-module .oc-executive-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.oc-module .oc-financial-filter{grid-template-columns:repeat(3,minmax(0,1fr))}.oc-module .oc-call-quality-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.oc-module .oc-report-columns{grid-template-columns:1fr}.oc-module .oc-daily-finance{grid-template-columns:repeat(4,minmax(135px,1fr))} }
    @media (max-width: 767px) {
        .oc-module { padding-top: 18px; }
        .oc-module .oc-header { align-items: flex-start; flex-direction: column; gap: 14px; }
        .oc-module .oc-title { font-size: 20px; }
        .oc-module .oc-tabs { gap: 4px; }
        .oc-module .oc-tab { flex: 1 1 auto; font-size: 12px; padding: 9px 10px; }
        .oc-module .oc-panel-header { padding: 16px; }
        .oc-module .oc-panel-body { padding: 18px 16px; }
        .oc-module .oc-grid { grid-template-columns: minmax(0, 1fr); gap: 20px; }
        .oc-module .oc-filter-grid, .oc-module .oc-detail-grid { grid-template-columns: minmax(0, 1fr); }
        .oc-module .oc-filter-actions { flex-wrap: wrap; }
        .oc-module .oc-timeline { grid-template-columns: minmax(0, 1fr); gap: 15px; }
        .oc-module .oc-timeline-item { grid-template-columns: 41px 1fr; justify-items: start; align-items: center; text-align: right; }
        .oc-module .oc-timeline-item::before { top: 40px; inset-inline-start: 20px; width: 2px; height: calc(100% + 15px); }
        .oc-module .oc-duration-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .oc-module .oc-finance-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .oc-module .oc-billing-actions { align-items: stretch; flex-direction: column; }
        .oc-module .oc-approval-form { grid-template-columns: minmax(0, 1fr); width: 100%; }
        .oc-module .oc-billing-modal .oc-approval-form { grid-template-columns: minmax(0, 1fr); }
        .oc-module .oc-stats { gap: 10px; }
        .oc-module .oc-stat { padding: 16px 12px; flex-direction: column; align-items: flex-start; gap: 10px; }
        .oc-module .oc-executive-grid,.oc-module .oc-financial-filter,.oc-module .oc-call-quality-grid{grid-template-columns:1fr}.oc-module .oc-financial-filter .oc-actions{width:100%}.oc-module .oc-financial-filter .oc-actions>*{flex:1}.oc-module .oc-report-period{flex-wrap:wrap}.oc-module .oc-daily-row{grid-template-columns:1fr}.oc-module .oc-daily-heading{align-items:flex-start;flex-direction:column}.oc-module .oc-daily-legend{gap:9px}.oc-module .oc-daily-chart{padding:10px;gap:10px}.oc-module .oc-daily-card-head{grid-template-columns:1fr;gap:11px;padding:14px}.oc-module .oc-daily-activity{justify-content:flex-start}.oc-module .oc-daily-total{text-align:right;padding:10px 0 0;border-inline-start:0;border-top:1px solid #e5edf2}.oc-module .oc-daily-composition-wrap{padding:0 14px 12px}.oc-module .oc-daily-finance{grid-template-columns:repeat(2,minmax(125px,1fr));padding:10px}.oc-module .oc-daily-finance>div{grid-template-columns:26px minmax(0,1fr);padding:8px 7px}.oc-module .oc-daily-finance>div>i{width:25px;height:25px}.oc-module .oc-daily-equation{justify-content:flex-start;padding:10px 14px}.oc-module .oc-executive-card strong{font-size:16px}
        .oc-module .oc-stat-icon { width: 36px; height: 36px; font-size: 17px; border-radius: 10px; }
        .oc-module .oc-stat strong { font-size: 25px; }
        .oc-module .oc-toolbar { align-items: stretch; flex-direction: column; }
        .oc-module .oc-search { flex: 1 1 auto; width: 100%; }
        .oc-module .oc-savebar { padding: 16px; }
        .oc-module .oc-savebar .oc-actions { width: 100%; }
        .oc-module .oc-savebar .oc-btn { flex: 1; }
        .oc-module .oc-table th, .oc-module .oc-table td { padding: 13px 14px; }
        .oc-module .oc-pagination { align-items: stretch; flex-direction: column; padding: 14px 16px; }
        .oc-module .oc-pagination-summary { text-align: center; white-space: normal; }
        .oc-module .oc-pager { justify-content: center; width: 100%; }
        .oc-module .oc-page-list { min-width: 0; overflow-x: auto; padding: 2px; scrollbar-width: thin; }
        .oc-module .oc-page-link, .oc-module .oc-page-current, .oc-module .oc-page-gap { flex: 0 0 auto; }
        .oc-module .oc-notice { padding: 14px; }
        .oc-module .oc-consultant-card{grid-template-columns:1fr}.oc-module .oc-consultant-person{border-inline-end:0;border-bottom:1px solid var(--oc-border)}.oc-module .oc-consultant-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.oc-module .oc-dashboard-filter{grid-template-columns:1fr}.oc-module .oc-filter-checks{grid-column:auto}.oc-module .oc-dashboard-stats{grid-template-columns:repeat(2,minmax(0,1fr))}
        .oc-module .oc-day-navigator{grid-template-columns:1fr 1fr}.oc-module .oc-date-picker-form{grid-column:1/-1;grid-row:1}.oc-module .oc-nav-arrow{grid-row:2}.oc-module .oc-month-picker{align-items:stretch;flex-direction:column}.oc-module .oc-month-picker .oc-field{width:100%}
        .oc-module .oc-case-form-grid{grid-template-columns:1fr}.oc-module .oc-case-report-field{grid-column:auto}.oc-module .oc-case-hero,.oc-module .oc-case-form-actions{align-items:stretch;flex-direction:column}.oc-module .oc-case-report summary{grid-template-columns:40px minmax(0,1fr) 15px}.oc-module .oc-case-report time{grid-column:2}.oc-module .oc-case-report summary>i{grid-column:3;grid-row:1/3}
        .oc-module .oc-follow-up-fields{grid-template-columns:minmax(0,1fr) 95px}.oc-module .oc-note-view{grid-template-columns:1fr;padding:17px 16px;gap:14px}.oc-module .oc-note-author{border-inline-end:0;border-bottom:1px solid #e2e8f0;padding:0 0 14px}.oc-module .oc-appointment-stats .oc-stat{min-height:92px;padding:13px;flex-direction:row;align-items:center;gap:10px}.oc-module .oc-appointment-stats .oc-stat strong{font-size:17px}.oc-module .oc-appointment-stats .oc-stat-detail{white-space:normal}.oc-module .oc-appointment-page .oc-detail-grid>div{padding:13px 15px}
    }
    @media (max-width: 420px) {
        .oc-module .oc-pagination { padding-inline: 10px; }
        .oc-module .oc-pager, .oc-module .oc-page-list { gap: 4px; }
        .oc-module .oc-page-nav { min-width: 38px; width: 38px; padding: 0; }
        .oc-module .oc-page-nav span { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
    }
    @media (prefers-reduced-motion: reduce) { .oc-module * { scroll-behavior: auto; transition: none; } .oc-page-loader *{animation:none!important;transition:none!important} }
</style>
@endpush
@endonce
