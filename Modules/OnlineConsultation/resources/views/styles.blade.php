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
    .oc-module .oc-pagination:empty { display: none; }
    .oc-module .oc-pagination:not(:empty) { padding: 16px 20px; border-top: 1px solid var(--oc-border); }
    .oc-module .oc-schedule .oc-input { width: 145px; direction: ltr; text-align: center; }
    .oc-module .oc-schedule td:first-child { min-width: 105px; font-weight: 600; }
    .oc-module .oc-schedule label { display: inline-flex; align-items: center; gap: 7px; margin: 0; cursor: pointer; white-space: nowrap; }
    .oc-module .oc-schedule input.oc-check-input { margin: 0; }
    .oc-module .oc-ltr { direction: ltr; unicode-bidi: isolate; }
    .oc-module .oc-cell-sub { display: block; margin-top: 3px; color: var(--oc-muted); font-size: 11px; }
    .oc-module .oc-count-badge { display: inline-grid; place-items: center; min-width: 34px; height: 30px; padding: 0 8px; border-radius: 9px; background: #e9f4fc; color: var(--oc-primary); font-size: 15px; font-weight: 700; }
    .oc-module .oc-text-success { display: block; color: #24724e; font-weight: 600; }
    .oc-module .oc-text-danger { color: #b42318; font-weight: 600; }
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
        .oc-module .oc-stat-icon { width: 36px; height: 36px; font-size: 17px; border-radius: 10px; }
        .oc-module .oc-stat strong { font-size: 25px; }
        .oc-module .oc-toolbar { align-items: stretch; flex-direction: column; }
        .oc-module .oc-search { flex: 1 1 auto; width: 100%; }
        .oc-module .oc-savebar { padding: 16px; }
        .oc-module .oc-savebar .oc-actions { width: 100%; }
        .oc-module .oc-savebar .oc-btn { flex: 1; }
        .oc-module .oc-table th, .oc-module .oc-table td { padding: 13px 14px; }
        .oc-module .oc-notice { padding: 14px; }
        .oc-module .oc-consultant-card{grid-template-columns:1fr}.oc-module .oc-consultant-person{border-inline-end:0;border-bottom:1px solid var(--oc-border)}.oc-module .oc-consultant-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.oc-module .oc-dashboard-filter{grid-template-columns:1fr}.oc-module .oc-filter-checks{grid-column:auto}.oc-module .oc-dashboard-stats{grid-template-columns:repeat(2,minmax(0,1fr))}
        .oc-module .oc-day-navigator{grid-template-columns:1fr 1fr}.oc-module .oc-date-picker-form{grid-column:1/-1;grid-row:1}.oc-module .oc-nav-arrow{grid-row:2}.oc-module .oc-month-picker{align-items:stretch;flex-direction:column}.oc-module .oc-month-picker .oc-field{width:100%}
    }
    @media (prefers-reduced-motion: reduce) { .oc-module * { scroll-behavior: auto; transition: none; } .oc-page-loader *{animation:none!important;transition:none!important} }
</style>
@endpush
@endonce
