import React, { useEffect, useRef, useState } from 'react';
import { Link, Navigate, useParams } from 'react-router-dom';
import api from '../lib/api';
import useAsync from '../lib/useAsync';
import { useSite } from '../lib/SiteContext.jsx';
import { Loading, ErrorBox, Placeholder } from '../components/States.jsx';
import OpenStreetMap from '../components/OpenStreetMap.jsx';
import { fa, money } from '../lib/format';
import { directionLinks } from '../lib/workingHours';

const REASONS = [
  'برنامه‌ام تغییر کرده است',
  'می‌خواهم زمان دیگری بگیرم',
  'به پزشک دیگری مراجعه می‌کنم',
  'دیگر نیازی به ویزیت ندارم',
];

const NOTES = [
  'کارت ملی و دفترچه بیمه را همراه داشته باشید.',
  '۱۵ دقیقه پیش از ساعت نوبت در پذیرش حاضر شوید.',
  'آزمایش‌ها و مدارک پزشکی قبلی را به همراه بیاورید.',
];

/** نمایش مبلغ: اگر سرور رشته آماده داده باشد همان، وگرنه از عدد ساخته می شود */
function feeText(appt) {
  if (appt.fee_label) return fa(appt.fee_label);
  if (appt.fee) return money(appt.fee) + ' ریال';
  return null;
}

/** معادل طرح: Appointment Details.dc.html */
export default function AppointmentDetails() {
  const { code } = useParams();
  // وقتی ui بیماران غیر فعال است، برد کرامپ هم مانند هدر و فوتر نمایش داده نمی شود
  const { site } = useSite();
  const [cancelOpen, setCancelOpen] = useState(false);
  const [reason, setReason] = useState('');
  const [busy, setBusy] = useState(false);
  const [cancelError, setCancelError] = useState(null);
  const [copied, setCopied] = useState(false);
  const [mapPickerOpen, setMapPickerOpen] = useState(false);
  const copyTimer = useRef(null);

  const { data, error, loading, reload } = useAsync(() => api.appointment(code), [code]);

  useEffect(() => () => clearTimeout(copyTimer.current), []);

  if (loading) return <Loading label="در حال دریافت جزئیات نوبت" />;
  if (error) return <ErrorBox error={error} onRetry={reload} />;

  const appt = data?.appointment ?? {};
  const pending = appt.status === 'pending';
  const cancelled = appt.status === 'cancelled';
  const awaitingConfirmation = appt.status === 'awaiting_confirmation';
  const mapLinks = directionLinks(
    appt.lat && appt.lng ? { lat: appt.lat, lng: appt.lng } : null,
    appt.address,
  );

  // نوبت آنلاین: فقط پس از پرداخت مستقیم به گفتگو هدایت می شود؛
  // تا وقتی در انتظار پرداخت است، همین صفحه با گزینه پرداخت نمایش داده می شود
  if (appt.is_online && appt.online_chat_id && !cancelled && !pending && !awaitingConfirmation) {
    return <Navigate to={`/chat/${appt.online_chat_id}`} replace />;
  }
  const paid = !pending && !cancelled && !awaitingConfirmation;

  const doCancel = async () => {
    if (!reason) return;
    setBusy(true);
    setCancelError(null);
    try {
      await api.cancelAppointment(code, reason);
      setCancelOpen(false);
      setReason('');
      await reload();
      window.scrollTo(0, 0);
    } catch (err) {
      setCancelError(err);
    } finally {
      setBusy(false);
    }
  };

  const copyAddress = () => {
    if (!appt.address) return;
    try { navigator.clipboard?.writeText(appt.address); } catch { /* بی خطر */ }
    setCopied(true);
    clearTimeout(copyTimer.current);
    copyTimer.current = setTimeout(() => setCopied(false), 2000);
  };

  const statusLabel = appt.status_label
    ?? (pending ? 'در انتظار پرداخت' : cancelled ? 'لغو شده' : awaitingConfirmation ? 'در انتظار تایید' : 'نوبت قطعی');

  // نوار زمان نوبت: پرداخت شده پررنگ، در انتظار خط چین، لغو شده خاکستری
  const timeBox = cancelled
    ? { background: 'var(--c-bg)', color: 'var(--c-muted-2)', border: '1px solid var(--c-line)' }
    : pending || awaitingConfirmation
      ? { background: 'var(--c-bg)', color: 'var(--c-ink-2)', border: '1px dashed var(--c-line-2)' }
      : { background: 'linear-gradient(140deg, var(--c-brand), #2a7f74)', color: '#fff', border: 0 };

  const timeNote = cancelled
    ? 'این نوبت دیگر فعال نیست'
    : pending
      ? 'این زمان تا پرداخت برای شما نگه داشته شده است'
      : awaitingConfirmation
        ? 'این نوبت هنوز نهایی نشده و منتظر تایید مطب است'
        : null;

  const fee = feeText(appt);

  return (
    <div className="na-wrap" style={{
      padding: '16px 16px 40px', maxWidth: 860,
      display: 'flex', flexDirection: 'column', gap: 14,
    }}>
      {!site?.patientUiDisabled && (
        <nav aria-label="مسیر صفحه" style={{ fontSize: 12, color: 'var(--c-muted)' }}>
          <Link to="/">صفحه اصلی</Link>
          <span style={{ margin: '0 6px' }}>›</span>
          <Link to="/profile">نوبت‌های من</Link>
          <span style={{ margin: '0 6px' }}>›</span>
          <span>جزئیات نوبت</span>
        </nav>
      )}

      {/* هشدار در انتظار تایید */}
      {awaitingConfirmation && (
        <section style={{
          background: 'var(--c-gold-soft)', border: '1px solid #e6d7b8', borderRadius: 20,
          padding: 18, display: 'flex', flexDirection: 'column', gap: 10,
        }}>
          <span style={{
            alignSelf: 'flex-start', fontSize: 12, fontWeight: 800, color: '#7d6231',
            background: '#f0e3c8', borderRadius: 999, padding: '7px 12px',
          }}>در انتظار تایید</span>

          <h2 style={{ margin: 0, fontSize: 'clamp(17px, 4.4vw, 21px)', fontWeight: 800, lineHeight: 1.5 }}>
            نوبت شما هنوز نهایی نشده است
          </h2>

          <p style={{ margin: 0, fontSize: 13, lineHeight: 1.95, color: '#7d6231' }}>
            نوبت شما در حال بررسی و تایید توسط مطب است. لطفا صبر کنید؛ به محض تایید یا رد نوبت،
            نتیجه از طریق پیامک به شما اطلاع‌رسانی خواهد شد.
          </p>
        </section>
      )}

      {/* هشدار پرداخت */}
      {pending && (
        <section style={{
          background: 'var(--c-gold-soft)', border: '1px solid #e6d7b8', borderRadius: 20,
          padding: 18, display: 'flex', flexDirection: 'column', gap: 14,
        }}>
          <span style={{
            alignSelf: 'flex-start', fontSize: 12, fontWeight: 800, color: '#7d6231',
            background: '#f0e3c8', borderRadius: 999, padding: '7px 12px',
          }}>در انتظار پرداخت</span>

          <h2 style={{ margin: 0, fontSize: 'clamp(17px, 4.4vw, 21px)', fontWeight: 800, lineHeight: 1.5 }}>
            برای قطعی‌شدن نوبت، حق ویزیت را پرداخت کنید
          </h2>

          {appt.is_online && (
            <p style={{ margin: 0, fontSize: 13, lineHeight: 1.95, color: '#7d6231' }}>
              پس از پرداخت، مستقیم وارد صفحه گفتگو با پزشک می‌شوید.
            </p>
          )}

          {appt.pay_deadline && (
            <p style={{ margin: 0, fontSize: 13, lineHeight: 1.95, color: '#7d6231' }}>
              این زمان تا <strong>{fa(appt.pay_deadline)}</strong> برای شما نگه داشته می‌شود.
              در صورت نبود پرداخت، نوبت آزاد می‌شود.
            </p>
          )}

          {fee && (
            <div style={{
              display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 10,
              background: '#fff', border: '1px solid #e6d7b8', borderRadius: 15, padding: '13px 15px',
            }}>
              <span style={{ fontSize: 13, color: 'var(--c-muted)' }}>مبلغ قابل پرداخت</span>
              <strong style={{ fontSize: 18 }}>{fee}</strong>
            </div>
          )}

          {appt.payment_url && (
            <a href={appt.payment_url} style={{
              minHeight: 56, display: 'flex', alignItems: 'center', justifyContent: 'center',
              borderRadius: 15, background: '#8a6a3b', color: '#fff', fontSize: 16, fontWeight: 800,
            }}>اقدام به پرداخت</a>
          )}
        </section>
      )}

      {/* کارت اصلی نوبت */}
      <section className="na-card" style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap' }}>
          <span style={{
            fontSize: 12, fontWeight: 700, padding: '7px 12px', borderRadius: 999,
            ...(pending || awaitingConfirmation ? { background: '#f0e3c8', color: '#7d6231' }
              : cancelled ? { background: 'var(--c-danger-soft)', color: 'var(--c-danger)' }
                : { background: 'var(--c-ok-soft)', color: 'var(--c-accent)' }),
          }}>{statusLabel}</span>

          <span style={{ fontSize: 12, color: 'var(--c-muted)' }}>
            کد پیگیری: <strong style={{ color: 'var(--c-ink)' }} dir="ltr">{fa(appt.code ?? code)}</strong>
          </span>
        </div>

        <div style={{ display: 'flex', gap: 14, alignItems: 'flex-start' }}>
          {appt.doctor_avatar
            ? <img src={appt.doctor_avatar} alt="" width="70" height="70"
                style={{ flex: '0 0 auto', width: 70, height: 70, borderRadius: 19, objectFit: 'cover' }} />
            : <Placeholder label="عکس پزشک" style={{ flex: '0 0 auto', width: 70, height: 70, borderRadius: 19 }} />}

          <div style={{ flex: '1 1 auto', minWidth: 0 }}>
            <h1 style={{ margin: 0, fontSize: 'clamp(19px, 5vw, 25px)', fontWeight: 800, lineHeight: 1.4 }}>
              {appt.doctor}
            </h1>
            {appt.specialty && (
              <p style={{ margin: '6px 0 0', fontSize: 14, fontWeight: 600, color: 'var(--c-brand)' }}>
                {appt.specialty}
              </p>
            )}
            {appt.place && (
              <p style={{ margin: '4px 0 0', fontSize: 13, color: 'var(--c-muted)', lineHeight: 1.8 }}>
                {appt.place}
              </p>
            )}
          </div>
        </div>

        {/* زمان نوبت */}
        <div style={{
          borderRadius: 18, padding: 16, display: 'flex', flexWrap: 'wrap',
          gap: 12, alignItems: 'center', ...timeBox,
        }}>
          <span style={{ flex: '1 1 170px', minWidth: 0 }}>
            <span style={{ display: 'block', fontSize: 12, opacity: 0.85 }}>زمان نوبت</span>
            <strong style={{ display: 'block', fontSize: 20, marginTop: 4 }}>
              {fa(appt.date ?? '')}{appt.time ? ' · ساعت ' + fa(appt.time) : ''}
            </strong>
            {timeNote && <span style={{ display: 'block', fontSize: 12, marginTop: 6, opacity: 0.9 }}>{timeNote}</span>}
          </span>
        </div>

        {/* مشخصات */}
        <dl style={{
          margin: 0, display: 'grid',
          gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: 10,
        }}>
          {[
            ['بیمار', appt.patient],
            ['نوع ویزیت', appt.kind],
            ['خدمت', appt.service],
            [paid ? 'مبلغ پرداخت‌شده' : 'حق ویزیت', fee],
          ].filter(([, v]) => v).map(([k, v]) => (
            <div key={k} style={{
              background: 'var(--c-bg)', border: '1px solid var(--c-line)',
              borderRadius: 15, padding: '12px 14px',
            }}>
              <dt style={{ fontSize: 12, color: 'var(--c-muted)' }}>{k}</dt>
              <dd style={{ margin: '5px 0 0', fontSize: 14, fontWeight: 700, lineHeight: 1.7 }}>{v}</dd>
            </div>
          ))}
        </dl>

        {/* اقدامات */}
        {(pending || paid || awaitingConfirmation) && (
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 10 }}>
            {paid && appt.address && (
              <a href="#map" style={{
                flex: '1 1 180px', minHeight: 52, display: 'flex', alignItems: 'center',
                justifyContent: 'center', borderRadius: 15, background: 'var(--c-brand)',
                color: '#fff', fontSize: 15, fontWeight: 800,
              }}>مسیریابی به مطب</a>
            )}
            {appt.can_cancel && (
              <button type="button" onClick={() => { setCancelOpen(true); setCancelError(null); }}
                style={{
                  flex: '1 1 140px', minHeight: 52, borderRadius: 15,
                  border: '1px solid #e2c6c1', background: 'var(--c-danger-soft)',
                  color: 'var(--c-danger)', fontSize: 15, fontWeight: 800, cursor: 'pointer',
                }}>لغو نوبت</button>
            )}
          </div>
        )}

        {cancelled && (
          <div style={{
            background: 'var(--c-danger-soft)', border: '1px solid #e8d3ce',
            borderRadius: 16, padding: 16, display: 'flex', flexDirection: 'column', gap: 12,
          }}>
            <strong style={{ fontSize: 14, color: 'var(--c-danger)' }}>این نوبت لغو شد</strong>
            <span style={{ fontSize: 13, lineHeight: 1.9, color: '#8a5049' }}>
              نوبت شما لغو شد و می‌توانید زمان دیگری رزرو کنید.
            </span>
            <Link to="/" style={{
              alignSelf: 'flex-start', minHeight: 50, display: 'flex', alignItems: 'center',
              padding: '0 20px', borderRadius: 14, background: 'var(--c-brand)',
              color: '#fff', fontSize: 14, fontWeight: 800,
            }}>گرفتن نوبت جدید</Link>
          </div>
        )}
      </section>

      {/* نقشه */}
      {appt.address && (
        <section id="map" className="na-card" style={{ scrollMarginTop: 84 }}>
          <h2 style={{ margin: '0 0 4px', fontSize: 17, fontWeight: 800 }}>مسیریابی به مطب</h2>
          <p style={{ margin: '0 0 14px', fontSize: 13, lineHeight: 1.9, color: 'var(--c-muted)' }}>
            {appt.address}
          </p>

          {appt.lat && appt.lng && (
            <div style={{
              position: 'relative', borderRadius: 18, overflow: 'hidden',
              border: '1px solid var(--c-line-2)', background: 'var(--c-placeholder)',
              aspectRatio: '16 / 10',
            }}>
              <OpenStreetMap
                title="نقشه محل مطب" coordinates={{ lat: appt.lat, lng: appt.lng }}
                style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', border: 0 }}
              />
            </div>
          )}

          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 10, marginTop: 14 }}>
            {mapLinks.length > 0 && (
              <button type="button" onClick={() => setMapPickerOpen(true)} style={{
                flex: '1 1 190px', minHeight: 50, display: 'flex', alignItems: 'center',
                justifyContent: 'center', borderRadius: 14, background: 'var(--c-brand)',
                color: '#fff', fontSize: 14, fontWeight: 800, border: 0, cursor: 'pointer', fontFamily: 'inherit',
              }}>مسیریابی</button>
            )}

            <button type="button" onClick={copyAddress} className="na-btn na-btn--ghost"
              style={{ flex: '1 1 150px', minHeight: 50, justifyContent: 'center' }}>
              {copied ? 'آدرس کپی شد ✓' : 'کپی آدرس'}
            </button>

            {(appt.phones ?? []).slice(0, 1).map((p) => (
              <a key={p} href={'tel:' + p} style={{
                flex: '1 1 140px', minHeight: 50, display: 'flex', alignItems: 'center',
                justifyContent: 'center', borderRadius: 14, border: '1px solid var(--c-line-2)',
                fontSize: 14, fontWeight: 700, color: 'var(--c-brand)',
              }}>تماس با پذیرش</a>
            ))}
          </div>
        </section>
      )}

      {/* پیش از مراجعه */}
      {!cancelled && (
        <section className="na-card">
          <h2 style={{ margin: '0 0 12px', fontSize: 16, fontWeight: 800 }}>پیش از مراجعه</h2>
          <ul style={{ listStyle: 'none', margin: 0, padding: 0, display: 'flex', flexDirection: 'column', gap: 10 }}>
            {NOTES.map((n) => (
              <li key={n} style={{
                display: 'flex', gap: 10, alignItems: 'flex-start',
                fontSize: 13, lineHeight: 1.9, color: 'var(--c-ink-2)',
              }}>
                <span style={{
                  flex: '0 0 auto', width: 7, height: 7, marginTop: 8,
                  borderRadius: '50%', background: 'var(--c-accent-2)',
                }} />
                <span>{n}</span>
              </li>
            ))}
          </ul>
        </section>
      )}

      {/* شیت لغو نوبت */}
      {cancelOpen && (
        <div role="dialog" aria-modal="true" aria-label="لغو نوبت" className="na-sheet-overlay">
          <div onClick={() => !busy && setCancelOpen(false)} style={{ position: 'absolute', inset: 0 }} />
          <div className="na-sheet" style={{ maxWidth: 440 }}>
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: 10, marginBottom: 16 }}>
              <strong style={{ flex: '1 1 auto', fontSize: 17, lineHeight: 1.5 }}>
                لغو نوبت {fa(appt.date ?? '')}{appt.time ? ' · ' + fa(appt.time) : ''}
              </strong>
              <button type="button" aria-label="بستن" disabled={busy}
                onClick={() => setCancelOpen(false)}
                style={{
                  flex: '0 0 auto', width: 40, height: 40, borderRadius: 12,
                  border: '1px solid var(--c-line)', background: '#fff',
                  color: 'var(--c-muted)', fontSize: 18, cursor: 'pointer',
                }}>×</button>
            </div>

            <p style={{ margin: '0 0 14px', fontSize: 13, lineHeight: 1.95, color: 'var(--c-muted)' }}>
              با لغو نوبت، جایگاه شما آزاد می‌شود و این کار قابل بازگشت نیست.
            </p>

            <span style={{ display: 'block', fontSize: 13, fontWeight: 700, marginBottom: 8 }}>دلیل لغو</span>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 16 }}>
              {REASONS.map((r) => (
                <button key={r} type="button" onClick={() => setReason(r)}
                  style={{
                    textAlign: 'right', minHeight: 50, padding: '13px 14px', borderRadius: 14,
                    fontSize: 14, fontWeight: 600, cursor: 'pointer',
                    background: reason === r ? 'var(--c-brand-soft)' : 'var(--c-bg)',
                    border: '1px solid ' + (reason === r ? 'var(--c-brand)' : 'var(--c-line)'),
                    color: reason === r ? 'var(--c-brand)' : 'var(--c-ink-2)',
                  }}>{r}</button>
              ))}
            </div>

            {cancelError && (
              <p style={{ fontSize: 13, color: 'var(--c-danger)', marginBottom: 12, lineHeight: 1.9 }}>
                {cancelError.message}
              </p>
            )}

            <div style={{ display: 'flex', gap: 10 }}>
              <button type="button" onClick={() => setCancelOpen(false)} disabled={busy}
                style={{
                  flex: '1 1 0', minHeight: 52, borderRadius: 15, border: '1px solid var(--c-line-2)',
                  background: '#fff', fontSize: 15, fontWeight: 700,
                  color: 'var(--c-ink-2)', cursor: 'pointer',
                }}>انصراف</button>

              <button type="button" onClick={doCancel} disabled={!reason || busy}
                style={{
                  flex: '1 1 0', minHeight: 52, borderRadius: 15, border: 0,
                  fontSize: 15, fontWeight: 800,
                  ...(reason && !busy
                    ? { background: 'var(--c-danger)', color: '#fff', cursor: 'pointer' }
                    : { background: '#e6dcda', color: '#ad9c99', cursor: 'not-allowed' }),
                }}>{busy ? 'در حال لغو…' : 'تأیید لغو نوبت'}</button>
            </div>
          </div>
        </div>
      )}

      {/* انتخاب نقشه برای مسیریابی */}
      {mapPickerOpen && (
        <MapPickerDialog links={mapLinks} onClose={() => setMapPickerOpen(false)} />
      )}
    </div>
  );
}

/** آیکون هر نقشه، برای تشخیص سریع در فهرست انتخاب */
const MAP_ICONS = {
  google: (
    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
      <path fill="#4285F4" d="M12 2C7.6 2 4 5.6 4 10c0 5.4 6.8 11 7.1 11.3.5.4 1.3.4 1.8 0C13.2 21 20 15.4 20 10c0-4.4-3.6-8-8-8Z" />
      <circle cx="12" cy="10" r="3.2" fill="#fff" />
    </svg>
  ),
  neshan: (
    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
      <path fill="#00C08B" d="M12 2C7.6 2 4 5.6 4 10c0 5.4 6.8 11 7.1 11.3.5.4 1.3.4 1.8 0C13.2 21 20 15.4 20 10c0-4.4-3.6-8-8-8Z" />
      <circle cx="12" cy="10" r="3.2" fill="#fff" />
    </svg>
  ),
  balad: (
    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
      <path fill="#FF5A1F" d="M12 2C7.6 2 4 5.6 4 10c0 5.4 6.8 11 7.1 11.3.5.4 1.3.4 1.8 0C13.2 21 20 15.4 20 10c0-4.4-3.6-8-8-8Z" />
      <circle cx="12" cy="10" r="3.2" fill="#fff" />
    </svg>
  ),
};

/** انتخاب برنامه نقشه برای مسیریابی، هر کدام با آیکون خودش */
function MapPickerDialog({ links, onClose }) {
  return (
    <div role="dialog" aria-modal="true" aria-label="انتخاب نقشه برای مسیریابی" className="na-sheet-overlay">
      <div onClick={onClose} style={{ position: 'absolute', inset: 0 }} />
      <div className="na-sheet" style={{ maxWidth: 380 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 16 }}>
          <strong style={{ flex: '1 1 auto', fontSize: 16 }}>مسیریابی با کدام نقشه؟</strong>
          <button type="button" aria-label="بستن" onClick={onClose} style={{
            width: 40, height: 40, borderRadius: 12, border: '1px solid var(--c-line)',
            background: '#fff', color: 'var(--c-muted)', fontSize: 18, cursor: 'pointer',
          }}>×</button>
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          {links.map((link) => (
            <a key={link.key} href={link.url} target="_blank" rel="noopener noreferrer" onClick={onClose}
              style={{
                display: 'flex', alignItems: 'center', gap: 12, minHeight: 58,
                padding: '0 16px', borderRadius: 14, border: '1px solid var(--c-line)',
                background: 'var(--c-bg)', color: 'var(--c-ink)', fontSize: 15, fontWeight: 700,
              }}>
              <span style={{
                flex: '0 0 auto', width: 36, height: 36, borderRadius: 10, background: '#fff',
                display: 'grid', placeItems: 'center', border: '1px solid var(--c-line)',
              }}>{MAP_ICONS[link.key]}</span>
              <span style={{ flex: '1 1 auto' }}>{link.label}</span>
            </a>
          ))}
        </div>
      </div>
    </div>
  );
}
