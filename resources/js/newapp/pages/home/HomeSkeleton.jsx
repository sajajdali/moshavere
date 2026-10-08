import React from 'react';
import { SectionLoading } from '../../components/States.jsx';
import '../../styles/single-doctor.css';

const skel = (style) => <div className="na-skel" style={style} />;

/**
 * اسکلتون صفحه اصلی، برای نمایش هنگام دریافت اطلاعات از سرور.
 * کل صفحه یک بار بلر و پالس می شود؛ به عمد چند تکه جدا و چند نوشته
 * «در حال بارگذاری» کنار هم نمایش داده نمی شود تا شلوغ نشود.
 * شکل هدر بر اساس حالت نوبت دهی فرق می کند (تک پزشک، یا کلینیکی/چند پزشک).
 */
export default function HomeSkeleton({ mode = 'single_doctor' }) {
  const isClinicLike = mode === 'clinic' || mode === 'single_doctor_with_doctors';

  return (
    <SectionLoading>
      <div className="sd-home">
        {isClinicLike ? (
          <section style={{ background: 'var(--c-brand)', padding: '52px 16px' }}>
            <div className="na-wrap">
              {skel({ width: '55%', height: 30, marginBottom: 14, background: 'rgba(255,255,255,.35)' })}
              {skel({ width: '35%', height: 16, marginBottom: 24, background: 'rgba(255,255,255,.25)' })}
              {skel({ width: '100%', maxWidth: 480, height: 54, borderRadius: 15, background: 'rgba(255,255,255,.3)' })}
            </div>
          </section>
        ) : (
          <section className="na-hero">
            <div className="na-wrap na-hero__grid">
              <div>
                {skel({ width: 130, height: 28, borderRadius: 999, marginBottom: 16 })}
                {skel({ width: '70%', height: 34, marginBottom: 10 })}
                {skel({ width: '45%', height: 18, marginBottom: 18 })}
                {skel({ width: '100%', height: 14, marginBottom: 8 })}
                {skel({ width: '90%', height: 14, marginBottom: 20 })}
                <div style={{ display: 'flex', gap: 10 }}>
                  {skel({ width: 180, height: 54, borderRadius: 15 })}
                  {skel({ width: 140, height: 54, borderRadius: 15 })}
                </div>
              </div>
              <div>
                {skel({ width: '100%', aspectRatio: '4/3', borderRadius: 24, marginBottom: 12 })}
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 10 }}>
                  {skel({ height: 70, borderRadius: 16 })}
                  {skel({ height: 70, borderRadius: 16 })}
                </div>
              </div>
            </div>
          </section>
        )}

        <section className="na-wrap sd-section">
          {skel({ width: 160, height: 26, marginBottom: 10 })}
          {skel({ width: 280, height: 14, marginBottom: 20 })}
          <div className="na-grid na-grid--services">
            {Array.from({ length: 3 }).map((_, i) => (
              <div key={i} className="na-svc-card">
                <div style={{ display: 'flex', gap: 12, marginBottom: 12 }}>
                  {skel({ width: 42, height: 42, borderRadius: isClinicLike ? '50%' : 13 })}
                  <div style={{ flex: 1 }}>
                    {skel({ width: '80%', height: 16, marginBottom: 8 })}
                    {skel({ width: '50%', height: 12 })}
                  </div>
                </div>
                {skel({ width: '100%', height: 40, borderRadius: 12 })}
              </div>
            ))}
          </div>
        </section>

        {!isClinicLike && (
          <section className="na-wrap sd-section">
            {skel({ width: 200, height: 26, marginBottom: 20 })}
            <div className="sd-places">
              <div className="na-contact-card">
                {skel({ width: '60%', height: 18, marginBottom: 10 })}
                {skel({ width: '90%', height: 14, marginBottom: 6 })}
                {skel({ width: '70%', height: 14 })}
              </div>
            </div>
          </section>
        )}
      </div>
    </SectionLoading>
  );
}
