import React, { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import api from '../lib/api';
import useAsync from '../lib/useAsync';
import { fa } from '../lib/format';
import { ErrorBox, Empty, LoadingBlock } from '../components/States.jsx';
import DoctorTile from '../components/ClinicDoctorTile.jsx';
import BookingSheet from '../components/BookingSheet.jsx';
import '../styles/clinic.css';

/** صفحه یک بخش (service): پزشکان همان بخش */
export default function ServicePage() {
  const { id } = useParams();
  const { data, error, loading, reload } = useAsync(() => api.serviceDoctors(id), [id]);
  const [booking, setBooking] = useState(null);

  if (loading) return <div className="cl-wrap cl-section"><LoadingBlock rows={3} height={150} /></div>;
  if (error) return <ErrorBox error={error} onRetry={reload} />;

  const service = data?.service ?? {};
  const doctors = data?.doctors ?? [];

  return (
    <>
      <section className="cl-booking">
        <div className="cl-wrap cl-booking__in">
          <nav className="cl-crumb"><Link to="/">صفحه اصلی</Link><span>/</span><span>بخش‌ها</span></nav>
          <div className="cl-service-head">
            {service.image && <img src={service.image} alt="" />}
            <div>
              <h1 className="cl-h2" style={{ fontSize: 'clamp(24px,5.5vw,38px)' }}>بخش {service.title}</h1>
              <p className="cl-sub" style={{ margin: 0 }}>
                {service.description || 'پزشکان این بخش و نزدیک‌ترین زمان‌های آزاد آنها'}
                {' · '}{fa(doctors.length)} پزشک
              </p>
            </div>
          </div>
        </div>
      </section>

      <section className="cl-wrap cl-section cl-section--last">
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

      {booking && (
        <BookingSheet doctorId={booking.doctorId} serviceId={booking.serviceId} onClose={() => setBooking(null)} />
      )}
    </>
  );
}
