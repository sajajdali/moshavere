import React from 'react';
import { useSite } from '../../lib/SiteContext.jsx';
import ServiceCard from '../../components/ServiceCard.jsx';
import OpenStreetMap from '../../components/OpenStreetMap.jsx';
import { Header, Footer } from '../../components/Layout.jsx';
import { Placeholder, Empty } from '../../components/States.jsx';
import { fa, toEnDigits } from '../../lib/format';
import { groupWorkingHours, placeMap } from '../../lib/workingHours';
import '../../styles/single-doctor.css';

function ContactPlace({ place, hours, email }) {
  const map = placeMap(place);
  const phones = (Array.isArray(place.phone) ? place.phone : [place.phone]).filter(Boolean);
  const groupedHours = groupWorkingHours(place.working_hours ?? hours);

  return (
    <div className="sd-contact-grid">
      <div className="na-contact-card">
        <div>
          <strong className="sd-card-title">{place.title || 'اطلاعات مطب'}</strong>
          {place.address && <p className="sd-address">{place.address}</p>}
        </div>
        {(phones.length > 0 || email) && (
          <div className="sd-contact-links">
            {phones.map((phone, i) => (
              <a key={i} href={'tel:' + toEnDigits(phone).replace(/[^+\d]/g, '')}><bdi>{fa(phone)}</bdi></a>
            ))}
            {email && <a href={'mailto:' + email}><bdi>{email}</bdi></a>}
          </div>
        )}
        {groupedHours.length > 0 && (
          <div className="na-contact-card__hours" role="group" aria-label="ساعات کاری هفتگی">
            {groupedHours.map((hour, i) => (
              <div key={i} className="na-contact-card__hours-row">
                <span>{hour.day_name}</span>
                <strong className={hour.ranges.length ? 'sd-hours-open' : 'sd-hours-closed'}>
                  {hour.ranges.length
                    ? hour.ranges.map((range, j) => <span key={j}>{fa(range.start)} تا {fa(range.end)}</span>)
                    : 'تعطیل'}
                </strong>
              </div>
            ))}
          </div>
        )}
        {map.directions && (
          <a href={map.directions} target="_blank" rel="noreferrer" className="na-btn na-btn--ghost sd-directions">
            مسیریابی روی نقشه
          </a>
        )}
      </div>
      {map.coordinates
        ? <OpenStreetMap className="sd-map" title={'نقشه موقعیت ' + (place.title || 'مطب')} coordinates={map.coordinates} />
        : <Placeholder className="sd-map" label="نقشه موقعیت مطب" />}
    </div>
  );
}

/** صفحه خانهٔ تک‌پزشک، مطابق Single Doctor Home.dc.html و با داده‌های واقعی سایت. */
export default function SingleDoctorHome({ data, onBook }) {
  const { images } = useSite();
  const doctor = data.doctor ?? {};
  const services = data.services ?? [];
  const stats = data.stats ?? {};
  const hero = data.hero ?? {};
  const comments = data.comments ?? [];
  const hours = data.working_hours ?? [];
  const faqs = data.faqs ?? [];
  const places = data.places?.length ? data.places : (
    hours.length || doctor.address || hero.phone || data.contact_email
      ? [{ id: 'office', title: doctor.name ? 'مطب ' + doctor.name : 'مطب', address: doctor.address, phone: hero.phone }]
      : []
  );
  const timeline = data.timeline ?? (doctor.education ?? []).map((text) => ({ text }));
  const credentials = timeline.length ? timeline : [
    doctor.specialty && { year: 'تخصص', text: doctor.specialty },
    doctor.licence_number && { year: 'نظام پزشکی', text: fa(doctor.licence_number) },
    stats.experience && { year: 'سابقه', text: fa(stats.experience) + ' سال تجربه' },
  ].filter(Boolean);
  const hasAbout = Boolean(data.about || images?.about || credentials.length);
  const ratedComments = comments.filter((comment) => Number(comment.star) > 0);
  const averageRating = ratedComments.length
    ? fa((ratedComments.reduce((total, comment) => total + Number(comment.star), 0) / ratedComments.length)
      .toFixed(1).replace('.', '٫')) : null;
  const book = () => onBook({ doctorId: doctor.id });
  const sections = [
    { id: 'services', label: 'خدمات' },
    { id: 'booking', label: 'نوبت‌دهی', booking: true },
    ...(hasAbout ? [{ id: 'about', label: 'درباره دکتر' }] : []),
    ...(comments.length ? [{ id: 'reviews', label: 'نظرات' }] : []),
    ...(places.length ? [{ id: 'contact', label: 'تماس و آدرس' }] : []),
  ];

  return (
    <div className="sd-home">
      <Header doctor={doctor} sections={sections} onBook={book} />
      <main id="top">
        <section className="na-hero">
          <div className="na-wrap na-hero__grid">
            <div>
              {hero.badge && <span className="na-hero__badge"><span className="na-hero__dot" />{hero.badge}</span>}
              <h1 className="na-hero__name">{hero.title || doctor.name}</h1>
              {(hero.subtitle || doctor.specialty) && <p className="na-hero__specialty">{hero.subtitle || doctor.specialty}</p>}
              {hero.description && <p className="na-hero__bio">{hero.description}</p>}
              <div className="sd-credentials">
                {doctor.licence_number && <span>نظام پزشکی {fa(doctor.licence_number)}</span>}
                {stats.experience && <span>{fa(stats.experience)} سال تجربه</span>}
              </div>
              <div className="na-hero__actions">
                <button type="button" className="na-btn" onClick={book}>{hero.primary_button || 'دریافت نوبت آنلاین'}</button>
                {hero.phone && <a href={'tel:' + toEnDigits(hero.phone).replace(/[^+\d]/g, '')} className="na-btn na-btn--ghost">
                  {hero.secondary_button || 'تماس با مطب'}
                </a>}
              </div>
            </div>
            <div className="na-hero__figure">
              {(images?.doctor_avatar || doctor.avatar)
                ? <img src={images?.doctor_avatar || doctor.avatar} alt={doctor.name || ''} className="na-hero__photo" />
                : <Placeholder label="عکس پرتره دکتر" className="na-hero__photo" />}
              <dl className="na-hero__stats">
                {[
                  ['سابقه', stats.experience ? fa(stats.experience) + ' سال' : '—'],
                  ['بیمار', stats.patients ? fa(stats.patients) : '—'],
                ].map(([label, value]) => (
                  <div key={label} className="na-hero__stat"><dt>{label}</dt><dd>{value}</dd></div>
                ))}
              </dl>
            </div>
          </div>
        </section>

        <section id="services" className="na-wrap sd-section">
          <h2 className="na-sec-title">خدمات مطب</h2>
          <p className="na-sec-sub">خدمت موردنظر را انتخاب کنید تا زمان‌های آزاد همان خدمت را ببینید.</p>
          {services.length === 0 ? <Empty title="خدمتی ثبت نشده است" /> : (
            <div className="na-grid na-grid--services">
              {services.map((service) => <ServiceCard key={service.id} service={service}
                onPick={() => onBook({ doctorId: service.doctor_id || doctor.id, serviceId: service.id })} />)}
            </div>
          )}
        </section>

        {hasAbout && (
          <section id="about" className="na-wrap sd-section">
            <div className="sd-about-grid">
              {(data.about || images?.about) && <div className="sd-panel">
                <h2 className="na-sec-title">{data.about_title || 'درباره پزشک'}</h2>
                {data.about ? <p className="sd-about-text">{data.about}</p>
                  : <img src={images.about} alt="درباره پزشک" className="sd-about-photo" loading="lazy" />}
              </div>}
              {credentials.length > 0 && <div className="sd-panel">
                <h3 className="sd-card-title">تحصیلات و سوابق</h3>
                <ol className="sd-timeline">
                  {credentials.map((item, i) => <li key={i}>
                    {item.year && <span className="sd-timeline__year">{fa(item.year)}</span>}
                    <span>{item.text}</span>
                  </li>)}
                </ol>
              </div>}
            </div>
          </section>
        )}

        {comments.length > 0 && (
          <section id="reviews" className="na-wrap sd-section">
            <div className="sd-section-head">
              <h2 className="na-sec-title">نظرات بیماران</h2>
              <span>{averageRating && 'میانگین ' + averageRating + ' از ۵ · '}{fa(comments.length)} نظر نمایش‌داده‌شده</span>
            </div>
            <p className="na-sec-sub">تجربه مراجعه و نظرات ثبت‌شده بیماران.</p>
            <div className="sd-reviews-grid">
              {comments.map((comment) => <article key={comment.id} className="na-review">
                <div className="na-review__head">
                  <span className="na-review__avatar">{(comment.author ?? '').trim().slice(0, 1)}</span>
                  <span className="sd-review-author">
                    <strong className="na-review__name">{comment.author}</strong>
                    {comment.date && <span className="na-review__date">{fa(comment.date)}</span>}
                  </span>
                  {Number(comment.star) > 0 && <span className="na-review__rating">★ {fa(comment.star).replace('.', '٫')}</span>}
                </div>
                <p className="na-review__body">{comment.body}</p>
              </article>)}
            </div>
          </section>
        )}

        {places.length > 0 && (
          <section id="contact" className="na-wrap sd-section">
            <h2 className="na-sec-title">آدرس و ساعات کاری</h2>
            <div className="sd-places">
              {places.map((place) => <ContactPlace key={place.id} place={place} hours={hours} email={data.contact_email} />)}
            </div>
          </section>
        )}

        {faqs.length > 0 && <section id="faq" className="na-wrap sd-section">
          <h2 className="na-sec-title">پرسش‌های پرتکرار</h2>
          <div className="sd-faqs">
            {faqs.map((faq) => <details key={faq.id} className="sd-faq">
              <summary>{faq.question}<span className="sd-faq__sign" aria-hidden="true" /></summary>
              <p>{faq.answer}</p>
            </details>)}
          </div>
        </section>}

        <section className="na-wrap sd-cta-section">
          <div className="na-cta">
            <h2>نوبت خود را همین حالا ثبت کنید</h2>
            <p>زمان‌های آزاد این هفته را ببینید و در کمتر از یک دقیقه نوبت بگیرید.</p>
            <button type="button" className="na-btn" onClick={book}>مشاهده زمان‌های آزاد</button>
          </div>
        </section>
      </main>
      <Footer singleDoctor onBook={book} contact={places[0]} />
      <div className="sd-booking-bar">
        <div className="na-wrap">
          <div><strong>انتخاب زمان نوبت</strong><span>{doctor.name || 'دریافت نوبت آنلاین'}</span></div>
          <button type="button" className="na-btn" onClick={book}>مشاهده زمان‌ها</button>
        </div>
      </div>
    </div>
  );
}
