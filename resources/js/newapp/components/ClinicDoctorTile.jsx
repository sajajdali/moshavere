import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { fa } from '../lib/format';
import '../styles/clinic.css';

/** «امروز ۱۰:۰۰» / «فردا ۱۰:۰۰» / «چهارشنبه ۰۷/۲۲ · ۰۹:۰۰» */
export function nextLabel(next) {
  if (!next) return 'بدون زمان آزاد';
  const time = next.slots?.[0] ? ` · ${fa(next.slots[0])}` : '';
  const d = new Date(next.date + 'T00:00:00');
  const t = new Date(); t.setHours(0, 0, 0, 0);
  const diff = Math.round((d - t) / 86400000);
  if (diff === 0) return `امروز${time}`;
  if (diff === 1) return `فردا${time}`;
  return `${fa(next.label)}${time}`;
}

export default function DoctorTile({ d, featured, onBook }) {
  const [expanded, setExpanded] = useState(false);
  const slots = d.next?.slots ?? [];
  const size = featured ? 62 : 54;

  return (
    <article className={`cl-doc${featured ? ' cl-doc--featured' : ''}`}>
      <div className="cl-doc__head">
        {/* عکس و نام، پروفایل پزشک را باز می کنند */}
        <Link to={`/doctor/${d.id}`} aria-label={`پروفایل ${d.name}`} style={{ flex: '0 0 auto', lineHeight: 0 }}>
          {d.avatar
            ? <img src={d.avatar} alt={d.name} className="cl-doc__avatar" style={{ width: size, height: size }} />
            : <span className="cl-doc__avatar cl-doc__avatar--ph" style={{ width: size, height: size }}>عکس</span>}
        </Link>
        <span className="cl-doc__info">
          <Link to={`/doctor/${d.id}`} style={{ color: 'inherit', textDecoration: 'none' }}>
            <strong>{d.name}</strong>
          </Link>
          <span className="cl-doc__spec">
            {[d.specialty, d.department && `بخش ${d.department}`].filter(Boolean).join(' · ')}
          </span>
        </span>
        {d.rating && <span className="cl-doc__rate">★ {fa(d.rating)}</span>}
      </div>

      <div className={featured ? 'cl-doc__next cl-doc__next--box' : 'cl-doc__next'}>
        <span>{featured ? 'نزدیک‌ترین زمان آزاد' : 'نزدیک‌ترین زمان'}</span>
        <strong>{nextLabel(d.next)}</strong>
      </div>

      {expanded && (
        <div className="cl-doc__slots">
          {slots.length === 0 && <span className="cl-muted">زمان آزادی ثبت نشده است.</span>}
          {slots.map((t) => (
            <button key={t} type="button" onClick={() => onBook({ doctorId: d.id })}>{fa(t)}</button>
          ))}
        </div>
      )}

      <div className="cl-doc__btns">
        <button type="button" className="cl-btn-line" onClick={() => setExpanded((v) => !v)}>
          {expanded ? 'بستن ساعت‌ها' : 'مشاهده ساعت‌ها'}
        </button>
        <button type="button" className="cl-btn-fill" onClick={() => onBook({ doctorId: d.id })}>دریافت نوبت</button>
      </div>
    </article>
  );
}

