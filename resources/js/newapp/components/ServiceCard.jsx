import React, { useState } from 'react';
import { money } from '../lib/format';

export default function ServiceCard({ service, onPick }) {
  // اگر فایل تصویر پیدا نشود، به نشان حرفی برمی گردیم
  const [imageFailed, setImageFailed] = useState(false);
  const showImage = Boolean(service.image) && !imageFailed;

  return (
    <article className="na-svc-card">
      <div style={{ display: 'flex', alignItems: 'flex-start', gap: 12 }}>
        {showImage ? (
          <img
            src={service.image} alt="" loading="lazy"
            onError={() => setImageFailed(true)}
            className="na-svc-card__mark na-svc-card__mark--img"
          />
        ) : (
          <span className="na-svc-card__mark">{(service.name ?? '').trim().slice(0, 2)}</span>
        )}
        <span style={{ flex: '1 1 auto', minWidth: 0 }}>
          <strong className="na-svc-card__name">{service.name}</strong>
          <span className="na-svc-card__meta">
            {[service.duration, service.price ? money(service.price) + ' ریال' : null]
              .filter(Boolean).join(' · ')}
          </span>
        </span>
      </div>

      {service.description && (
        <p className="na-svc-card__desc">{service.description}</p>
      )}

      <div className="na-svc-card__foot">
        {service.soonest && (
          <span className="na-svc-card__soonest">نزدیک‌ترین: {service.soonest}</span>
        )}
        <button type="button" className="na-btn na-svc-card__btn" onClick={() => onPick?.(service)}>
          دریافت نوبت
        </button>
      </div>
    </article>
  );
}
