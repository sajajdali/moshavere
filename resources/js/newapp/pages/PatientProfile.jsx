import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../lib/api';
import useAsync from '../lib/useAsync';
import { useSite } from '../lib/SiteContext.jsx';
import { Loading, ErrorBox } from '../components/States.jsx';
import { fa, toEnDigits } from '../lib/format';

const TABS = [
  { key: 'upcoming', label: 'پیش‌رو' },
  { key: 'past', label: 'انجام‌شده' },
  { key: 'canceled', label: 'لغوشده' },
];

const EMPTY_TEXT = {
  upcoming: 'نوبت پیش‌رویی ندارید',
  past: 'هنوز ویزیتی انجام نشده',
  canceled: 'نوبت لغوشده‌ای ندارید',
};

const REASONS = [
  'مشکل پیش‌آمده و نمی‌رسم',
  'زمان دیگری رزرو کرده‌ام',
  'حال عمومی بهتر شده',
  'دلیل دیگر',
];

/** رنگ و برچسب وضعیت پرداخت، مطابق طرح */
const PAY = {
  pending: { label: 'در انتظار پرداخت', badge: 'پرداخت‌نشده', fg: 'var(--c-gold)', bg: 'var(--c-gold-soft)' },
  paid: { label: 'پرداخت‌شده', badge: 'تأییدشده', fg: 'var(--c-accent)', bg: 'var(--c-ok-soft)' },
  refunded: { label: 'مبلغ بازگردانده شد', badge: 'لغوشده', fg: 'var(--c-danger)', bg: 'var(--c-danger-soft)' },
  canceled: { label: 'بدون پرداخت', badge: 'لغوشده', fg: 'var(--c-danger)', bg: 'var(--c-danger-soft)' },
  awaiting_confirmation: { label: 'در انتظار تایید', badge: 'در انتظار تایید', fg: 'var(--c-gold)', bg: 'var(--c-gold-soft)' },
};

/** معادل طرح: Patient Profile.dc.html */
export default function PatientProfile() {
  const { user, setUser, openLogin } = useSite();
  const [tab, setTab] = useState('upcoming');
  const [editOpen, setEditOpen] = useState(false);
  const [cancelFor, setCancelFor] = useState(null);

  // مهمان: به جای رفتن به صفحه جداگانه، همان مودال سراسری ورود باز می شود
  useEffect(() => {
    if (!user) openLogin({ hint: 'برای مشاهده نوبت های خود وارد حساب کاربری شوید.' });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [user]);

  const { data, error, loading, reload } = useAsync(() => api.profile(), [user], { immediate: !!user });

  if (!user) return null;
  if (loading) return <Loading label="در حال دریافت نوبت های شما" />;
  if (error) return <ErrorBox error={error} onRetry={reload} />;

  const profile = data?.user ?? user;
  const info = data?.info ?? {};
  const all = data?.appointments ?? [];
  const items = all.filter((a) => a.group === tab);

  const infoRows = [
    ['نام و نام خانوادگی', profile.name],
    ['شماره موبایل', info.mobile ? fa(info.mobile) : null],
    ['کد ملی', info.national_code ? fa(info.national_code) : null],
    ['تاریخ تولد', info.birthday ? fa(info.birthday) : null],
    ['جنسیت', info.gender],
    ['شهر', info.city],
  ].filter(([, v]) => v);

  return (
    <div className="na-wrap" style={{
      padding: '20px 16px 40px', maxWidth: 960,
      display: 'flex', flexDirection: 'column', gap: 16,
    }}>
      {/* کارت کاربر */}
      <section className="na-card" style={{
        display: 'flex', flexWrap: 'wrap', alignItems: 'center', gap: 16,
      }}>
        <span style={{
          width: 64, height: 64, flex: '0 0 auto', borderRadius: 20,
          background: 'var(--c-brand-soft)', display: 'grid', placeItems: 'center',
          fontSize: 22, fontWeight: 800, color: 'var(--c-brand)',
        }}>{(profile.name ?? 'ب').trim().charAt(0)}</span>

        <div style={{ flex: '1 1 200px', minWidth: 0 }}>
          <strong style={{ display: 'block', fontSize: 19, fontWeight: 800 }}>
            {profile.name ?? 'بیمار'}
          </strong>
          {info.mobile && (
            <span style={{ display: 'block', fontSize: 13, color: 'var(--c-muted)', marginTop: 4 }} dir="ltr">
              {fa(info.mobile)}
            </span>
          )}
        </div>

        <div style={{ flex: '0 0 auto', display: 'flex', gap: 8, flexWrap: 'wrap' }}>
          <button type="button" onClick={() => setEditOpen(true)}
            style={{
              minHeight: 44, padding: '0 16px', borderRadius: 12,
              border: '1px solid var(--c-line-2)', background: '#fff',
              color: 'var(--c-brand)', fontSize: 13, fontWeight: 700, cursor: 'pointer',
            }}>ویرایش اطلاعات</button>

          <button type="button"
            onClick={async () => {
              await api.logout().catch(() => {});
              setUser(null);
              window.location.href = '/';
            }}
            style={{
              minHeight: 44, padding: '0 16px', borderRadius: 12,
              border: '1px solid var(--c-line)', background: '#fff',
              color: 'var(--c-muted-2)', fontSize: 13, fontWeight: 700, cursor: 'pointer',
            }}>خروج</button>
        </div>
      </section>

      {/* تب ها و فهرست نوبت ها */}
      <section style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
        <div style={{
          display: 'flex', gap: 4, background: '#e7eded',
          border: '1px solid #dde5e4', borderRadius: 16, padding: 5,
        }}>
          {TABS.map((t) => {
            const on = tab === t.key;
            const count = all.filter((a) => a.group === t.key).length;
            return (
              <button key={t.key} type="button" onClick={() => setTab(t.key)}
                style={{
                  flex: '1 1 0', minHeight: 46, borderRadius: 12, border: 0,
                  display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6,
                  fontSize: 13, fontWeight: 700, cursor: 'pointer', fontFamily: 'inherit',
                  ...(on
                    ? { background: '#fff', color: 'var(--c-brand)', boxShadow: '0 2px 8px -4px rgba(9,28,42,.45)' }
                    : { background: 'transparent', color: 'var(--c-muted)' }),
                }}>
                <span>{t.label}</span>
                {count > 0 && (
                  <span style={{
                    display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
                    minWidth: 20, height: 20, padding: '0 6px', borderRadius: 999,
                    fontSize: 11, fontWeight: 800, lineHeight: 1,
                    ...(on
                      ? { background: 'var(--c-brand)', color: '#fff' }
                      : { background: 'rgba(20,92,99,0.12)', color: 'var(--c-muted)' }),
                  }}>{fa(count)}</span>
                )}
              </button>
            );
          })}
        </div>

        {items.length === 0 ? (
          <div style={{
            background: '#fff', border: '1px dashed var(--c-line-2)', borderRadius: 20,
            padding: '36px 20px', textAlign: 'center',
            display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 12,
          }}>
            <span style={{
              width: 52, height: 52, borderRadius: '50%', background: 'var(--c-bg)',
              display: 'grid', placeItems: 'center', fontSize: 22, color: 'var(--c-muted-2)',
            }} aria-hidden="true">◷</span>
            <strong style={{ fontSize: 15 }}>{EMPTY_TEXT[tab]}</strong>
            <Link to="/" style={{
              minHeight: 46, display: 'flex', alignItems: 'center', padding: '0 20px',
              borderRadius: 13, background: 'var(--c-brand)', color: '#fff',
              fontSize: 14, fontWeight: 700,
            }}>رزرو نوبت جدید</Link>
          </div>
        ) : (
          <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
            {items.map((a) => {
              const pay = PAY[a.pay] ?? PAY.paid;
              // نوبت گذشته، صرف نظر از پرداخت، «انجام‌شده» است
              const badge = a.group === 'past'
                ? { text: 'انجام‌شده', fg: 'var(--c-muted)', bg: 'var(--c-placeholder)' }
                : { text: pay.badge, fg: pay.fg, bg: pay.bg };

              return (
                <article key={a.id} className="na-card"
                  style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
                  <div style={{ display: 'flex', alignItems: 'flex-start', gap: 12 }}>
                    <span style={{
                      flex: '0 0 auto', width: 52, borderRadius: 14,
                      background: 'var(--c-brand-soft)', padding: '8px 4px', textAlign: 'center',
                    }}>
                      <span style={{ display: 'block', fontSize: 11, color: 'var(--c-muted)' }}>{a.month}</span>
                      <strong style={{ display: 'block', fontSize: 19, fontWeight: 800, color: 'var(--c-brand)' }}>
                        {fa(a.day ?? '')}
                      </strong>
                    </span>

                    <span style={{ flex: '1 1 auto', minWidth: 0 }}>
                      <strong style={{ display: 'block', fontSize: 16, lineHeight: 1.5 }}>{a.service}</strong>
                      <span style={{ display: 'block', fontSize: 13, color: 'var(--c-muted)', marginTop: 4 }}>
                        {a.doctor}{a.time ? ` · ساعت ${fa(a.time)}` : ''}
                      </span>
                      {a.place && (
                        <span style={{ display: 'block', fontSize: 12, color: 'var(--c-muted-2)', marginTop: 4 }}>
                          {a.place}
                        </span>
                      )}
                    </span>

                    <span style={{
                      flex: '0 0 auto', fontSize: 11, fontWeight: 700, borderRadius: 999,
                      padding: '6px 10px', whiteSpace: 'nowrap',
                      color: badge.fg, background: badge.bg,
                    }}>{badge.text}</span>
                  </div>

                  <div style={{
                    display: 'flex', flexWrap: 'wrap', alignItems: 'center', gap: 10,
                    borderTop: '1px dashed var(--c-line)', paddingTop: 12,
                  }}>
                    <span style={{ flex: '1 1 auto', minWidth: 0, fontSize: 12, color: 'var(--c-muted)' }}>
                      کد پیگیری {fa(a.code)}
                      {a.group === 'past' ? '' : ` · ${pay.label}`}
                      {a.fee_label ? ` · ${fa(a.fee_label)}` : ''}
                    </span>

                    {a.can_pay && a.payment_url && (
                      <a href={a.payment_url} style={{
                        minHeight: 42, display: 'flex', alignItems: 'center', padding: '0 16px',
                        borderRadius: 12, background: 'var(--c-brand)', color: '#fff',
                        fontSize: 13, fontWeight: 700,
                      }}>پرداخت</a>
                    )}

                    {a.can_cancel && (
                      <button type="button" onClick={() => setCancelFor(a)}
                        style={{
                          minHeight: 42, padding: '0 16px', borderRadius: 12,
                          border: '1px solid #e0cac7', background: '#fff',
                          color: 'var(--c-danger)', fontSize: 13, fontWeight: 700, cursor: 'pointer',
                        }}>لغو نوبت</button>
                    )}

                    <Link to={`/appointment/${encodeURIComponent(a.code)}`} style={{
                      minHeight: 42, display: 'flex', alignItems: 'center', padding: '0 16px',
                      borderRadius: 12, border: '1px solid var(--c-line-2)',
                      color: 'var(--c-brand)', fontSize: 13, fontWeight: 700,
                    }}>جزئیات</Link>
                  </div>
                </article>
              );
            })}
          </div>
        )}
      </section>

      {/* اطلاعات من */}
      {infoRows.length > 0 && (
        <section className="na-card">
          <strong style={{ display: 'block', fontSize: 16, marginBottom: 14 }}>اطلاعات من</strong>
          <dl style={{
            margin: 0, display: 'grid',
            gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: 14,
          }}>
            {infoRows.map(([label, value]) => (
              <div key={label} style={{
                display: 'flex', flexDirection: 'column', gap: 4,
                background: 'var(--c-bg)', borderRadius: 14, padding: '12px 14px',
              }}>
                <dt style={{ fontSize: 12, color: 'var(--c-muted)' }}>{label}</dt>
                <dd style={{ margin: 0, fontSize: 14, fontWeight: 600 }}>{value}</dd>
              </div>
            ))}
          </dl>
        </section>
      )}

      {editOpen && (
        <EditDialog
          info={info}
          onClose={() => setEditOpen(false)}
          onSaved={async (res) => {
            setUser(res?.user ?? null);
            setEditOpen(false);
            await reload();
          }}
        />
      )}

      {cancelFor && (
        <CancelDialog
          appointment={cancelFor}
          onClose={() => setCancelFor(null)}
          onDone={async () => {
            setCancelFor(null);
            await reload();
            setTab('canceled');
          }}
        />
      )}
    </div>
  );
}

/** ویرایش اطلاعات بیمار */
function EditDialog({ info, onClose, onSaved }) {
  const [form, setForm] = useState({
    first_name: info.first_name ?? '',
    last_name: info.last_name ?? '',
    national_code: info.national_code ?? '',
    city: info.city ?? '',
  });
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');

  const set = (k) => (e) => { setForm((f) => ({ ...f, [k]: e.target.value })); setError(''); };

  const save = async () => {
    setBusy(true);
    setError('');
    try {
      const res = await api.updateProfile({
        ...form,
        national_code: toEnDigits(form.national_code).trim(),
      });
      onSaved(res);
    } catch (err) {
      setError(err?.message ?? 'ذخیره اطلاعات انجام نشد.');
    } finally {
      setBusy(false);
    }
  };

  const fields = [
    ['نام', 'first_name', 'rtl'],
    ['نام خانوادگی', 'last_name', 'rtl'],
    ['کد ملی', 'national_code', 'ltr'],
    ['شهر', 'city', 'rtl'],
  ];

  return (
    <div role="dialog" aria-modal="true" aria-label="ویرایش اطلاعات" className="na-sheet-overlay">
      <div onClick={() => !busy && onClose()} style={{ position: 'absolute', inset: 0 }} />
      <div className="na-sheet" style={{ maxWidth: 420 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 16 }}>
          <strong style={{ flex: '1 1 auto', fontSize: 16 }}>ویرایش اطلاعات</strong>
          <button type="button" aria-label="بستن" disabled={busy} onClick={onClose}
            style={{
              width: 40, height: 40, borderRadius: 12, border: '1px solid var(--c-line)',
              background: '#fff', color: 'var(--c-muted)', fontSize: 18, cursor: 'pointer',
            }}>×</button>
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
          {fields.map(([label, key, dir]) => (
            <label key={key} style={{ display: 'flex', flexDirection: 'column', gap: 7 }}>
              <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--c-ink-2)' }}>{label}</span>
              <span className="na-field">
                <input type="text" dir={dir} value={form[key]} onChange={set(key)} />
              </span>
            </label>
          ))}

          {error && (
            <span style={{
              fontSize: 12.5, fontWeight: 600, color: 'var(--c-danger)',
              background: 'var(--c-danger-soft)', borderRadius: 10,
              padding: '10px 12px', lineHeight: 1.9,
            }}>{error}</span>
          )}

          <button type="button" className="na-btn" disabled={busy} onClick={save}
            style={{ minHeight: 52, marginTop: 4 }}>
            {busy ? 'در حال ذخیره…' : 'ذخیره تغییرات'}
          </button>
        </div>
      </div>
    </div>
  );
}

/** لغو نوبت با انتخاب دلیل */
function CancelDialog({ appointment, onClose, onDone }) {
  const [reason, setReason] = useState('');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');

  const submit = async () => {
    if (!reason) return;
    setBusy(true);
    setError('');
    try {
      await api.cancelAppointment(appointment.code, reason);
      onDone();
    } catch (err) {
      setError(err?.message ?? 'لغو نوبت انجام نشد.');
    } finally {
      setBusy(false);
    }
  };

  return (
    <div role="dialog" aria-modal="true" aria-label="لغو نوبت" className="na-sheet-overlay">
      <div onClick={() => !busy && onClose()} style={{ position: 'absolute', inset: 0 }} />
      <div className="na-sheet" style={{ maxWidth: 420 }}>
        <strong style={{ display: 'block', fontSize: 16, marginBottom: 8 }}>لغو نوبت</strong>

        <p style={{ margin: '0 0 14px', fontSize: 13, lineHeight: 1.9, color: 'var(--c-muted)' }}>
          {appointment.service} — {appointment.month} {fa(appointment.day ?? '')}
          {appointment.time ? ` ساعت ${fa(appointment.time)}` : ''}
        </p>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 16 }}>
          {REASONS.map((r) => (
            <button key={r} type="button" onClick={() => setReason(r)}
              style={{
                minHeight: 48, borderRadius: 13, fontSize: 14, fontWeight: 600,
                cursor: 'pointer', fontFamily: 'inherit', textAlign: 'right', padding: '0 14px',
                ...(reason === r
                  ? { background: 'var(--c-brand-soft)', border: '1px solid var(--c-brand)', color: 'var(--c-brand)' }
                  : { background: 'var(--c-bg)', border: '1px solid var(--c-line)', color: 'var(--c-ink-2)' }),
              }}>{r}</button>
          ))}
        </div>

        {error && (
          <p style={{ margin: '0 0 12px', fontSize: 13, color: 'var(--c-danger)', lineHeight: 1.9 }}>
            {error}
          </p>
        )}

        <div style={{ display: 'flex', gap: 10 }}>
          <button type="button" onClick={onClose} disabled={busy}
            style={{
              flex: '1 1 0', minHeight: 50, borderRadius: 14,
              border: '1px solid var(--c-line-2)', background: '#fff',
              color: 'var(--c-brand)', fontSize: 14, fontWeight: 700, cursor: 'pointer',
            }}>انصراف</button>

          <button type="button" onClick={submit} disabled={!reason || busy}
            style={{
              flex: '1 1 0', minHeight: 50, borderRadius: 14, border: 0,
              fontSize: 14, fontWeight: 800, color: '#fff',
              ...(reason && !busy
                ? { background: 'var(--c-danger)', cursor: 'pointer' }
                : { background: '#d2bab6', cursor: 'not-allowed' }),
            }}>{busy ? 'در حال لغو…' : 'تأیید لغو'}</button>
        </div>
      </div>
    </div>
  );
}
