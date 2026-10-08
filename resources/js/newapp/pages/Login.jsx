import React, { useState } from 'react';
import { useNavigate, useSearchParams, Link } from 'react-router-dom';
import api from '../lib/api';
import { useSite } from '../lib/SiteContext.jsx';
import { toEnDigits, fa } from '../lib/format';

/** ورود بیمار — الگوی مودال ورود در طرح ها */
export default function Login() {
  const { site, setUser } = useSite();
  const [params] = useSearchParams();
  const next = params.get('next') || '/profile';
  const nav = useNavigate();

  const [step, setStep] = useState('phone');
  const [mobile, setMobile] = useState('');
  const [code, setCode] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  const submitPhone = async (e) => {
    e.preventDefault();
    const m = toEnDigits(mobile).trim();
    if (!/^09\d{9}$/.test(m)) {
      setError('شماره موبایل را به شکل صحیح وارد کنید.');
      return;
    }
    setBusy(true); setError('');
    try {
      await api.login(m);
      setStep('code');
    } catch (err) {
      setError(err.message);
    } finally {
      setBusy(false);
    }
  };

  const submitCode = async (e) => {
    e.preventDefault();
    const c = toEnDigits(code).trim();
    if (!c) { setError('کد ارسال شده را وارد کنید.'); return; }
    setBusy(true); setError('');
    try {
      const res = await api.verify(toEnDigits(mobile).trim(), c);
      setUser(res?.user ?? null);
      nav(next, { replace: true });
    } catch (err) {
      setError(err.message);
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="na-wrap" style={{ padding: '44px 16px', maxWidth: 460 }}>
      <div className="na-card">
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 20 }}>
          <span style={{
            width: 36, height: 36, borderRadius: 11, flex: '0 0 auto',
            background: 'linear-gradient(140deg, var(--c-brand), var(--c-accent-2))',
            display: 'grid', placeItems: 'center', color: '#fff', fontWeight: 800,
          }}>{(site?.title ?? 'ن').trim().charAt(0)}</span>
          <strong style={{ fontSize: 16 }}>
            {step === 'phone' ? 'ورود / ثبت‌نام' : 'تایید شماره موبایل'}
          </strong>
        </div>

        {step === 'phone' ? (
          <form onSubmit={submitPhone}>
            <label style={{ display: 'block', fontSize: 13, color: 'var(--c-muted)', marginBottom: 8 }}>
              شماره موبایل
            </label>
            <div className="na-field" style={{ marginBottom: 14 }}>
              <input
                type="tel" inputMode="numeric" value={mobile} autoFocus maxLength={11}
                onChange={(e) => {
                  const digitsOnly = e.target.value.replace(/[^0-9۰-۹]/g, '').slice(0, 11);
                  setMobile(digitsOnly);
                  setError('');
                }}
                placeholder="۰۹۱۲۳۴۵۶۷۸۹"
              />
            </div>

            {error && (
              <p style={{ fontSize: 13, color: 'var(--c-danger)', margin: '0 0 12px', lineHeight: 1.9 }}>{error}</p>
            )}

            <button type="submit" className="na-btn" style={{ width: '100%' }} disabled={busy}>
              {busy ? 'در حال ارسال…' : 'ارسال کد تایید'}
            </button>
          </form>
        ) : (
          <form onSubmit={submitCode}>
            <p style={{ fontSize: 13, color: 'var(--c-muted)', margin: '0 0 14px', lineHeight: 1.9 }}>
              کد تایید به شماره {fa(mobile)} ارسال شد.
            </p>

            <label style={{ display: 'block', fontSize: 13, color: 'var(--c-muted)', marginBottom: 8 }}>
              کد تایید
            </label>
            <div className="na-field" style={{ marginBottom: 14 }}>
              <input
                type="text" inputMode="numeric" value={code} autoFocus
                onChange={(e) => { setCode(e.target.value); setError(''); }}
                placeholder="- - - - -"
                style={{ letterSpacing: 6, textAlign: 'center' }}
              />
            </div>

            {error && (
              <p style={{ fontSize: 13, color: 'var(--c-danger)', margin: '0 0 12px', lineHeight: 1.9 }}>{error}</p>
            )}

            <button type="submit" className="na-btn" style={{ width: '100%', marginBottom: 10 }} disabled={busy}>
              {busy ? 'در حال بررسی…' : 'ورود'}
            </button>
            <button type="button" className="na-btn na-btn--ghost" style={{ width: '100%' }}
              onClick={() => { setStep('phone'); setCode(''); setError(''); }}>
              تغییر شماره
            </button>
          </form>
        )}
      </div>

      <p style={{ textAlign: 'center', marginTop: 18, fontSize: 13 }}>
        <Link to="/">بازگشت به صفحه اصلی</Link>
      </p>
    </div>
  );
}
