import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../lib/api';
import { useSite } from '../lib/SiteContext.jsx';
import { toEnDigits } from '../lib/format';

/** متن های هر نوع ورود؛ ظاهر صفحه برای مدیر و پزشک یکسان است */
const COPY = {
  admin: {
    eyebrow: 'پنل مدیریت',
    description: 'مدیریت نوبت‌ها، بیماران و تنظیمات مجموعه در یک فضای امن و یکپارچه.',
    security: 'ورود امن ویژه مدیران و کارکنان مجاز',
    heading: 'ورود به پنل مدیریت',
    hint: 'اطلاعات حساب مدیریتی خود را وارد کنید.',
  },
  doctor: {
    eyebrow: 'پنل پزشک',
    description: 'مدیریت نوبت‌ها، برنامه کاری و بیماران خود در یک فضای امن و یکپارچه.',
    security: 'ورود امن ویژه پزشکان',
    heading: 'ورود پزشک',
    hint: 'شماره موبایل و رمز عبور خود را وارد کنید.',
  },
};

export default function AdminLogin({ variant = 'admin' }) {
  const { site, images } = useSite();
  const copy = COPY[variant] ?? COPY.admin;
  const canRegister = variant === 'doctor' && site?.doctorRegistration;
  const [mobile, setMobile] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  const title = site?.title || 'سامانه نوبت‌دهی';
  const logo = images?.clinic_logo || site?.logo;

  const submit = async (event) => {
    event.preventDefault();
    const normalizedMobile = toEnDigits(mobile).trim();

    if (!/^09\d{9}$/.test(normalizedMobile)) {
      setError('شماره موبایل را به شکل صحیح وارد کنید.');
      return;
    }
    if (!password) {
      setError('رمز عبور را وارد کنید.');
      return;
    }

    setBusy(true);
    setError('');
    try {
      const response = await api.adminLogin(normalizedMobile, password);
      window.location.assign(response?.redirect || '/admin/dashboard');
    } catch (err) {
      setError(err?.message || 'ورود به پنل انجام نشد.');
    } finally {
      setBusy(false);
    }
  };

  return (
    <main className="admin-login-page">
      <section className="admin-login-shell" aria-labelledby="admin-login-title">
        <div className="admin-login-brand">
          <div className="admin-login-brand__mark">
            {logo
              ? <img src={logo} alt="" />
              : <span>{title.trim().charAt(0)}</span>}
          </div>
          <div>
            <p className="admin-login-brand__eyebrow">{copy.eyebrow}</p>
            <h1>{title}</h1>
            <p>{copy.description}</p>
          </div>
          <div className="admin-login-brand__security">
            <ShieldIcon />
            <span>{copy.security}</span>
          </div>
        </div>

        <div className="admin-login-form-wrap">
          <div className="admin-login-heading">
            <span className="admin-login-heading__icon"><LockIcon /></span>
            <div>
              <h2 id="admin-login-title">{copy.heading}</h2>
              <p>{copy.hint}</p>
            </div>
          </div>

          <form onSubmit={submit} className="admin-login-form" noValidate>
            <label>
              <span>شماره موبایل</span>
              <span className="admin-login-field">
                <PhoneIcon />
                <input
                  type="tel"
                  inputMode="numeric"
                  autoComplete="username"
                  name="mobile"
                  dir="ltr"
                  maxLength={11}
                  autoFocus
                  value={mobile}
                  placeholder="09123456789"
                  onChange={(event) => {
                    setMobile(event.target.value.replace(/[^0-9۰-۹]/g, '').slice(0, 11));
                    setError('');
                  }}
                />
              </span>
            </label>

            <label>
              <span>رمز عبور</span>
              <span className="admin-login-field">
                <LockIcon />
                <input
                  type={showPassword ? 'text' : 'password'}
                  autoComplete="current-password"
                  name="password"
                  dir="ltr"
                  maxLength={255}
                  value={password}
                  placeholder="رمز عبور"
                  onChange={(event) => { setPassword(event.target.value); setError(''); }}
                />
                <button
                  type="button"
                  className="admin-login-password-toggle"
                  onClick={() => setShowPassword((visible) => !visible)}
                  aria-label={showPassword ? 'پنهان کردن رمز عبور' : 'نمایش رمز عبور'}
                >
                  <EyeIcon crossed={showPassword} />
                </button>
              </span>
            </label>

            {error && <div className="admin-login-error" role="alert">{error}</div>}

            <button type="submit" className="na-btn admin-login-submit" disabled={busy}>
              {busy ? <><span className="na-spinner" /> در حال ورود…</> : 'ورود به پنل'}
            </button>
          </form>

          {canRegister && (
            <p className="admin-login-register">
              حساب کاربری ندارید؟{' '}
              {/* صفحه ثبت نام پزشک سمت سرور رندر می شود، پس بارگذاری کامل لازم است */}
              <a href="/registration-doctor">ثبت نام پزشک</a>
            </p>
          )}

          <Link to="/" className="admin-login-back">بازگشت به سایت</Link>
        </div>
      </section>
    </main>
  );
}

function PhoneIcon() {
  return <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm3 15h4M9 6h6" /></svg>;
}

function LockIcon() {
  return <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10V8a5 5 0 0 1 10 0v2m-11 0h12a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2Zm6 4v3" /></svg>;
}

function ShieldIcon() {
  return <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v5c0 4.6 2.8 8.1 7 10 4.2-1.9 7-5.4 7-10V6l-7-3Zm-3 9 2 2 4-4" /></svg>;
}

function EyeIcon({ crossed }) {
  return <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Zm9.5 2.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />{crossed && <path d="m4 4 16 16" />}</svg>;
}
