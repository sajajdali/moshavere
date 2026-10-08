import React, { useEffect } from 'react';

/**
 * لودینگ تمام صفحه.
 * روی کل صفحه می نشیند تا کاملاً مشخص باشد چیزی در حال بارگذاری است.
 */
export function Loading({ label = 'در حال بارگذاری…', hint }) {
  // تا وقتی لودینگ باز است، صفحه پشت آن اسکرول نشود
  useEffect(() => {
    const prev = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    return () => { document.body.style.overflow = prev; };
  }, []);

  return (
    <div className="na-overlay" role="status" aria-busy="true" aria-live="polite">
      <div className="na-overlay__box">
        <span className="na-spinner na-spinner--lg" aria-hidden="true" />

        {label && (
          <span className="na-overlay__text">
            {label}
            <span className="na-dots" aria-hidden="true"><i /><i /><i /></span>
          </span>
        )}

        {hint && <span className="na-overlay__hint">{hint}</span>}
      </div>
    </div>
  );
}

/**
 * اسکلتون درون صفحه، برای بارگذاری بخشی از صفحه (نه کل آن).
 */
export function LoadingBlock({ rows = 3, height = 92, label = null }) {
  return (
    <div className="na-wrap" aria-busy="true" aria-live="polite"
      style={{ padding: '28px 16px', display: 'grid', gap: 14 }}>
      {label && (
        <div style={{
          display: 'flex', alignItems: 'center', gap: 9,
          fontSize: 13, color: 'var(--c-muted)', fontWeight: 600,
        }}>
          <span className="na-spinner" aria-hidden="true" />
          <span>{label}</span>
        </div>
      )}

      {Array.from({ length: rows }).map((_, i) => (
        <div key={i} className="na-shimmer"
          style={{ height, '--na-delay': `${i * 90}ms` }} />
      ))}
    </div>
  );
}

/**
 * بارگذاری یک بخش از صفحه (نه کل صفحه): شکل کلی همان بخش با بلر و پالس
 * نمایش داده می شود، بدون نوشته یا چرخنده اضافه، تا شلوغ نشود.
 * برای بارگذاری هایی که پیام متنی هم لازم دارند، label را پر کنید.
 */
export function SectionLoading({ label, children }) {
  return (
    <div className="na-section-loading" aria-busy="true" aria-live="polite">
      <div className="na-section-loading__content" aria-hidden="true">{children}</div>
      {label && (
        <span className="na-section-loading__badge">
          <span className="na-spinner" aria-hidden="true" />
          {label}
        </span>
      )}
    </div>
  );
}

export function ErrorBox({ error, onRetry }) {
  return (
    <div className="na-wrap" style={{ padding: '40px 16px' }}>
      <div className="na-card" style={{ textAlign: 'center', borderColor: 'var(--c-danger)', background: 'var(--c-danger-soft)' }}>
        <strong style={{ display: 'block', fontSize: 15, color: 'var(--c-danger)', marginBottom: 8 }}>
          مشکلی پیش آمد
        </strong>
        <p style={{ margin: '0 0 14px', fontSize: 13, color: 'var(--c-ink-2)', lineHeight: 1.9 }}>
          {error?.message ?? 'ارتباط با سرور برقرار نشد.'}
        </p>
        {onRetry && <button type="button" className="na-btn" onClick={onRetry}>تلاش دوباره</button>}
      </div>
    </div>
  );
}

export function Empty({ title = 'موردی یافت نشد', hint }) {
  return (
    <div className="na-card" style={{ textAlign: 'center', padding: '34px 18px' }}>
      <strong style={{ display: 'block', fontSize: 15, marginBottom: 6 }}>{title}</strong>
      {hint && <p style={{ margin: 0, fontSize: 13, color: 'var(--c-muted)', lineHeight: 1.9 }}>{hint}</p>}
    </div>
  );
}

export function Placeholder({ label = 'عکس', style, className = '' }) {
  return <div className={`na-ph${className ? ' ' + className : ''}`} style={style}>{label}</div>;
}
