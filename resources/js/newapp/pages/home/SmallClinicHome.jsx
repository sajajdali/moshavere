import React from 'react';
import { useSite } from '../../lib/SiteContext.jsx';
import ServiceCard from '../../components/ServiceCard.jsx';
import DoctorCard from '../../components/DoctorCard.jsx';
import { Placeholder, Empty } from '../../components/States.jsx';
import { fa } from '../../lib/format';

/** معادل طرح: Small Clinic Home.dc.html — تک پزشک به همراه چند پزشک */
export default function SmallClinicHome({ data, onBook }) {
  const { site, images } = useSite();
  const owner = data.doctor ?? {};
  const team = data.doctors ?? [];
  const services = data.services ?? [];
  const devices = data.devices ?? [];
  const hero = data.hero ?? {};

  return (
    <>
      {/* هدر کلینیک */}
      <section style={{ position: 'relative', overflow: 'hidden', background: 'var(--c-brand)' }}>
        {images?.hero && (
          <>
            <img src={images.hero} alt=""
              style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover' }} />
            <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(270deg, rgba(9,28,42,.72), rgba(9,28,42,.42))' }} />
          </>
        )}

        <div className="na-wrap" style={{ position: 'relative', padding: '48px 16px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 16 }}>
            {images?.clinic_logo && (
              <img src={images.clinic_logo} alt=""
                style={{ width: 52, height: 52, borderRadius: 15, objectFit: 'cover', flex: '0 0 auto' }} />
            )}
            <h1 style={{ color: '#fff', fontSize: 26, fontWeight: 800, margin: 0, lineHeight: 1.5 }}>
              {hero.title || site?.title}
            </h1>
          </div>

          {(hero.description || site?.subtitle) && (
            <p style={{ color: 'rgba(255,255,255,.9)', fontSize: 14, margin: '0 0 22px', lineHeight: 1.9, maxWidth: 620 }}>
              {hero.description || site.subtitle}
            </p>
          )}

          <button type="button" className="na-btn"
            style={{ background: '#fff', color: 'var(--c-brand)' }}
            onClick={() => onBook({ doctorId: owner.id })}>
            دریافت نوبت
          </button>
        </div>
      </section>

      {/* تیم درمان */}
      <section id="team" className="na-wrap" style={{ padding: '38px 16px' }}>
        <h2 className="na-sec-title">تیم درمان کلینیک</h2>
        <p className="na-sec-sub" style={{ marginBottom: 20 }}>
          پزشک یا کارشناس موردنظر را انتخاب کنید و زمان آزاد او را ببینید.
        </p>

        {images?.team && (
          <img src={images.team} alt="تیم درمان"
            style={{ width: '100%', aspectRatio: '16/7', objectFit: 'cover', borderRadius: 24, marginBottom: 20 }} />
        )}

        {/* پزشک مؤسس */}
        {owner.id && (
          <div className="na-card" style={{
            display: 'flex', gap: 16, alignItems: 'center', flexWrap: 'wrap',
            marginBottom: 16, borderColor: 'var(--c-brand)', background: 'var(--c-brand-soft)',
          }}>
            {(images?.doctor_avatar || owner.avatar)
              ? <img src={images?.doctor_avatar ?? owner.avatar} alt={owner.name ?? ''}
                  style={{ width: 82, height: 82, borderRadius: 22, objectFit: 'cover', flex: '0 0 auto' }} />
              : <Placeholder label="عکس" style={{ width: 82, height: 82, borderRadius: 22, flex: '0 0 auto' }} />}

            <div style={{ flex: '1 1 220px', minWidth: 0 }}>
              <span style={{
                display: 'inline-block', fontSize: 11, fontWeight: 700, marginBottom: 6,
                background: 'var(--c-brand)', color: '#fff', borderRadius: 999, padding: '4px 10px',
              }}>مؤسس کلینیک</span>
              <strong style={{ display: 'block', fontSize: 16, marginBottom: 4 }}>{owner.name}</strong>
              <span style={{ fontSize: 13, color: 'var(--c-muted)' }}>{owner.specialty}</span>
            </div>

            <button type="button" className="na-btn" onClick={() => onBook({ doctorId: owner.id })}>
              دریافت نوبت
            </button>
          </div>
        )}

        {/* سایر اعضای تیم */}
        {team.length === 0
          ? (!owner.id && <Empty title="عضوی ثبت نشده است" />)
          : (
            <div className="na-grid na-grid--2 na-grid--3">
              {team.map((d) => (
                <DoctorCard key={d.id} doctor={d} onBook={() => onBook({ doctorId: d.id })} />
              ))}
            </div>
          )}
      </section>

      {/* دستگاه های کلینیک */}
      {(devices.length > 0 || images?.device) && (
        <section id="devices" style={{ background: '#fff', borderTop: '1px solid var(--c-line)' }}>
          <div className="na-wrap" style={{ padding: '38px 16px' }}>
            <h2 className="na-sec-title" style={{ marginBottom: 20 }}>دستگاه‌های کلینیک</h2>

            {devices.length > 0 ? (
              <div className="na-grid na-grid--2 na-grid--3">
                {devices.map((d) => (
                  <div key={d.id} className="na-card">
                    {d.image
                      ? <img src={d.image} alt={d.title}
                          style={{ width: '100%', height: 110, objectFit: 'cover', borderRadius: 14, marginBottom: 12 }} />
                      : <Placeholder label="عکس" style={{ height: 110, borderRadius: 14, marginBottom: 12 }} />}
                    <strong style={{ display: 'block', fontSize: 14, marginBottom: 6 }}>{d.title}</strong>
                    {d.description && (
                      <p style={{ margin: 0, fontSize: 13, color: 'var(--c-muted)', lineHeight: 1.9 }}>{d.description}</p>
                    )}
                  </div>
                ))}
              </div>
            ) : (
              <div className="na-grid na-grid--2" style={{ alignItems: 'center' }}>
                <img src={images.device} alt="دستگاه ها"
                  style={{ width: '100%', aspectRatio: '3/2', objectFit: 'cover', borderRadius: 24 }} />
                <p style={{ margin: 0, fontSize: 14, color: 'var(--c-muted)', lineHeight: 2 }}>
                  {data.devices_description}
                </p>
              </div>
            )}
          </div>
        </section>
      )}

      {/* خدمات و تعرفه ها */}
      <section id="services" className="na-wrap" style={{ padding: '38px 16px' }}>
        <h2 className="na-sec-title" style={{ marginBottom: 20 }}>خدمات و تعرفه‌ها</h2>
        {services.length === 0
          ? <Empty title="خدمتی ثبت نشده است" />
          : (
            <div className="na-grid na-grid--2 na-grid--3">
              {services.map((s) => (
                <ServiceCard key={s.id} service={s}
                  onPick={() => onBook({ serviceId: s.id })} />
              ))}
            </div>
          )}
      </section>

      {/* درباره ما */}
      {(data.about || images?.about) && (
        <section id="about" style={{ background: '#fff', borderTop: '1px solid var(--c-line)' }}>
          <div className="na-wrap na-grid na-grid--2" style={{ padding: '38px 16px', alignItems: 'center' }}>
            {images?.about
              ? <img src={images.about} alt="درباره ما"
                  style={{ width: '100%', aspectRatio: '4/3', objectFit: 'cover', borderRadius: 24 }} />
              : <Placeholder label="عکس" style={{ aspectRatio: '4/3', borderRadius: 24 }} />}
            <div>
              <h2 className="na-sec-title" style={{ marginBottom: 12 }}>{data.about_title ?? 'درباره ما'}</h2>
              <p style={{ margin: 0, fontSize: 14, color: 'var(--c-muted)', lineHeight: 2 }}>{data.about}</p>
            </div>
          </div>
        </section>
      )}

      {/* نظرات مراجعان */}
      {(data.comments ?? []).length > 0 && (
        <section className="na-wrap" style={{ padding: '38px 16px' }}>
          <h2 className="na-sec-title" style={{ marginBottom: 20 }}>نظرات مراجعان</h2>
          <div className="na-grid na-grid--2 na-grid--3">
            {data.comments.map((c) => (
              <blockquote key={c.id} className="na-card" style={{ margin: 0 }}>
                <p style={{ margin: '0 0 12px', fontSize: 13, color: 'var(--c-ink-2)', lineHeight: 2 }}>{c.body}</p>
                <cite style={{ fontSize: 12, color: 'var(--c-muted)', fontStyle: 'normal', fontWeight: 700 }}>
                  {c.author}
                </cite>
              </blockquote>
            ))}
          </div>
        </section>
      )}
    </>
  );
}
