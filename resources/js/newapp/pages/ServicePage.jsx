import React, { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import api from '../lib/api';
import useAsync from '../lib/useAsync';
import { fa } from '../lib/format';
import { ErrorBox, Empty, LoadingBlock } from '../components/States.jsx';
import DoctorTile from '../components/ClinicDoctorTile.jsx';
import BookingSheet from '../components/BookingSheet.jsx';
import '../styles/clinic.css';

/** صفحه یک بخش (service): پزشکان همان بخش در بالا، سپس توضیحات بخش */
export default function ServicePage() {
  const { id } = useParams();
  const { data, error, loading, reload } = useAsync(() => api.serviceDoctors(id), [id]);
  const [booking, setBooking] = useState(null);

  // عنوان و توضیح متای صفحه برای سئو
  useEffect(() => {
    const s = data?.service;
    if (!s?.title) return undefined;
    const prevTitle = document.title;
    let meta = document.querySelector('meta[name="description"]');
    const created = !meta;
    if (!meta) {
      meta = Object.assign(document.createElement('meta'), { name: 'description' });
      document.head.appendChild(meta);
    }
    const prevDesc = meta.getAttribute('content');
    document.title = `بخش ${s.title}` + (prevTitle ? ` | ${prevTitle}` : '');
    meta.setAttribute('content', s.description || `پزشکان بخش ${s.title} و زمان‌های آزاد نوبت‌دهی`);
    return () => {
      document.title = prevTitle;
      if (created) meta.remove();
      else meta.setAttribute('content', prevDesc ?? '');
    };
  }, [data]);

  if (loading) return <div className="cl-wrap cl-section"><LoadingBlock rows={3} height={150} /></div>;
  if (error) return <ErrorBox error={error} onRetry={reload} />;

  const service = data?.service ?? {};
  const doctors = data?.doctors ?? [];
  const hasAbout = Boolean(service.description_html || service.banner);

  return (
    <>
      <section className="cl-booking">
        <div className="cl-wrap cl-booking__in">
          <nav className="cl-crumb">
            <Link to="/">صفحه اصلی</Link><span>/</span><Link to="/services">خدمات</Link><span>/</span><span>{service.title}</span>
          </nav>
          <div className="cl-service-head">
            {service.image && <img src={service.image} alt="" />}
            <div>
              <h1 className="cl-h2" style={{ fontSize: 'clamp(24px,5.5vw,38px)' }}>بخش {service.title}</h1>
              <p className="cl-sub" style={{ margin: 0 }}>
                پزشکان این بخش و نزدیک‌ترین زمان‌های آزاد آنها · {fa(doctors.length)} پزشک
              </p>
            </div>
          </div>
        </div>
      </section>

      <section className={`cl-wrap cl-section${hasAbout ? '' : ' cl-section--last'}`}>
        <h2 className="cl-h2">پزشکان بخش</h2>
        {doctors.length === 0
          ? <Empty title="پزشکی برای این بخش یافت نشد" hint="بخش دیگری را امتحان کنید." />
          : (
            <div className="cl-grid">
              {doctors.map((d) => (
                <DoctorTile key={d.id} d={d} onBook={(b) => setBooking({ ...b, serviceId: Number(id) })} />
              ))}
            </div>
          )}
      </section>

      {hasAbout && (
        <section className="cl-wrap cl-section cl-section--last">
          <article className="cl-dep cl-about">
            {service.banner && (
              <img src={service.banner} alt={`بخش ${service.title}`} className="cl-about__banner" loading="lazy" />
            )}
            <h2 className="cl-h2" style={{ margin: 0 }}>درباره بخش {service.title}</h2>
            {service.description_html && (
              // HTML روی سرور با HTMLPurifier پاکسازی شده است (Service::sanitizeDescription)
              <div className="cl-richtext" dangerouslySetInnerHTML={{ __html: service.description_html }} />
            )}
          </article>
        </section>
      )}

      {booking && (
        <BookingSheet doctorId={booking.doctorId} serviceId={booking.serviceId} onClose={() => setBooking(null)} />
      )}
    </>
  );
}
