import React from 'react';
import { Link } from 'react-router-dom';
import { fa } from '../lib/format';

export default function DoctorCard({ doctor, onBook }) {
  return (
    <article className="na-card" style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
        {doctor.avatar
          ? <img src={doctor.avatar} alt={doctor.name}
              style={{ width: 62, height: 62, borderRadius: 16, objectFit: 'cover', flex: '0 0 auto' }} />
          : <span className="na-ph" style={{ width: 62, height: 62, borderRadius: 16, flex: '0 0 auto' }}>عکس</span>}

        <div style={{ minWidth: 0, flex: '1 1 auto' }}>
          <strong style={{ display: 'block', fontSize: 15, marginBottom: 3 }}>{doctor.name}</strong>
          <span style={{ fontSize: 12, color: 'var(--c-muted)' }}>{doctor.specialty}</span>
        </div>

        {doctor.rating && (
          <span style={{
            fontSize: 12, fontWeight: 700, color: 'var(--c-gold)',
            background: 'var(--c-gold-soft)', borderRadius: 999, padding: '5px 10px', flex: '0 0 auto',
          }}>★ {fa(doctor.rating)}</span>
        )}
      </div>

      {doctor.next && (
        <div style={{
          fontSize: 12, color: 'var(--c-accent)', background: 'var(--c-ok-soft)',
          borderRadius: 10, padding: '8px 11px', fontWeight: 700,
        }}>
          نزدیک‌ترین نوبت: {doctor.next}
        </div>
      )}

      <div style={{ display: 'flex', gap: 8, marginTop: 'auto' }}>
        <Link to={`/doctor/${doctor.id}`} className="na-btn na-btn--ghost" style={{ flex: 1, padding: '10px 12px' }}>
          پروفایل
        </Link>
        <button type="button" className="na-btn" style={{ flex: 1, padding: '10px 12px' }}
          onClick={() => onBook?.(doctor)}>
          دریافت نوبت
        </button>
      </div>
    </article>
  );
}
