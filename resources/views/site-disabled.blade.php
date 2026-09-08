<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>سایت موقتاً غیرفعال است</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(circle at 85% 10%,#dbeafe 0,transparent 34%),linear-gradient(145deg,#f8fafc,#eef2ff);font-family:Tahoma,Arial,sans-serif;color:#172033}.wrap{width:min(680px,100%)}.card{position:relative;overflow:hidden;padding:54px 42px 42px;text-align:center;border:1px solid rgba(37,99,235,.15);border-radius:30px;background:rgba(255,255,255,.95);box-shadow:0 28px 80px rgba(15,23,42,.14)}.glow{position:absolute;width:270px;height:270px;top:-190px;left:-80px;border-radius:50%;background:#2563eb;opacity:.09}.icon{width:88px;height:88px;margin:0 auto 24px;display:grid;place-items:center;border-radius:26px;background:linear-gradient(145deg,#dbeafe,#eff6ff);color:#2563eb;font-size:40px;box-shadow:0 12px 30px rgba(37,99,235,.15)}h1{margin:0 0 14px;font-size:29px}.message{max-width:540px;margin:0 auto;padding:18px 20px;border-radius:16px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-size:16px;line-height:2;white-space:pre-line}.hint{margin:20px 0 0;color:#94a3b8;font-size:12px}@media(max-width:520px){.card{padding:42px 22px 30px}h1{font-size:23px}}
    </style>
</head>
<body>
<main class="wrap"><section class="card"><div class="glow"></div>
    <div class="icon">⚙</div>
    <h1>این سایت موقتاً غیرفعال است</h1>
    <div class="message">{{ $disabledTenant->disabled_message ?: 'این سایت موقتاً غیرفعال شده است. لطفاً با پشتیبانی تماس بگیرید.' }}</div>
    <p class="hint">پس از فعال‌شدن سایت، دسترسی‌ها به‌صورت خودکار برقرار می‌شوند.</p>
</section></main>
</body></html>
