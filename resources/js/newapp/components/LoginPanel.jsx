import React, { useEffect, useRef, useState } from 'react';
import api from '../lib/api';
import { useSite } from '../lib/SiteContext.jsx';
import { toEnDigits, fa } from '../lib/format';

const OTP_LENGTH = 4;
const birthdayParts = (value) => {
  const [year = '', month = '', day = ''] = toEnDigits(value ?? '').split('/');
  return { year, month, day };
};
const pickerBirthdayDate = (value) => {
  const { year, month, day } = birthdayParts(value);
  return year && month && day ? `${year}/${month.padStart(2, '0')}/${day.padStart(2, '0')}` : '';
};

/**
 * فرم ورود با کد پیامکی، برای استفاده داخل مودال ها.
 * سه گام دارد: شماره موبایل، کد تایید و در صورت نیاز تکمیل نام.
 * پس از ورود موفق onDone صدا زده می شود تا ادامه کار (مثلاً ثبت نوبت) انجام شود.
 *
 * معادل طرح: بخش ورود در Clinic Booking Home.dc.html
 */
export default function LoginPanel({ title, hint, onDone, registrationOnly = false, registrationUser = null }) {
  const { setUser, site, user } = useSite();
  const existingUser = registrationUser ?? user;

  const [step, setStep] = useState(registrationOnly ? 'name' : 'phone');
  const [mobile, setMobile] = useState('');
  const [otp, setOtp] = useState(Array(OTP_LENGTH).fill(''));
  const [first, setFirst] = useState(registrationOnly ? existingUser?.first_name ?? '' : '');
  const [last, setLast] = useState(registrationOnly ? existingUser?.last_name ?? '' : '');
  const [nationalCode, setNationalCode] = useState(registrationOnly ? existingUser?.national_code ?? '' : '');
  const [birthdayDate, setBirthdayDate] = useState(() => pickerBirthdayDate(registrationOnly ? existingUser?.birthday : null));
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);
  const [timer, setTimer] = useState(0);

  const cells = useRef([]);
  const tick = useRef(null);
  const otpAbort = useRef(null);
  const birthdayInput = useRef(null);

  useEffect(() => {
    if (step !== 'name' || !site?.birthdayRequired || !birthdayInput.current) return undefined;
    const input = birthdayInput.current;
    const picker = window.jQuery(input).persianDatepicker({
      calendarType: 'persian',
      viewMode: 'year',
      format: 'YYYY/MM/DD',
      initialValue: false,
      persianDigit: false,
      autoClose: true,
      maxDate: new window.persianDate().valueOf(),
      onSelect: (unixDate) => {
        setBirthdayDate(new window.persianDate(unixDate).toLocale('en').format('YYYY/MM/DD'));
        setError('');
      },
    });
    return () => {
      picker.destroy();
    };
  }, [step, site?.birthdayRequired]);

  // شمارش معکوس ارسال دوباره کد
  useEffect(() => {
    if (timer <= 0) return undefined;
    tick.current = setTimeout(() => setTimer((t) => t - 1), 1000);
    return () => clearTimeout(tick.current);
  }, [timer]);

  useEffect(() => () => clearTimeout(tick.current), []);

  // خواندن خودکار کد از پیامک با WebOTP API (اندروید/کروم).
  // در مرورگرهایی که این قابلیت را ندارند (اکثر آیفون‌ها، دسکتاپ) بی‌صدا نادیده گرفته می‌شود
  // و صفحه‌کلید موبایل با همان autoComplete="one-time-code" پیشنهاد ورود خودکار را نشان می‌دهد.
  useEffect(() => {
    if (step !== 'code') return undefined;
    if (typeof window === 'undefined' || !('OTPCredential' in window) || !navigator.credentials?.get) {
      return undefined;
    }

    const ac = new AbortController();
    otpAbort.current = ac;

    navigator.credentials
      .get({ otp: { transport: ['sms'] }, signal: ac.signal })
      .then((cred) => { if (cred?.code) fillOtp(cred.code); })
      .catch(() => { /* لغو شده یا کاربر اجازه نداد؛ ورود دستی همچنان کار می کند */ });

    return () => ac.abort();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [step]);

  const sendCode = async (resend = false) => {
    const m = toEnDigits(mobile).trim();

    if (!/^09\d{9}$/.test(m)) {
      setError('شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.');
      return;
    }

    setBusy(true);
    setError('');

    try {
      const res = await api.login(m);

      // اگر ورود بدون کد تایید فعال باشد، سرور همان جا کاربر را وارد می کند
      if (res?.verified) {
        setUser(res.user ?? null);
        if (res.needs_registration) {
          setFirst(res.user?.first_name ?? '');
          setLast(res.user?.last_name ?? '');
          setNationalCode(res.user?.national_code ?? '');
          setBirthdayDate(pickerBirthdayDate(res.user?.birthday));
          setStep('name');
          return;
        }
        onDone?.(res.user ?? null);
        return;
      }

      setOtp(Array(OTP_LENGTH).fill(''));
      setStep('code');
      setTimer(res?.retry_after ?? 120);
      setTimeout(() => cells.current[0]?.focus(), 60);
    } catch (err) {
      setError(err?.message ?? 'ارسال کد انجام نشد.');
      // اگر سرور مهلت باقی مانده را گفت، همان را نشان می دهیم
      if (err?.data?.retry_after) {
        setTimer(err.data.retry_after);
        if (resend) setStep('code');
      }
    } finally {
      setBusy(false);
    }
  };

  const submitCode = async (value) => {
    const code = toEnDigits(value ?? otp.join('')).trim();

    if (code.length !== OTP_LENGTH) {
      setError('کد تایید چهار رقمی را کامل وارد کنید.');
      return;
    }

    setBusy(true);
    setError('');

    try {
      const res = await api.verify(toEnDigits(mobile).trim(), code);
      setUser(res?.user ?? null);

      if (res?.needs_registration) {
        setFirst(res.user?.first_name ?? '');
        setLast(res.user?.last_name ?? '');
        setNationalCode(res.user?.national_code ?? '');
        setBirthdayDate(pickerBirthdayDate(res.user?.birthday));
        setStep('name');
        return;
      }
      onDone?.(res?.user ?? null);
    } catch (err) {
      setError(err?.message ?? 'کد وارد شده صحیح نیست.');
      setOtp(Array(OTP_LENGTH).fill(''));
      cells.current[0]?.focus();
    } finally {
      setBusy(false);
    }
  };

  const submitName = async (e) => {
    e.preventDefault();

    if (!first.trim() || !last.trim()) {
      setError('نام و نام خانوادگی را وارد کنید.');
      return;
    }

    const code = toEnDigits(nationalCode).trim();
    if (site?.nationalCodeRequired && !code) {
      setError('وارد کردن کد ملی الزامی است.');
      return;
    }
    if (code && !/^\d{10}$/.test(code)) {
      setError('کد ملی باید ۱۰ رقم باشد.');
      return;
    }

    const birth = birthdayParts(birthdayDate.trim());
    if (site?.birthdayRequired && (!birth.year || !birth.month || !birth.day)) {
      setError('وارد کردن تاریخ تولد الزامی است.');
      return;
    }
    if (site?.birthdayRequired && (birth.year || birth.month || birth.day) && (
      !/^\d{4}$/.test(birth.year) || !/^\d{1,2}$/.test(birth.month) || !/^\d{1,2}$/.test(birth.day)
      || Number(birth.year) < 1300 || Number(birth.year) > 1500
      || Number(birth.month) < 1 || Number(birth.month) > 12
      || Number(birth.day) < 1 || Number(birth.day) > (Number(birth.month) <= 6 ? 31 : 30)
    )) {
      setError('تاریخ تولد را به شکل صحیح وارد کنید.');
      return;
    }

    setBusy(true);
    setError('');

    try {
      const res = await api.completeRegistration({
        first_name: first.trim(),
        last_name: last.trim(),
        national_code: code,
        ...(site?.birthdayRequired ? {
          birthday_year: birth.year,
          birthday_month: birth.month,
          birthday_day: birth.day,
        } : {}),
      });
      setUser(res?.user ?? null);
      onDone?.(res?.user ?? null);
    } catch (err) {
      setError(err?.message ?? 'ثبت اطلاعات انجام نشد.');
    } finally {
      setBusy(false);
    }
  };

  /** پر کردن همه خانه ها از یک کد کامل — پیست، خواندن خودکار پیامک یا پرشدن صفحه کلید */
  const fillOtp = (raw) => {
    const digits = toEnDigits(raw).replace(/\D/g, '').slice(0, OTP_LENGTH).split('');
    if (digits.length !== OTP_LENGTH) return false;

    setOtp(digits);
    setError('');
    otpAbort.current?.abort();
    cells.current[OTP_LENGTH - 1]?.focus();
    submitCode(digits.join(''));
    return true;
  };

  /** نوشتن یک رقم در خانه کد و رفتن به خانه بعد */
  const setCell = (i, raw) => {
    // اگر چند رقم یک جا وارد شده باشد (پیست یا پیشنهاد صفحه کلید)، کل کد پر می شود
    const cleaned = toEnDigits(raw).replace(/\D/g, '');
    if (cleaned.length > 1) { fillOtp(cleaned); return; }

    const digit = cleaned.slice(-1);

    setOtp((prev) => {
      const next = prev.slice();
      next[i] = digit;

      // با کامل شدن کد، بدون فشردن دکمه بررسی می شود
      if (digit && next.every((d) => d)) submitCode(next.join(''));
      return next;
    });

    setError('');
    if (digit && i + 1 < OTP_LENGTH) cells.current[i + 1]?.focus();
  };

  const onCellKey = (i, e) => {
    if (e.key === 'Backspace' && !otp[i] && i > 0) cells.current[i - 1]?.focus();
    if (e.key === 'Enter') submitCode();
  };

  const onCellPaste = (e) => {
    const text = e.clipboardData?.getData('text');
    if (text && fillOtp(text)) e.preventDefault();
  };

  const heading = step === 'phone'
    ? (title ?? 'ورود / ثبت‌نام')
    : step === 'code' ? 'کد تایید' : 'تکمیل اطلاعات';

  return (
    <div>
      <strong style={{ display: 'block', fontSize: 15, marginBottom: 12 }}>{heading}</strong>

      {step === 'phone' && (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
          <p style={{ margin: 0, fontSize: 13, lineHeight: 1.95, color: 'var(--c-muted)' }}>
            {hint ?? 'شماره موبایل خود را وارد کنید. کد تایید چهاررقمی برای شما پیامک می‌شود.'}
          </p>

          <label style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
            <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--c-ink-2)' }}>شماره موبایل</span>
            <span className="na-field">
              <input
                type="tel" inputMode="numeric" dir="ltr" autoFocus
                autoComplete="tel" name="mobile" maxLength={11}
                value={mobile} placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                onChange={(e) => {
                  // فقط رقم (فارسی یا انگلیسی) و حداکثر ۱۱ رقم
                  const digitsOnly = e.target.value.replace(/[^0-9۰-۹]/g, '').slice(0, 11);
                  setMobile(digitsOnly);
                  setError('');
                }}
                onKeyDown={(e) => { if (e.key === 'Enter') sendCode(); }}
                style={{ textAlign: 'left', letterSpacing: 1, fontSize: 17 }}
              />
            </span>
          </label>

          {error && <ErrorNote text={error} />}

          <button type="button" className="na-btn" disabled={busy}
            style={{ width: '100%', minHeight: 52 }} onClick={() => sendCode()}>
            {busy ? 'در حال ارسال…' : 'دریافت کد تایید'}
          </button>
        </div>
      )}

      {step === 'code' && (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
          <p style={{ margin: 0, fontSize: 13, lineHeight: 1.95, color: 'var(--c-muted)' }}>
            کد چهاررقمی ارسال‌شده به{' '}
            <strong style={{ color: 'var(--c-ink)' }} dir="ltr">{fa(mobile)}</strong> را وارد کنید.
            <button type="button" onClick={() => { setStep('phone'); setError(''); }}
              style={{
                border: 0, background: 'transparent', color: 'var(--c-brand)',
                fontWeight: 700, fontSize: 13, padding: '0 4px',
                cursor: 'pointer', fontFamily: 'inherit',
              }}>ویرایش شماره</button>
          </p>

          <div dir="ltr" style={{ display: 'flex', gap: 8, justifyContent: 'space-between' }}>
            {otp.map((v, i) => (
              <input
                key={i} type="tel" inputMode="numeric" maxLength={i === 0 ? OTP_LENGTH : 1} value={v}
                ref={(el) => { cells.current[i] = el; }}
                // خانه اول autoComplete خودکار پیامک را فعال می کند؛ بقیه فقط برای ورود دستی هستند
                autoComplete={i === 0 ? 'one-time-code' : 'off'}
                onChange={(e) => setCell(i, e.target.value)}
                onKeyDown={(e) => onCellKey(i, e)}
                onPaste={onCellPaste}
                aria-label={`رقم ${i + 1} کد تایید`}
                style={{
                  flex: '1 1 0', minWidth: 0, height: 58, textAlign: 'center',
                  fontSize: 22, fontWeight: 700, color: 'var(--c-ink)',
                  border: '1px solid var(--c-line-2)', borderRadius: 14,
                  background: 'var(--c-bg)', outline: 'none',
                }}
              />
            ))}
          </div>

          {error && <ErrorNote text={error} />}

          <button type="button" className="na-btn" disabled={busy}
            style={{ width: '100%', minHeight: 52 }} onClick={() => submitCode()}>
            {busy ? 'در حال بررسی…' : 'تایید و ورود'}
          </button>

          <div style={{
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            gap: 6, fontSize: 13, color: 'var(--c-muted)',
          }}>
            {timer > 0
              ? <span>ارسال دوباره کد تا {fa(timer)} ثانیه دیگر</span>
              : (
                <button type="button" onClick={() => sendCode(true)} disabled={busy}
                  style={{
                    border: 0, background: 'transparent', color: 'var(--c-brand)',
                    fontWeight: 700, fontSize: 13, cursor: 'pointer',
                    fontFamily: 'inherit', padding: 8,
                  }}>ارسال دوباره کد</button>
              )}
          </div>
        </div>
      )}

      {step === 'name' && (
        <form onSubmit={submitName} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
          <p style={{ margin: 0, fontSize: 13, lineHeight: 1.95, color: 'var(--c-muted)' }}>
            برای ثبت نوبت، اطلاعات خود را وارد کنید.
          </p>

          <label style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
            <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--c-ink-2)' }}>نام</span>
            <span className="na-field">
              <input type="text" value={first} autoFocus
                onChange={(e) => { setFirst(e.target.value); setError(''); }} />
            </span>
          </label>

          <label style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
            <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--c-ink-2)' }}>نام خانوادگی</span>
            <span className="na-field">
              <input type="text" value={last}
                onChange={(e) => { setLast(e.target.value); setError(''); }} />
            </span>
          </label>

          <label style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
            <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--c-ink-2)' }}>
              کد ملی{site?.nationalCodeRequired && <span style={{ color: 'var(--c-danger)' }}> *</span>}
            </span>
            <span className="na-field">
              <input type="text" inputMode="numeric" dir="ltr" maxLength={10}
                value={nationalCode} placeholder="۱۰ رقم"
                onChange={(e) => {
                  setNationalCode(toEnDigits(e.target.value).replace(/\D/g, '').slice(0, 10));
                  setError('');
                }} />
            </span>
          </label>

          {site?.birthdayRequired && (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
              <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--c-ink-2)' }}>
                تاریخ تولد (شمسی)<span style={{ color: 'var(--c-danger)' }}> *</span>
              </span>
              <span className="na-field">
                <input ref={birthdayInput} type="text" dir="ltr" readOnly
                  value={birthdayDate} placeholder="انتخاب تاریخ تولد" autoComplete="off" />
              </span>
            </div>
          )}

          {error && <ErrorNote text={error} />}

          <button type="submit" className="na-btn" disabled={busy}
            style={{ width: '100%', minHeight: 52 }}>
            {busy ? 'در حال ثبت…' : 'ثبت و ادامه'}
          </button>
        </form>
      )}
    </div>
  );
}

function ErrorNote({ text }) {
  return (
    <span style={{
      fontSize: 12.5, fontWeight: 600, color: 'var(--c-danger)',
      background: 'var(--c-danger-soft)', borderRadius: 10,
      padding: '10px 12px', lineHeight: 1.9,
    }}>{text}</span>
  );
}
