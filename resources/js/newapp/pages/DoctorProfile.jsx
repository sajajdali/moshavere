import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import api from '../lib/api';
import useAsync from '../lib/useAsync';
import { Loading, ErrorBox, Placeholder } from '../components/States.jsx';
import { fa, money } from '../lib/format';

/* ————— سبک ها، عیناً از طرح Doctor Profile.dc.html ————— */

const TAB_BASE = {
  flex: '0 0 auto', padding: '9px 15px', borderRadius: 999,
  fontSize: 13, fontWeight: 700, cursor: 'pointer',
};
const tabStyle = (on) => ({
  ...TAB_BASE,
  background: on ? 'var(--c-brand)' : '#fff',
  border: '1px solid ' + (on ? 'var(--c-brand)' : '#d9e3e1'),
  color: on ? '#fff' : 'var(--c-ink-2)',
});

const DAY_BASE = {
  flex: '0 0 auto', minWidth: 104, minHeight: 76, padding: '10px 14px',
  borderRadius: 16, cursor: 'pointer', textAlign: 'right',
};
const dayStyle = (on) => ({
  ...DAY_BASE,
  background: on ? 'var(--c-brand)' : 'var(--c-bg)',
  border: '1px solid ' + (on ? 'var(--c-brand)' : 'var(--c-line)'),
  color: on ? '#fff' : 'var(--c-ink-2)',
});

const SLOT_BASE = { minHeight: 48, borderRadius: 13, fontSize: 14, fontWeight: 700 };
const slotStyle = (state) => {
  if (state === 'dead') {
    return {
      ...SLOT_BASE, background: '#f0f3f3', border: '1px dashed #dbe3e8',
      color: '#a9b8c2', cursor: 'not-allowed', textDecoration: 'line-through',
    };
  }
  if (state === 'on') {
    return {
      ...SLOT_BASE, background: 'var(--c-accent-2)',
      border: '1px solid var(--c-accent-2)', color: '#fff', cursor: 'pointer',
    };
  }
  return {
    ...SLOT_BASE, background: 'var(--c-bg)',
    border: '1px solid var(--c-line-2)', color: 'var(--c-ink)', cursor: 'pointer',
  };
};

const CARD = {
  background: '#fff', border: '1px solid var(--c-line)',
  borderRadius: 22, padding: 18,
};

const SECTIONS = [
  { key: 'about', label: 'معرفی' },
  { key: 'times', label: 'زمان‌های خالی' },
  { key: 'gallery', label: 'گالری' },
  { key: 'reviews', label: 'نظر بیماران' },
  { key: 'related', label: 'پزشکان مشابه' },
];

/** معادل طرح: Doctor Profile.dc.html */
export default function DoctorProfile() {
  const { id } = useParams();
  const { data, error, loading, reload } = useAsync(() => api.doctor(id), [id]);

  const [dayIdx, setDayIdx] = useState(0);
  const [picked, setPicked] = useState(null);
  const [submitting, setSubmitting] = useState(false);
  const timesRef = useRef(null);

  useEffect(() => { setDayIdx(0); setPicked(null); }, [id]);

  const booking = data?.booking ?? {};
  const days = booking.days ?? [];
  const day = days[dayIdx];

  const nearest = useMemo(() => {
    for (const d of days) {
      const t = (d.times ?? []).find((x) => x.free);
      if (t) return { day: d, time: t };
    }
    return null;
  }, [days]);

  if (loading) return <Loading label="در حال دریافت اطلاعات پزشک" />;
  if (error) return <ErrorBox error={error} onRetry={reload} />;

  const doc = data?.doctor ?? {};
  const services = data?.services ?? [];
  const places = data?.places ?? [];
  const reviews = data?.reviews ?? [];
  const related = data?.related ?? [];
  const fee = services.find((s) => s.price)?.price ?? null;

  const jumpToTimes = () => {
    timesRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  const confirm = () => {
    if (!picked || submitting) return;
    const q = new URLSearchParams({
      doctor_id: String(booking.doctor_id ?? doc.id),
      place_id: String(booking.place_id ?? ''),
      service_id: String(booking.service_id ?? ''),
      start_time: String(picked.start_time ?? picked.id),
      end_time: String(picked.end_time ?? picked.until ?? ''),
    });
    setSubmitting(true);
    window.location.href = '/appointment/checkout?' + q.toString();
  };

  return (
    <div style={{ paddingBottom: 84 }}>
      {/* مسیر صفحه */}
      <nav aria-label="مسیر صفحه" style={{
        maxWidth: 1180, margin: '0 auto', padding: '14px 16px 0',
        fontSize: 12, color: 'var(--c-muted)',
      }}>
        <Link to="/">صفحه اصلی</Link>
        <span style={{ margin: '0 6px' }}>›</span>
        <Link to="/search?query=پزشکان">پزشکان</Link>
        <span style={{ margin: '0 6px' }}>›</span>
        <span style={{ color: 'var(--c-ink-2)', fontWeight: 600 }}>{doc.name}</span>
      </nav>

      {/* میانبر بخش ها */}
      <section style={{ maxWidth: 1180, margin: '0 auto', padding: '14px 16px 0' }}>
        <div style={{ display: 'flex', gap: 8, overflowX: 'auto', padding: '4px 0 12px' }}>
          {SECTIONS.map((s) => (
            <button key={s.key} type="button" style={tabStyle(false)}
              onClick={() => document.getElementById('sec-' + s.key)
                ?.scrollIntoView({ behavior: 'smooth', block: 'start' })}>
              {s.label}
            </button>
          ))}
        </div>
      </section>

      {/* دو ستون: محتوا + پنل نوبت */}
      <section className="dp-grid" style={{
        maxWidth: 1180, margin: '0 auto', padding: '0 16px 28px',
        display: 'grid', gap: 16,
      }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 14, minWidth: 0 }}>
          {/* کارت معرفی */}
          <article id="sec-about" style={{ ...CARD, scrollMarginTop: 84 }}>
            <div style={{ display: 'flex', gap: 15, flexWrap: 'wrap' }}>
              {doc.avatar
                ? <img src={doc.avatar} alt={doc.name}
                    style={{ width: 104, height: 104, borderRadius: 24, objectFit: 'cover', flex: '0 0 auto' }} />
                : <Placeholder label="عکس پزشک"
                    style={{ width: 104, height: 104, borderRadius: 24, flex: '0 0 auto' }} />}

              <div style={{ flex: '1 1 220px', minWidth: 0 }}>
                <h1 style={{ margin: '0 0 6px', fontSize: 22, fontWeight: 800 }}>{doc.name}</h1>
                {doc.specialty && (
                  <p style={{ margin: '0 0 6px', fontSize: 14, color: 'var(--c-brand)', fontWeight: 600 }}>
                    {doc.specialty}
                  </p>
                )}
                {doc.degree && (
                  <p style={{ margin: '0 0 10px', fontSize: 13, color: 'var(--c-muted)' }}>{doc.degree}</p>
                )}

                <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, fontSize: 12 }}>
                  {doc.rating && (
                    <span style={{
                      fontWeight: 700, color: 'var(--c-gold)', background: 'var(--c-gold-soft)',
                      borderRadius: 999, padding: '5px 11px',
                    }}>
                      ★ {fa(doc.rating)}{reviews.length ? ` · ${fa(reviews.length)} نظر` : ''}
                    </span>
                  )}
                  {doc.experience && (
                    <span style={{ background: 'var(--c-bg)', borderRadius: 999, padding: '5px 11px', color: 'var(--c-ink-2)' }}>
                      {fa(doc.experience)} سال سابقه
                    </span>
                  )}
                  {doc.licence_number && (
                    <span style={{ background: 'var(--c-bg)', borderRadius: 999, padding: '5px 11px', color: 'var(--c-ink-2)' }}>
                      نظام پزشکی {fa(doc.licence_number)}
                    </span>
                  )}
                </div>
              </div>
            </div>

            {/* نوار نزدیک ترین نوبت */}
            {nearest && (
              <div style={{
                marginTop: 16, padding: '13px 15px', borderRadius: 16,
                background: 'var(--c-ok-soft)', display: 'flex',
                alignItems: 'center', gap: 12, flexWrap: 'wrap',
              }}>
                <span style={{ flex: '1 1 auto', minWidth: 0 }}>
                  <span style={{ display: 'block', fontSize: 11, color: 'var(--c-muted)' }}>
                    نزدیک‌ترین زمان آزاد
                  </span>
                  <strong style={{ fontSize: 15, color: 'var(--c-accent)' }}>
                    {nearest.day.label} — {nearest.time.label}
                  </strong>
                  {places[0]?.title && (
                    <span style={{ display: 'block', fontSize: 12, color: 'var(--c-muted)', marginTop: 2 }}>
                      {places[0].title}
                    </span>
                  )}
                </span>
                <button type="button" className="na-btn" style={{ flex: '0 0 auto' }}
                  onClick={() => {
                    const i = days.indexOf(nearest.day);
                    if (i >= 0) setDayIdx(i);
                    setPicked(nearest.time);
                    jumpToTimes();
                  }}>
                  دریافت این نوبت
                </button>
              </div>
            )}
          </article>

          {/* بیوگرافی */}
          {(doc.biography || (doc.education ?? []).length > 0 || services.length > 0) && (
            <article style={CARD}>
              {doc.biography && (
                <>
                  <h2 style={{ margin: '0 0 10px', fontSize: 17, fontWeight: 800 }}>بیوگرافی</h2>
                  <p style={{ margin: '0 0 14px', fontSize: 14, color: 'var(--c-ink-2)', lineHeight: 2.1 }}>
                    {doc.biography}
                  </p>
                </>
              )}

              {(doc.education ?? []).length > 0 && (
                <>
                  <h3 style={{ margin: '0 0 10px', fontSize: 15, fontWeight: 700 }}>تحصیلات و سابقه</h3>
                  <ul style={{ margin: '0 0 14px', padding: 0, listStyle: 'none', display: 'grid', gap: 9 }}>
                    {doc.education.map((e, i) => (
                      <li key={i} style={{ display: 'flex', gap: 9, alignItems: 'flex-start' }}>
                        <span style={{
                          width: 7, height: 7, borderRadius: '50%', marginTop: 7,
                          background: 'var(--c-brand)', flex: '0 0 auto',
                        }} />
                        <span style={{ fontSize: 13, color: 'var(--c-muted)', lineHeight: 1.9 }}>{e}</span>
                      </li>
                    ))}
                  </ul>
                </>
              )}

              {services.length > 0 && (
                <>
                  <h3 style={{ margin: '0 0 10px', fontSize: 15, fontWeight: 700 }}>خدمات و حوزه درمان</h3>
                  <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8 }}>
                    {services.map((s) => (
                      <span key={s.id} style={{
                        fontSize: 12, fontWeight: 600, borderRadius: 999, padding: '7px 13px',
                        background: 'var(--c-brand-soft)', color: 'var(--c-brand)',
                      }}>{s.name}</span>
                    ))}
                  </div>
                </>
              )}
            </article>
          )}

          {/* گالری */}
          {(doc.gallery ?? []).length > 0 && (
            <article id="sec-gallery" style={{ ...CARD, scrollMarginTop: 84 }}>
              <h2 style={{ margin: '0 0 4px', fontSize: 17, fontWeight: 800 }}>گالری مطب و تصاویر</h2>
              <p style={{ margin: '0 0 14px', fontSize: 13, color: 'var(--c-muted)' }}>
                تصاویر اتاق ویزیت، تجهیزات و محیط مطب.
              </p>
              <div style={{
                display: 'grid', gap: 10,
                gridTemplateColumns: 'repeat(auto-fill, minmax(140px, 1fr))',
              }}>
                {doc.gallery.map((g) => (
                  <img key={g.id} src={g.file_address} alt="" loading="lazy"
                    style={{ width: '100%', aspectRatio: '4/3', objectFit: 'cover', borderRadius: 14 }} />
                ))}
              </div>
            </article>
          )}

          {/* نظر بیماران */}
          {reviews.length > 0 && (
            <article id="sec-reviews" style={{ ...CARD, scrollMarginTop: 84 }}>
              <h2 style={{ margin: '0 0 14px', fontSize: 17, fontWeight: 800 }}>نظر بیماران</h2>
              <div style={{ display: 'grid', gap: 10 }}>
                {reviews.map((r) => (
                  <blockquote key={r.id} style={{
                    margin: 0, background: 'var(--c-bg)', borderRadius: 16, padding: 14,
                  }}>
                    <p style={{ margin: '0 0 10px', fontSize: 13, color: 'var(--c-ink-2)', lineHeight: 2 }}>
                      {r.body}
                    </p>
                    <footer style={{
                      display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                      gap: 8, fontSize: 12, color: 'var(--c-muted)',
                    }}>
                      <span>{r.author}</span>
                      {r.star && <span style={{ fontWeight: 700, color: 'var(--c-gold)' }}>★ {fa(r.star)}</span>}
                    </footer>
                  </blockquote>
                ))}
              </div>
            </article>
          )}
        </div>

        {/* ستون کناری: انتخاب زمان */}
        <aside id="sec-times" ref={timesRef} className="dp-aside"
          style={{ display: 'flex', flexDirection: 'column', gap: 14, scrollMarginTop: 84, minWidth: 0 }}>
          <div style={{ ...CARD, borderRadius: 22 }}>
            <h2 style={{ margin: '0 0 4px', fontSize: 17, fontWeight: 800 }}>زمان‌های خالی</h2>
            <p style={{ margin: '0 0 14px', fontSize: 12, color: 'var(--c-muted)', lineHeight: 1.9 }}>
              روز و سپس ساعت موردنظر خود را انتخاب کنید.
            </p>

            {places[0] && (
              <div style={{
                background: 'var(--c-bg)', borderRadius: 12, padding: 12, marginBottom: 12,
                fontSize: 12, lineHeight: 1.9, color: 'var(--c-muted)',
              }}>
                زمان‌های زیر مربوط به <strong style={{ color: 'var(--c-ink)' }}>{places[0].title}</strong> است.
              </div>
            )}

            {days.length === 0 ? (
              <p style={{ margin: 0, fontSize: 13, color: 'var(--c-muted)', lineHeight: 1.9 }}>
                در حال حاضر زمان خالی برای این پزشک ثبت نشده است.
              </p>
            ) : (
              <>
                {/* روزها */}
                <div style={{ display: 'flex', gap: 8, overflowX: 'auto', paddingBottom: 12 }}>
                  {days.map((d, i) => (
                    <button key={d.key ?? i} type="button" style={dayStyle(i === dayIdx)}
                      onClick={() => { setDayIdx(i); setPicked(null); }}>
                      <span style={{ display: 'block', fontSize: 12, opacity: .75 }}>
                        {String(d.label).split(' ')[0]}
                      </span>
                      <strong style={{ display: 'block', fontSize: 14, marginTop: 3 }}>
                        {String(d.label).split(' ').slice(1).join(' ') || d.label}
                      </strong>
                      {d.free_count != null && (
                        <span style={{ display: 'block', fontSize: 11, marginTop: 4, opacity: .8 }}>
                          {fa(d.free_count)} نوبت
                        </span>
                      )}
                    </button>
                  ))}
                </div>

                {/* ساعت ها */}
                <div style={{
                  display: 'grid', gap: 8,
                  gridTemplateColumns: 'repeat(auto-fit, minmax(92px, 1fr))',
                }}>
                  {(day?.times ?? []).map((t) => {
                    const state = !t.free ? 'dead' : (picked?.id === t.id ? 'on' : 'off');
                    return (
                      <button key={t.id} type="button" disabled={!t.free}
                        style={slotStyle(state)}
                        onClick={() => t.free && setPicked(t)}>
                        {t.label}
                      </button>
                    );
                  })}
                </div>

                {(day?.times ?? []).length === 0 && (
                  <p style={{ margin: '10px 0 0', fontSize: 12, color: 'var(--c-muted)', lineHeight: 1.9 }}>
                    در این روز نوبت آزادی نیست؛ روز دیگری را انتخاب کنید.
                  </p>
                )}

                {/* جمع بندی و تایید */}
                <div style={{
                  marginTop: 14, paddingTop: 14, borderTop: '1px solid var(--c-line)',
                  display: 'grid', gap: 9,
                }}>
                  <Row label="نوبت انتخابی"
                    value={picked ? `${day?.label} — ${picked.label}` : 'انتخاب نشده'} />
                  {places[0]?.title && <Row label="محل مراجعه" value={places[0].title} />}
                  {fee && <Row label="هزینه ویزیت" value={`${money(fee)} ریال`} />}

                  <button type="button" className="na-btn"
                    disabled={!picked || submitting}
                    style={{ width: '100%', marginTop: 4 }}
                    onClick={confirm}>
                    {submitting && <span className="na-spinner"
                      style={{ borderTopColor: '#fff', borderColor: 'rgba(255,255,255,.4)' }}
                      aria-hidden="true" />}
                    {submitting ? 'در حال انتقال…' : picked ? 'تایید و ادامه' : 'یک زمان را انتخاب کنید'}
                  </button>
                </div>
              </>
            )}
          </div>

          {/* اطلاعات ویزیت */}
          {places.length > 0 && (
            <div style={CARD}>
              <h2 style={{ margin: '0 0 12px', fontSize: 17, fontWeight: 800 }}>اطلاعات ویزیت</h2>
              <div style={{ display: 'grid', gap: 9 }}>
                {places.map((p) => (
                  <React.Fragment key={p.id}>
                    <Row label="محل" value={p.title} />
                    {p.address && <Row label="آدرس" value={p.address} />}
                    {p.phone && <Row label="تلفن" value={fa(p.phone)} />}
                  </React.Fragment>
                ))}
              </div>
            </div>
          )}
        </aside>
      </section>

      {/* پزشکان مشابه */}
      {related.length > 0 && (
        <section id="sec-related" style={{
          maxWidth: 1180, margin: '0 auto', padding: '0 16px 32px', scrollMarginTop: 84,
        }}>
          <h2 style={{ margin: '0 0 4px', fontSize: 18, fontWeight: 800 }}>پزشکان مشابه</h2>
          <p style={{ margin: '0 0 16px', fontSize: 13, color: 'var(--c-muted)' }}>
            اگر زمان مناسبی پیدا نکردید، این پزشکان هم زمان آزاد دارند.
          </p>
          <div className="na-grid na-grid--2 na-grid--3">
            {related.map((r) => (
              <article key={r.id} style={CARD}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 11, marginBottom: 12 }}>
                  {r.avatar
                    ? <img src={r.avatar} alt={r.name}
                        style={{ width: 52, height: 52, borderRadius: 15, objectFit: 'cover', flex: '0 0 auto' }} />
                    : <Placeholder label="عکس" style={{ width: 52, height: 52, borderRadius: 15, flex: '0 0 auto' }} />}
                  <span style={{ flex: '1 1 auto', minWidth: 0 }}>
                    <strong style={{ display: 'block', fontSize: 14 }}>{r.name}</strong>
                    <span style={{ fontSize: 12, color: 'var(--c-muted)' }}>{r.specialty}</span>
                  </span>
                  {r.rating && (
                    <span style={{ fontSize: 12, fontWeight: 700, color: 'var(--c-gold)' }}>★ {fa(r.rating)}</span>
                  )}
                </div>
                <Link to={`/doctor/${r.id}`} className="na-btn na-btn--ghost"
                  style={{ width: '100%' }}>
                  مشاهده پروفایل و نوبت‌ها
                </Link>
              </article>
            ))}
          </div>
        </section>
      )}

      {/* نوار ثابت پایین صفحه - فقط موبایل */}
      {nearest && (
        <div className="dp-sticky" style={{
          position: 'fixed', insetInline: 0, bottom: 0, zIndex: 50,
          background: '#fff', borderTop: '1px solid var(--c-line)',
          padding: '10px 16px', display: 'flex', alignItems: 'center', gap: 12,
          boxShadow: '0 -6px 20px rgba(9,28,42,.08)',
        }}>
          <span style={{ flex: '1 1 auto', minWidth: 0 }}>
            <span style={{ display: 'block', fontSize: 11, color: 'var(--c-muted)' }}>
              نزدیک‌ترین زمان آزاد
            </span>
            <strong style={{ fontSize: 14, color: 'var(--c-accent)' }}>
              {nearest.day.label} — {nearest.time.label}
            </strong>
          </span>
          <button type="button" className="na-btn" style={{ flex: '0 0 auto' }}
            onClick={() => {
              const i = days.indexOf(nearest.day);
              if (i >= 0) setDayIdx(i);
              setPicked(nearest.time);
              jumpToTimes();
            }}>
            دریافت نوبت
          </button>
        </div>
      )}
    </div>
  );
}

/** یک ردیف «عنوان — مقدار» در کارت های کناری */
function Row({ label, value }) {
  return (
    <div style={{
      display: 'flex', alignItems: 'baseline', justifyContent: 'space-between',
      gap: 10, fontSize: 12,
    }}>
      <span style={{ color: 'var(--c-muted)', flex: '0 0 auto' }}>{label}</span>
      <strong style={{ color: 'var(--c-ink)', textAlign: 'left', minWidth: 0 }}>{value}</strong>
    </div>
  );
}
