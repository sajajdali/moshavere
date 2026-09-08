<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>پایان دوره پشتیبانی</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(circle at 15% 15%,#fff7ed 0,transparent 35%),linear-gradient(145deg,#f8fafc,#eef2ff);font-family:Tahoma,Arial,sans-serif;color:#172033}.wrap{width:min(680px,100%);position:relative}.card{position:relative;overflow:hidden;padding:54px 42px 42px;text-align:center;border:1px solid rgba(239,68,68,.16);border-radius:30px;background:rgba(255,255,255,.94);box-shadow:0 28px 80px rgba(15,23,42,.14)}.glow{position:absolute;width:260px;height:260px;top:-180px;right:-80px;border-radius:50%;background:#ef4444;opacity:.09}.icon{width:88px;height:88px;margin:0 auto 24px;display:grid;place-items:center;border-radius:26px;background:linear-gradient(145deg,#fee2e2,#fff1f2);color:#dc2626;font-size:42px;box-shadow:0 12px 30px rgba(220,38,38,.14)}h1{margin:0 0 14px;font-size:30px}p{max-width:500px;margin:0 auto;color:#64748b;font-size:16px;line-height:2}.date{display:inline-flex;align-items:center;gap:10px;margin:26px 0;padding:12px 18px;border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0}.date strong{color:#dc2626;font-size:18px}.actions{display:flex;justify-content:center;gap:12px;flex-wrap:wrap}.btn{display:inline-flex;align-items:center;justify-content:center;padding:13px 22px;border-radius:12px;text-decoration:none;font-size:14px;font-weight:700}.primary{color:#fff;background:linear-gradient(135deg,#dc2626,#ef4444);box-shadow:0 10px 24px rgba(220,38,38,.24)}.secondary{color:#475569;background:#f1f5f9}@media(max-width:520px){.card{padding:42px 22px 30px}h1{font-size:24px}.btn{width:100%}}
    </style>
</head>
<body>
<main class="wrap"><section class="card"><div class="glow"></div>
    <div class="icon">⌛</div>
    <h1>دوره پشتیبانی این سایت به پایان رسیده است</h1>
    <p>برای استفاده دوباره از امکانات سامانه، ابتدا هزینه پشتیبانی و سرور را تمدید کنید.</p>
    @if ($expiredTenant->expires_at)
        <div class="date"><span>تاریخ پایان پشتیبانی:</span><strong>{{ verta($expiredTenant->expires_at)->format('Y/m/d') }}</strong></div>
    @endif
    <div class="actions">
        <a class="btn primary" href="/shemiranWebLogin">ورود مدیریت و تمدید</a>
        <a class="btn secondary" href="javascript:location.reload()">بررسی مجدد وضعیت</a>
    </div>
</section></main>
</body></html>
