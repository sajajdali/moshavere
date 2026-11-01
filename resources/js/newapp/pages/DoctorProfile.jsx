import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Link, useNavigate, useParams, useSearchParams } from 'react-router-dom';
import api from '../lib/api';
import useAsync from '../lib/useAsync';
import { useSite } from '../lib/SiteContext.jsx';
import { Loading, ErrorBox, Placeholder } from '../components/States.jsx';
import BookingSheet, { ConfirmDialog } from '../components/BookingSheet.jsx';
import LoginPanel from '../components/LoginPanel.jsx';
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

const LINK_BTN = {
  display: 'inline-block', marginBottom: 10, padding: 0, border: 0, background: 'transparent',
  color: 'var(--c-brand)', fontSize: 12.5, fontWeight: 700, cursor: 'pointer', fontFamily: 'inherit',
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
  // بخش و مطب: از لینک خدمت مشخص (/s/...) یا انتخاب بیمار در همین صفحه
  const [searchParams] = useSearchParams();
  const serviceId = searchParams.get('service_id') ?? '';
  const placeId = searchParams.get('place_id') ?? '';
  const { data, error, loading, reload } = useAsync(
    () => api.doctor(id, { service_id: serviceId, place_id: placeId }),
    [id, serviceId, placeId],
  );

  const [dayIdx, setDayIdx] = useState(0);
  const [picked, setPicked] = useState(null);
  const [submitting, setSubmitting] = useState(false);
  // پنجره تایید نوبت و فرم ورود، هر دو در همین صفحه (بدون رفتن به checkout لایوایر)
  const [confirming, setConfirming] = useState(false);
  const [bookError, setBookError] = useState(null);
  const [needLogin, setNeedLogin] = useState(null);
  // مودال رزرو (BookingSheet)؛ برای پزشک چندبخشی، انتخاب بخش در همین مودال انجام میشود
  const [sheetOpen, setSheetOpen] = useState(false);
  const timesRef = useRef(null);
  const nav = useNavigate();

  const { user, site, setUser } = useSite();
  const registrationIncomplete = user && (
    !user.first_name?.trim() || !user.last_name?.trim()
    || (site?.nationalCodeRequired && !user.national_code?.trim())
    || (site?.birthdayRequired && !user.birthday?.trim())
  );

  useEffect(() => { setDayIdx(0); setPicked(null); }, [id, serviceId, placeId]);

  // بدون لینک خدمت مشخص و با بیش از یک بخش، ابتدا مودال انتخاب بخش باز میشود
  useEffect(() => {
    if (data?.booking?.needs_service) setSheetOpen(true);
  }, [data]);

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
  const place = places.find((p) => p.id === booking.place_id) ?? places[0];
  const bookingServices = booking.services ?? [];
  const needsService = !!booking.needs_service;
  const service = booking.preselected
    ? (services.find((s) => s.id === booking.service_id)
      ?? bookingServices.filter((s) => s.id === booking.service_id).map((s) => ({ id: s.id, name: s.title }))[0])
    : null;

  const fee = service?.price ?? services.find((s) => s.price)?.price ?? null;

  const jumpToTimes = () => {
    timesRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  // «تایید و ادامه»: مهمان ابتدا وارد می شود، سپس پنجره تایید باز می شود
  const confirm = () => {
    if (!picked || submitting) return;
    setBookError(null);
    if (!user) setNeedLogin({});
    else if (registrationIncomplete) setNeedLogin({ registration: true });
    else setConfirming(true);
  };

  /** ثبت نهایی نوبت از طریق API، مانند BookingSheet */
  async function book() {
    if (!picked || submitting) return;
    setSubmitting(true);
    setBookError(null);

    try {
      const res = await api.bookAppointment(booking.doctor_id ?? doc.id, {
        visit_type: 'in_person',
        start_time: picked.start_time ?? picked.id,
        place_id: booking.place_id ?? null,
        service_id: booking.service_id ?? null,
        segment_ids: [],
      });
      nav(res?.redirect ?? `/appointment/${res?.tracking_code ?? ''}`);
    } catch (err) {
      setSubmitting(false);
      setBookError(err?.message ?? 'ثبت نوبت انجام نشد.');

      // ساعت در این فاصله پر شده: زمان ها دوباره خوانده می شوند
      if (err?.data?.refresh_times) {
        setConfirming(false);
        setPicked(null);
        reload();
      }

      if (err?.data?.need_registration) {
        setBookError(null);
        if (err.data.user) setUser(err.data.user);
        setConfirming(false);
        setNeedLogin({ registration: true, registrationUser: err.data.user });
      } else if (err?.data?.need_login || err?.status === 401) {
        setBookError(null);
        setConfirming(false);
        setNeedLogin({});
      }
    }
  }

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
                  {place?.title && (
                    <span style={{ display: 'block', fontSize: 12, color: 'var(--c-muted)', marginTop: 2 }}>
                      {place.title}
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

            {/* هنوز بخش انتخاب نشده: نزدیک ترین نوبت معنا ندارد */}
            {needsService && (
              <button type="button" className="na-btn" onClick={() => setSheetOpen(true)}
                style={{ width: '100%', marginTop: 16 }}>
                انتخاب بخش و دریافت نوبت
              </button>
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
                        background: s.id === service?.id ? 'var(--c-brand)' : 'var(--c-brand-soft)',
                        color: s.id === service?.id ? '#fff' : 'var(--c-brand)',
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
            <h2 style={{ margin: '0 0 4px', fontSize: 17, fontWeight: 800 }}>
              {needsService ? 'دریافت نوبت' : 'زمان‌های خالی'}
            </h2>
            <p style={{ margin: '0 0 14px', fontSize: 12, color: 'var(--c-muted)', lineHeight: 1.9 }}>
              {needsService
                ? 'این پزشک در چند بخش نوبت می‌دهد؛ ابتدا بخش موردنظر را انتخاب کنید.'
                : 'روز و سپس ساعت موردنظر خود را انتخاب کنید.'}
            </p>

            {needsService ? (
              <button type="button" className="na-btn" style={{ width: '100%' }}
                onClick={() => setSheetOpen(true)}>
                انتخاب بخش و دریافت نوبت
              </button>
            ) : (<>
            {service && bookingServices.length > 1 && (
              <button type="button" onClick={() => setSheetOpen(true)} style={LINK_BTN}>
                › تغییر بخش
              </button>
            )}

            {place && (
              <div style={{
                background: 'var(--c-bg)', borderRadius: 12, padding: 12, marginBottom: 12,
                fontSize: 12, lineHeight: 1.9, color: 'var(--c-muted)',
              }}>
                زمان‌های زیر مربوط به
                {service && <> بخش <strong style={{ color: 'var(--c-ink)' }}>{service.name}</strong> در</>}
                {' '}<strong style={{ color: 'var(--c-ink)' }}>{place.title}</strong> است.
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
                  {service && <Row label="بخش" value={service.name} />}
                  {place?.title && <Row label="محل مراجعه" value={place.title} />}
                  {fee && <Row label="هزینه ویزیت" value={`${money(fee)} ریال`} />}

                  <button type="button" className="na-btn"
                    disabled={!picked || submitting}
                    style={{ width: '100%', marginTop: 4 }}
                    onClick={confirm}>
                    {submitting && <span className="na-spinner"
                      style={{ borderTopColor: '#fff', borderColor: 'rgba(255,255,255,.4)' }}
                      aria-hidden="true" />}
                    {submitting ? 'در حال ثبت…' : picked ? 'تایید و ادامه' : 'یک زمان را انتخاب کنید'}
                  </button>
                </div>
              </>
            )}
            </>)}
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

      {/* همان فلو صفحه اصلی: بخش، مطب، نوع ویزیت، ناحیه، اپراتور، زمان و تایید */}
      {sheetOpen && (
        <BookingSheet doctorId={Number(id)} onClose={() => setSheetOpen(false)} />
      )}

      {confirming && picked && (
        <ConfirmDialog
          doctorName={doc.name}
          serviceName={service?.name
            ?? services.find((s) => s.id === booking.service_id)?.name
            ?? bookingServices.find((s) => s.id === booking.service_id)?.title}
          day={day}
          time={picked}
          error={bookError}
          submitting={submitting}
          onCancel={() => {
            if (submitting) return;
            setConfirming(false);
            setBookError(null);
          }}
          onConfirm={book}
        />
      )}

      {needLogin && (
        <LoginDialog onClose={() => setNeedLogin(null)}>
          <div style={{
            background: 'var(--c-brand-soft)', border: '1px solid var(--c-line)',
            borderRadius: 14, padding: '11px 13px', marginBottom: 16,
            fontSize: 12.5, lineHeight: 1.9, color: 'var(--c-ink-2)',
          }}>
            زمان انتخابی شما: <b>{day?.label ?? ''}</b>{' · ساعت '}<b>{picked?.label ?? ''}</b>
          </div>
          <LoginPanel
            registrationOnly={!!needLogin.registration}
            registrationUser={needLogin.registrationUser}
            hint="برای ثبت این نوبت، شماره موبایل خود را وارد کنید."
            onDone={() => {
              // پس از ورود، همان ساعت به مرحله تایید می رود
              setNeedLogin(null);
              setConfirming(true);
            }}
          />
        </LoginDialog>
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

/** پنجره ورود/تکمیل اطلاعات پیش از ثبت نوبت */
function LoginDialog({ onClose, children }) {
  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  return (
    <div role="dialog" aria-modal="true" aria-label="ورود برای ثبت نوبت" style={{
      position: 'fixed', inset: 0, zIndex: 90, background: 'rgba(9,28,42,0.5)',
      backdropFilter: 'blur(2px)', display: 'grid', placeItems: 'center', padding: 16,
    }}>
      <div onClick={onClose} style={{ position: 'absolute', inset: 0 }} />
      <div style={{
        position: 'relative', width: '100%', maxWidth: 420, maxHeight: '90vh', overflowY: 'auto',
        background: '#fff', borderRadius: 20, padding: 22,
        boxShadow: '0 24px 60px -20px rgba(9,28,42,.55)', animation: 'naUp .18s ease',
      }}>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 }}>
          <strong style={{ fontSize: 15 }}>ورود برای ثبت نوبت</strong>
          <button type="button" onClick={onClose} aria-label="بستن" style={{
            border: 0, background: 'transparent', fontSize: 22, lineHeight: 1,
            cursor: 'pointer', color: 'var(--c-muted)',
          }}>×</button>
        </div>
        {children}
      </div>
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
