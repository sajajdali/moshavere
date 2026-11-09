import React, { useEffect, useState } from 'react';

/**
 * دکمه رفتن به درگاه پرداخت که فقط یک بار درخواست می فرستد.
 * کلیک های بعدی تا ترک صفحه نادیده گرفته می شوند؛ سرور هم درخواست های هم زمان یک نوبت را
 * قفل می کند و همان توکن قبلی را برمی گرداند.
 */
export default function PayLink({ href, children, busyLabel = 'در حال انتقال به درگاه…', style, ...rest }) {
  const [busy, setBusy] = useState(false);

  // با دکمه بازگشت مرورگر صفحه از bfcache برمی گردد؛ دکمه باید دوباره فعال شود
  useEffect(() => {
    const onShow = (e) => { if (e.persisted) setBusy(false); };
    window.addEventListener('pageshow', onShow);
    return () => window.removeEventListener('pageshow', onShow);
  }, []);

  const go = (e) => {
    e.preventDefault();
    if (busy) return;
    setBusy(true);
    window.location.assign(href);
  };

  return (
    <a href={href} onClick={go} aria-disabled={busy} {...rest}
      style={{ ...style, ...(busy ? { opacity: 0.7, cursor: 'wait', pointerEvents: 'none' } : null) }}>
      {busy ? busyLabel : children}
    </a>
  );
}
