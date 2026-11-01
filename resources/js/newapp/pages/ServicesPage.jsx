import React from 'react';
import { Link } from 'react-router-dom';
import api from '../lib/api';
import useAsync from '../lib/useAsync';
import { fa } from '../lib/format';
import { ErrorBox, Empty, LoadingBlock } from '../components/States.jsx';
import '../styles/clinic.css';

/** آدرس صفحه یک بخش، مانند لینک های بخش در صفحه اصلی */
export const serviceUrl = (s) => `/service/${s.id}/${encodeURIComponent(String(s.title ?? '').trim().replace(/\s+/g, '-'))}`;

/** صفحه «خدمات»: همه بخش هایی که پزشک قابل رزرو دارند */
export default function ServicesPage() {
  const { data, error, loading, reload } = useAsync(() => api.services(), []);

  if (loading) return <div className="cl-wrap cl-section"><LoadingBlock rows={3} height={150} /></div>;
  if (error) return <ErrorBox error={error} onRetry={reload} />;

  const services = data?.services ?? [];

  return (
    <>
      <section className="cl-booking">
        <div className="cl-wrap cl-booking__in">
          <nav className="cl-crumb"><Link to="/">صفحه اصلی</Link><span>/</span><span>خدمات</span></nav>
          <h1 className="cl-h2" style={{ fontSize: 'clamp(24px,5.5vw,38px)' }}>خدمات</h1>
          <p className="cl-sub" style={{ margin: 0 }}>
            بخش موردنظر را انتخاب کنید تا توضیحات، پزشکان و زمان‌های آزاد آن را ببینید.
          </p>
        </div>
      </section>

      <section className="cl-wrap cl-section cl-section--last">
        {services.length === 0
          ? <Empty title="خدمتی برای نمایش وجود ندارد" />
          : (
            <div className="cl-grid cl-grid--dep">
              {services.map((s) => (
                <article key={s.id} className="cl-dep">
                  <div className="cl-dep__head">
                    {s.image && <img src={s.image} alt="" />}
                    <strong>{s.title}</strong>
                    <span>{fa(s.doctors_count)} پزشک</span>
                  </div>
                  {s.description && (
                    <p style={{
                      display: '-webkit-box', WebkitLineClamp: 3, WebkitBoxOrient: 'vertical', overflow: 'hidden',
                    }}>{s.description}</p>
                  )}
                  <Link to={serviceUrl(s)} className="cl-btn-line">
                    مشاهده توضیحات و پزشکان
                  </Link>
                </article>
              ))}
            </div>
          )}
      </section>
    </>
  );
}
