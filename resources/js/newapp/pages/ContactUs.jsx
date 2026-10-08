import React, { useState } from 'react';
import api from '../lib/api';
import useAsync from '../lib/useAsync';
import { useSite } from '../lib/SiteContext.jsx';
import { LoadingBlock, ErrorBox } from '../components/States.jsx';
import { toEnDigits } from '../lib/format';

/** معادل طرح: Contact Us.dc.html */
export default function ContactUs() {
  const { user } = useSite();
  const { data, error, loading, reload } = useAsync(() => api.contactUs(), []);

  const [mobile, setMobile] = useState('');
  const [fullName, setFullName] = useState('');
  const [message, setMessage] = useState('');
  const [formError, setFormError] = useState('');
  const [busy, setBusy] = useState(false);
  const [sent, setSent] = useState(false);

  const submit = async (e) => {
    e.preventDefault();
    setFormError('');

    if (!message.trim()) {
      setFormError('پیام خود را وارد کنید.');
      return;
    }
    if (!user) {
      const m = toEnDigits(mobile).trim();
      if (!/^09\d{9}$/.test(m)) {
        setFormError('شماره موبایل را به شکل صحیح وارد کنید.');
        return;
      }
      if (!fullName.trim()) {
        setFormError('نام و نام خانوادگی را وارد کنید.');
        return;
      }
    }

    setBusy(true);
    try {
      await api.sendContactMessage(user
        ? { message: message.trim() }
        : { mobile: toEnDigits(mobile).trim(), full_name: fullName.trim(), message: message.trim() });
      setSent(true);
      setMessage(''); setMobile(''); setFullName('');
    } catch (err) {
      setFormError(err.message);
    } finally {
      setBusy(false);
    }
  };

  return (
    <>
      <section style={{ background: 'var(--c-brand)', padding: '20px 16px' }}>
        <h1 style={{ textAlign: 'center', color: '#fff', fontSize: 20, fontWeight: 700, margin: 0 }}>
          درخواست مشاوره
        </h1>
      </section>

      {loading && <LoadingBlock rows={2} height={140} />}
      {error && <ErrorBox error={error} onRetry={reload} />}

      {!loading && !error && (
        <main style={{ background: 'var(--c-bg-2, #f6f8f9)', padding: '28px 16px' }}>
          {data?.first_section_show && (
            <div className="na-wrap" style={{ marginBottom: 24 }}>
              <h2 style={{ fontSize: 16, fontWeight: 700, margin: '0 0 8px' }}>{data.first_section_title}</h2>
              {data.first_section_body && (
                <p style={{ margin: 0, fontSize: 13, lineHeight: 1.9, color: 'var(--c-muted)' }}>{data.first_section_body}</p>
              )}
            </div>
          )}

          <div className="na-wrap" style={{ display: 'flex', flexWrap: 'wrap', gap: 20 }}>
            {data?.form_active && (
              <form onSubmit={submit} className="na-card" style={{ flex: '1 1 320px', display: 'grid', gap: 14 }}>
                <strong style={{ fontSize: 15 }}>به ما پیام بدهید</strong>

                {sent && (
                  <div style={{ fontSize: 13, color: 'var(--c-ok, #1a8a5f)', background: 'var(--c-ok-soft, #e6f7ef)', borderRadius: 10, padding: '10px 12px' }}>
                    نظر شما با موفقیت ثبت شد!
                  </div>
                )}
                {formError && (
                  <div style={{ fontSize: 13, color: 'var(--c-danger)', background: 'var(--c-danger-soft)', borderRadius: 10, padding: '10px 12px' }}>
                    {formError}
                  </div>
                )}

                {!user && (
                  <div style={{ display: 'grid', gap: 12, gridTemplateColumns: '1fr 1fr' }}>
                    <div>
                      <label style={{ display: 'block', fontSize: 12, marginBottom: 6 }}>ایمیل / شماره تلفن</label>
                      <input type="text" value={mobile} onChange={(e) => setMobile(e.target.value)}
                        placeholder="شماره موبایل"
                        style={{ width: '100%', borderRadius: 10, padding: '10px 12px', fontSize: 13, border: '1px solid var(--c-line)' }} />
                    </div>
                    <div>
                      <label style={{ display: 'block', fontSize: 12, marginBottom: 6 }}>نام و نام خانوادگی</label>
                      <input type="text" value={fullName} onChange={(e) => setFullName(e.target.value)}
                        placeholder="به فارسی"
                        style={{ width: '100%', borderRadius: 10, padding: '10px 12px', fontSize: 13, border: '1px solid var(--c-line)' }} />
                    </div>
                  </div>
                )}

                <div>
                  <label style={{ display: 'block', fontSize: 12, marginBottom: 6 }}>پیام شما</label>
                  <textarea value={message} onChange={(e) => setMessage(e.target.value)}
                    placeholder="پیام شما چیست؟" rows={4}
                    style={{ width: '100%', borderRadius: 10, padding: '10px 12px', fontSize: 13, border: '1px solid var(--c-line)', resize: 'none' }} />
                </div>

                <button type="submit" className="na-btn" disabled={busy} style={{ justifySelf: 'end' }}>
                  {busy ? 'در حال ارسال…' : 'ارسال پیام'}
                </button>
              </form>
            )}

            <div style={{ flex: '1 1 240px', display: 'grid', gap: 14, alignContent: 'start' }}>
              {data?.address && (
                <div className="na-card">
                  <strong style={{ display: 'block', fontSize: 14, marginBottom: 6 }}>آدرس</strong>
                  <span style={{ fontSize: 13, color: 'var(--c-muted)' }}>{data.address}</span>
                </div>
              )}
              {data?.email && (
                <div className="na-card">
                  <strong style={{ display: 'block', fontSize: 14, marginBottom: 6 }}>شماره پشتیبانی</strong>
                  <span style={{ fontSize: 13, color: 'var(--c-muted)' }}>{data.email}</span>
                </div>
              )}
            </div>
          </div>

          {Array.isArray(data?.faqs) && data.faqs.length > 0 && (
            <div className="na-wrap" style={{ marginTop: 32, display: 'grid', gap: 14 }}>
              <h3 style={{ fontSize: 16, fontWeight: 700, margin: 0 }}>سوالات متداول</h3>
              {data.faqs.map((f) => (
                <details key={f.id} className="na-card" style={{ padding: '12px 16px' }}>
                  <summary style={{ cursor: 'pointer', fontSize: 13, fontWeight: 700 }}>{f.question}</summary>
                  <p style={{ margin: '10px 0 0', fontSize: 13, lineHeight: 1.9, color: 'var(--c-muted)' }}>{f.answer}</p>
                </details>
              ))}
            </div>
          )}
        </main>
      )}
    </>
  );
}
