import React, { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import api from '../lib/api';
import useAsync from '../lib/useAsync';
import { LoadingBlock, ErrorBox, Placeholder } from '../components/States.jsx';
import BookingSheet from '../components/BookingSheet.jsx';
import { fa } from '../lib/format';

/** معادل طرح: Search Results.dc.html */
export default function Search() {
  const [params, setParams] = useSearchParams();
  const query = params.get('query') ?? '';
  const speciality = params.get('speciality') ?? '';
  const sort = params.get('sort') ?? 'soonest';
  const serviceId = params.get('service_id') ?? '';
  const province = params.get('province') ?? '';

  const [draft, setDraft] = useState(query);
  const [expanded, setExpanded] = useState(() => new Set());
  const [booking, setBooking] = useState(null);

  useEffect(() => { setDraft(query); }, [query]);

  const { data, error, loading, reload } = useAsync(
    () => api.search({
      query, speciality, sort,
      ...(serviceId ? { service_id: serviceId } : {}),
      ...(province ? { province } : {}),
    }),
    [query, speciality, sort, serviceId, province],
  );

  const setParam = (key, value) => {
    const next = new URLSearchParams(params);
    if (value) next.set(key, value); else next.delete(key);
    setParams(next, { replace: true });
  };

  const submit = (e) => {
    e.preventDefault();
    setParam('query', draft.trim());
  };

  const toggle = (id) => setExpanded((prev) => {
    const next = new Set(prev);
    next.has(id) ? next.delete(id) : next.add(id);
    return next;
  });

  const doctors = data?.doctors ?? [];
  const specOptions = data?.specialities ?? [];
  const provinceOptions = data?.provinces ?? [];
  const provinceLabel = provinceOptions.find((p) => String(p.id) === String(province))?.title;

  const activeTags = [
    query && { key: 'query', label: `جست و جو: ${query}` },
    speciality && { key: 'speciality', label: `تخصص: ${speciality}` },
    province && { key: 'province', label: `موقعیت: ${provinceLabel ?? province}` },
  ].filter(Boolean);

  return (
    <>
      {/* نوار جست و جو */}
      <section style={{ background: '#fff', borderBottom: '1px solid var(--c-line)' }}>
        <div className="na-wrap" style={{ padding: '24px 16px' }}>
          <nav aria-label="مسیر صفحه" style={{ fontSize: 12, color: 'var(--c-muted)', marginBottom: 10 }}>
            <Link to="/">صفحه اصلی</Link>
            <span style={{ margin: '0 6px' }}>›</span>
            <span>نتایج جستجو</span>
          </nav>

          <h1 style={{ fontSize: 22, fontWeight: 800, margin: '0 0 6px' }}>نتایج جستجوی پزشک</h1>
          <p style={{ fontSize: 13, color: 'var(--c-muted)', margin: '0 0 18px' }}>
            {loading ? 'در حال جست و جو…' : `${fa(doctors.length)} پزشک یافت شد`}
          </p>

          <form onSubmit={submit} style={{ display: 'flex', flexWrap: 'wrap', gap: 10 }}>
            <input
              type="search" value={draft} onChange={(e) => setDraft(e.target.value)}
              placeholder="نام پزشک، تخصص یا بخش…" aria-label="جستجوی پزشک"
              style={{
                flex: '2 1 240px', minWidth: 0, borderRadius: 12, padding: '12px 14px',
                fontSize: 14, border: '1px solid var(--c-line)', background: 'var(--c-bg)', outline: 0,
              }}
            />
            <select
              value={speciality} onChange={(e) => setParam('speciality', e.target.value)} aria-label="تخصص"
              style={{
                flex: '1 1 160px', borderRadius: 12, padding: '12px 14px', fontSize: 14,
                border: '1px solid var(--c-line)', background: 'var(--c-bg)', color: 'var(--c-ink)', outline: 0,
              }}
            >
              <option value="">همه تخصص‌ها</option>
              {specOptions.map((o) => (
                <option key={o.id ?? o.title} value={o.title}>{o.title}</option>
              ))}
            </select>
            {provinceOptions.length > 0 && (
              <select
                value={province} onChange={(e) => setParam('province', e.target.value)} aria-label="موقعیت"
                style={{
                  flex: '1 1 160px', borderRadius: 12, padding: '12px 14px', fontSize: 14,
                  border: '1px solid var(--c-line)', background: 'var(--c-bg)', color: 'var(--c-ink)', outline: 0,
                }}
              >
                <option value="">همه شهرها</option>
                {provinceOptions.map((p) => (
                  <option key={p.id} value={p.id}>{p.title}</option>
                ))}
              </select>
            )}
            <select
              value={sort} onChange={(e) => setParam('sort', e.target.value)} aria-label="ترتیب نمایش"
              style={{
                flex: '1 1 150px', borderRadius: 12, padding: '12px 14px', fontSize: 14,
                border: '1px solid var(--c-line)', background: 'var(--c-bg)', color: 'var(--c-ink)', outline: 0,
              }}
            >
              <option value="soonest">نزدیک‌ترین نوبت</option>
              <option value="rating">بیشترین امتیاز</option>
              <option value="name">نام پزشک</option>
            </select>
            <button type="submit" className="na-btn" style={{ flex: '0 0 auto' }}>جست و جو</button>
          </form>

          {activeTags.length > 0 && (
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, marginTop: 12 }}>
              {activeTags.map((t) => (
                <button key={t.key} type="button" className="na-chip" onClick={() => setParam(t.key, '')}>
                  <span>{t.label}</span>
                  <span aria-hidden="true">×</span>
                </button>
              ))}
            </div>
          )}
        </div>
      </section>

      <div className="na-wrap" style={{ padding: '26px 16px' }}>
        {/* متن «در حال جست و جو» بالای صفحه نمایش داده می شود، اینجا تکرار نمی کنیم */}
        {loading && <LoadingBlock rows={3} height={150} />}
        {error && <ErrorBox error={error} onRetry={reload} />}

        {!loading && !error && doctors.length === 0 && (
          <div className="na-card" style={{ textAlign: 'center', padding: '36px 18px' }}>
            <strong style={{ display: 'block', fontSize: 16, marginBottom: 8 }}>
              پزشکی با این جستجو پیدا نشد
            </strong>
            <p style={{ margin: '0 0 16px', fontSize: 13, color: 'var(--c-muted)', lineHeight: 1.9 }}>
              عبارت دیگری را امتحان کنید، فیلتر تخصص را بردارید، یا یکی از تخصص‌های زیر را انتخاب کنید.
            </p>
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, justifyContent: 'center' }}>
              {specOptions.slice(0, 6).map((o) => (
                <button key={o.id ?? o.title} type="button" className="na-chip"
                  onClick={() => setParam('speciality', o.title)}>
                  {o.title}
                </button>
              ))}
            </div>
          </div>
        )}

        {!loading && !error && doctors.length > 0 && (
          <div style={{ display: 'grid', gap: 14 }}>
            {doctors.map((d) => {
              const isOpen = expanded.has(d.id);
              return (
                <article key={d.id} className="na-card">
                  <div style={{ display: 'flex', alignItems: 'center', gap: 13, flexWrap: 'wrap' }}>
                    {d.avatar
                      ? <img src={d.avatar} alt={d.name}
                          style={{ width: 66, height: 66, borderRadius: 18, objectFit: 'cover', flex: '0 0 auto' }} />
                      : <Placeholder label="عکس" style={{ width: 66, height: 66, borderRadius: 18, flex: '0 0 auto' }} />}

                    <div style={{ flex: '1 1 190px', minWidth: 0 }}>
                      <strong style={{ display: 'block', fontSize: 15, marginBottom: 3 }}>{d.name}</strong>
                      <span style={{ display: 'block', fontSize: 12, color: 'var(--c-brand)', marginBottom: 2 }}>
                        {d.specialty}
                      </span>
                      {d.degree && (
                        <span style={{ fontSize: 12, color: 'var(--c-muted)' }}>{d.degree}</span>
                      )}
                    </div>

                    {d.rating && (
                      <span style={{
                        fontSize: 12, fontWeight: 700, color: 'var(--c-gold)',
                        background: 'var(--c-gold-soft)', borderRadius: 999, padding: '5px 10px',
                      }}>★ {fa(d.rating)}</span>
                    )}
                  </div>

                  <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, margin: '12px 0' }}>
                    {d.experience && (
                      <span style={{ fontSize: 12, color: 'var(--c-muted)' }}>{fa(d.experience)} سال سابقه</span>
                    )}
                    {d.place_count != null && (
                      <span style={{ fontSize: 12, color: 'var(--c-muted)' }}>· {fa(d.place_count)} محل ویزیت</span>
                    )}
                  </div>

                  {d.next && (
                    <div style={{
                      background: 'var(--c-ok-soft)', borderRadius: 14, padding: '11px 13px', marginBottom: 12,
                    }}>
                      <span style={{ display: 'block', fontSize: 11, color: 'var(--c-muted)', marginBottom: 3 }}>
                        نزدیک‌ترین زمان آزاد
                      </span>
                      <strong style={{ fontSize: 14, color: 'var(--c-accent)' }}>{d.next}</strong>
                      {d.where && (
                        <span style={{ display: 'block', fontSize: 12, color: 'var(--c-muted)', marginTop: 3 }}>
                          {d.where}
                        </span>
                      )}
                    </div>
                  )}

                  {isOpen && (
                    <div style={{ display: 'grid', gap: 12, marginBottom: 12 }}>
                      {(d.locations ?? []).map((l, i) => (
                        <div key={i} style={{
                          border: '1px solid var(--c-line)', borderRadius: 14, padding: 13,
                        }}>
                          <strong style={{ display: 'block', fontSize: 13, marginBottom: 4 }}>
                            {[l.dept, l.place].filter(Boolean).join(' — ')}
                          </strong>
                          {l.shift && (
                            <span style={{ display: 'block', fontSize: 12, color: 'var(--c-muted)', marginBottom: 9 }}>
                              {l.shift}
                            </span>
                          )}
                          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 7 }}>
                            {(l.times ?? []).map((t, k) => (
                              <span key={k} style={{
                                fontSize: 12, fontWeight: 700, borderRadius: 10, padding: '7px 11px',
                                background: 'var(--c-brand-soft)', color: 'var(--c-brand)',
                              }}>{t}</span>
                            ))}
                            {(l.times ?? []).length === 0 && (
                              <span style={{ fontSize: 12, color: 'var(--c-muted)' }}>
                                در روزهای پیش‌رو نوبت آزادی ندارد.
                              </span>
                            )}
                          </div>
                        </div>
                      ))}
                    </div>
                  )}

                  <div style={{ display: 'flex', gap: 9, flexWrap: 'wrap' }}>
                    {(d.locations ?? []).length > 0 && (
                      <button type="button" className="na-btn na-btn--ghost" onClick={() => toggle(d.id)}>
                        {isOpen ? 'بستن زمان‌ها' : 'مشاهده زمان‌ها'}
                      </button>
                    )}
                    <Link to={`/doctor/${d.id}`} className="na-btn na-btn--ghost">پروفایل</Link>
                    <button type="button" className="na-btn" style={{ marginInlineStart: 'auto' }}
                      onClick={() => setBooking({ doctorId: d.id })}>
                      دریافت نوبت
                    </button>
                  </div>
                </article>
              );
            })}
          </div>
        )}
      </div>

      {booking && (
        <BookingSheet doctorId={booking.doctorId} onClose={() => setBooking(null)} />
      )}
    </>
  );
}
