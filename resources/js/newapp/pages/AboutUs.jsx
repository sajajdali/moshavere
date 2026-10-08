import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../lib/api';
import useAsync from '../lib/useAsync';
import { LoadingBlock, ErrorBox } from '../components/States.jsx';

/** معادل طرح: About Us.dc.html */
export default function AboutUs() {
  const { data, error, loading, reload } = useAsync(() => api.aboutUs(), []);

  const sections = [
    { title: data?.second_title, body: data?.second_body, image: data?.second_image },
    { title: data?.third_title, body: data?.third_body, image: data?.third_image },
    { title: data?.fourth_title, body: data?.fourth_body, image: data?.fourth_image },
  ].filter((s) => s.title || s.body);

  return (
    <>
      <section style={{ background: 'var(--c-brand)', padding: '20px 16px' }}>
        <h1 style={{ textAlign: 'center', color: '#fff', fontSize: 20, fontWeight: 700, margin: 0 }}>
          درباره ما
        </h1>
      </section>

      {loading && <LoadingBlock rows={3} height={140} />}
      {error && <ErrorBox error={error} onRetry={reload} />}

      {!loading && !error && (
        <>
          <section style={{ background: 'var(--c-bg-2, #f6f8f9)' }}>
            <div className="na-wrap" style={{ padding: '24px 16px', display: 'flex', flexDirection: 'column', gap: 10 }}>
              <div style={{ display: 'flex', flexWrap: 'wrap', alignItems: 'center', justifyContent: 'space-between', gap: 10 }}>
                <h2 style={{ fontSize: 16, fontWeight: 700, margin: 0 }}>{data?.first_title}</h2>
                <Link to="/search" className="na-btn na-btn--ghost">دریافت نوبت</Link>
              </div>
              {data?.first_body && (
                <p style={{ margin: 0, fontSize: 13, lineHeight: 1.9, color: 'var(--c-muted)' }}>{data.first_body}</p>
              )}
            </div>
          </section>

          <div className="na-wrap" style={{ padding: '40px 16px', display: 'grid', gap: 40 }}>
            {sections.map((s, i) => (
              <section key={i} style={{
                display: 'grid', gap: 24, alignItems: 'center',
                gridTemplateColumns: '1fr', direction: 'rtl',
              }}
                className="na-about-section"
              >
                <div style={{ order: i % 2 === 1 ? 2 : 1 }}>
                  {s.title && <h3 style={{ fontSize: 16, fontWeight: 700, margin: '0 0 10px' }}>{s.title}</h3>}
                  {s.body && <p style={{ margin: 0, fontSize: 13, lineHeight: 1.9, color: 'var(--c-muted)' }}>{s.body}</p>}
                </div>
                <div style={{ order: i % 2 === 1 ? 1 : 2, height: 220, borderRadius: 14, overflow: 'hidden', background: 'var(--c-brand-soft)' }}>
                  {s.image && (
                    <img src={s.image} alt={s.title || ''} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                  )}
                </div>
              </section>
            ))}
          </div>

          {Array.isArray(data?.faqs) && data.faqs.length > 0 && (
            <section style={{ background: 'var(--c-bg-2, #f6f8f9)', padding: '32px 16px' }}>
              <div className="na-wrap" style={{ display: 'grid', gap: 14 }}>
                <h3 style={{ fontSize: 16, fontWeight: 700, margin: 0 }}>سوالات متداول</h3>
                {data.faqs.map((f) => (
                  <details key={f.id} className="na-card" style={{ padding: '12px 16px' }}>
                    <summary style={{ cursor: 'pointer', fontSize: 13, fontWeight: 700 }}>{f.question}</summary>
                    <p style={{ margin: '10px 0 0', fontSize: 13, lineHeight: 1.9, color: 'var(--c-muted)' }}>{f.answer}</p>
                  </details>
                ))}
              </div>
            </section>
          )}
        </>
      )}
    </>
  );
}
