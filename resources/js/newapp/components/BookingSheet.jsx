import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../lib/api';
import LoginPanel from './LoginPanel.jsx';
import { useSite } from '../lib/SiteContext.jsx';
import { fa } from '../lib/format';

/**
 * شیت انتخاب زمان نوبت.
 * روزها و ساعت های خالی را از API می گیرد و کاربر را به صفحه تایید می برد.
 */
/** تعداد روزهایی که در هر صفحه نمایش داده می شود */
const DAYS_PER_PAGE = 4;

/**
 * کلید جابجایی بین صفحه های روز.
 * چون صفحه راست به چپ است، «بعدی» فلش چپ و «قبلی» فلش راست دارد.
 */
function ArrowButton({ dir, disabled, onClick }) {
  const isNext = dir === 'next';

  return (
    <button
      type="button" onClick={onClick} disabled={disabled}
      aria-label={isNext ? 'روزهای بعد' : 'روزهای قبل'}
      style={{
        flex: '0 0 auto', width: 36, height: 36, borderRadius: 11,
        border: '1px solid var(--c-line)',
        background: disabled ? 'var(--c-bg)' : '#fff',
        color: disabled ? 'var(--c-line-2)' : 'var(--c-brand)',
        cursor: disabled ? 'not-allowed' : 'pointer',
        display: 'grid', placeItems: 'center', padding: 0,
      }}
    >
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"
        style={{ transform: isNext ? 'none' : 'rotate(180deg)' }}>
        <path d="M15 5l-7 7 7 7" stroke="currentColor" strokeWidth="2.2"
          strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    </button>
  );
}

/**
 * اسکلتون بارگذاری که چیدمان واقعی را تقلید می کند:
 * ردیف روزها بالا و شبکه ساعت ها پایین. به همراه پیام و چرخنده تا کاملاً
 * مشخص باشد که در حال دریافت اطلاعات هستیم.
 */
function DaysSkeleton() {
  return (
    <div aria-busy="true" aria-live="polite">
      <div style={{
        display: 'flex', alignItems: 'center', gap: 9, marginBottom: 16,
        fontSize: 13, color: 'var(--c-muted)', fontWeight: 600,
      }}>
        <span className="na-spinner" aria-hidden="true" />
        <span>در حال دریافت زمان‌های آزاد…</span>
      </div>

      {/* ردیف روزها، هم اندازه چیدمان واقعی: دو کلید و صفحه ای از روزها */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 16 }}>
        <div className="na-shimmer" style={{ width: 36, height: 36, borderRadius: 11, flex: '0 0 auto' }} />
        <div style={{
          flex: '1 1 auto', display: 'grid', gap: 8,
          gridTemplateColumns: `repeat(${DAYS_PER_PAGE}, 1fr)`,
        }}>
          {Array.from({ length: DAYS_PER_PAGE }).map((_, i) => (
            <div key={i} className="na-shimmer"
              style={{ height: 34, borderRadius: 999, '--na-delay': `${i * 60}ms` }} />
          ))}
        </div>
        <div className="na-shimmer" style={{ width: 36, height: 36, borderRadius: 11, flex: '0 0 auto' }} />
      </div>

      {/* شبکه ساعت ها */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(96px, 1fr))', gap: 8 }}>
        {Array.from({ length: 12 }).map((_, i) => (
          <div key={i} className="na-shimmer"
            style={{ height: 40, borderRadius: 12, '--na-delay': `${i * 45}ms` }} />
        ))}
      </div>

      {/* جای دکمه تایید */}
      <div className="na-shimmer" style={{ height: 46, borderRadius: 14, marginTop: 18 }} />

      <span style={{
        position: 'absolute', width: 1, height: 1, overflow: 'hidden',
        clip: 'rect(0 0 0 0)', whiteSpace: 'nowrap',
      }}>در حال بارگذاری زمان های نوبت</span>
    </div>
  );
}

/** اسکلتون مرحله های انتخاب (مطب/بخش/ناحیه) */
function StepSkeleton() {
  return (
    <div aria-busy="true" aria-live="polite">
      <div style={{
        display: 'flex', alignItems: 'center', gap: 9, marginBottom: 16,
        fontSize: 13, color: 'var(--c-muted)', fontWeight: 600,
      }}>
        <span className="na-spinner" aria-hidden="true" />
        <span>در حال دریافت گزینه‌ها…</span>
      </div>
      <div style={{ display: 'grid', gap: 8 }}>
        {Array.from({ length: 3 }).map((_, i) => (
          <div key={i} className="na-shimmer"
            style={{ height: 56, borderRadius: 14, '--na-delay': `${i * 70}ms` }} />
        ))}
      </div>
    </div>
  );
}

/**
 * فهرست گزینه های یک مرحله (مطب، بخش یا اپراتور).
 * انتخاب هر گزینه بلافاصله به مرحله بعد می رود.
 */
function OptionList({ hint, options, onPick }) {
  return (
    <div>
      {hint && (
        <p style={{ margin: '0 0 12px', fontSize: 13, lineHeight: 1.9, color: 'var(--c-muted)' }}>
          {hint}
        </p>
      )}
      <div style={{ display: 'grid', gap: 8 }}>
        {options.map((o) => (
          <button
            key={o.id} type="button" onClick={() => onPick(o)}
            style={{
              textAlign: 'right', minHeight: 56, padding: '13px 15px', borderRadius: 14,
              background: 'var(--c-bg)', border: '1px solid var(--c-line)',
              color: 'var(--c-ink)', cursor: 'pointer', lineHeight: 1.7,
            }}
          >
            <span style={{ display: 'block', fontSize: 14, fontWeight: 700 }}>{o.title}</span>
            {o.address && (
              <span style={{ display: 'block', fontSize: 12, color: 'var(--c-muted)', marginTop: 3 }}>
                {o.address}
              </span>
            )}
          </button>
        ))}
      </div>
    </div>
  );
}

/**
 * انتخاب ناحیه. بسته به تنظیمات، تک انتخابی یا چند انتخابی است.
 * در حالت چند انتخابی مدت ویزیت جمع زمان ناحیه های انتخاب شده خواهد بود.
 */
function SegmentPicker({ title, multiple, items, draft, onToggle, onSubmit }) {
  return (
    <div>
      <p style={{ margin: '0 0 12px', fontSize: 13, lineHeight: 1.9, color: 'var(--c-muted)' }}>
        {multiple
          ? 'می‌توانید چند مورد را انتخاب کنید:'
          : 'لطفا یک مورد را انتخاب کنید:'}
      </p>

      <div style={{ display: 'grid', gap: 8, marginBottom: 16 }}>
        {items.map((it) => {
          const on = draft.includes(it.id);
          return (
            <button
              key={it.id} type="button" onClick={() => onToggle(it.id)}
              aria-pressed={on}
              style={{
                display: 'flex', alignItems: 'center', gap: 10, textAlign: 'right',
                minHeight: 54, padding: '12px 15px', borderRadius: 14, cursor: 'pointer',
                background: on ? 'var(--c-brand-soft)' : 'var(--c-bg)',
                border: '1px solid ' + (on ? 'var(--c-brand)' : 'var(--c-line)'),
                color: on ? 'var(--c-brand)' : 'var(--c-ink-2)',
              }}
            >
              <span style={{
                flex: '0 0 auto', width: 18, height: 18,
                borderRadius: multiple ? 5 : '50%',
                border: '2px solid ' + (on ? 'var(--c-brand)' : 'var(--c-line-2)'),
                background: on ? 'var(--c-brand)' : '#fff',
                display: 'grid', placeItems: 'center', color: '#fff', fontSize: 11,
              }} aria-hidden="true">{on ? '✓' : ''}</span>

              <span style={{ flex: '1 1 auto', fontSize: 14, fontWeight: 700 }}>{it.title}</span>

              {it.time ? (
                <span style={{ flex: '0 0 auto', fontSize: 12, opacity: 0.8 }}>
                  {fa(it.time)} دقیقه
                </span>
              ) : null}
            </button>
          );
        })}
      </div>

      <div className="na-sheet__floating-footer">
        <button type="button" className="na-btn" disabled={!draft.length}
          style={{ width: '100%' }} onClick={onSubmit}>
          {draft.length ? 'ادامه' : 'یک مورد را انتخاب کنید'}
        </button>
      </div>
    </div>
  );
}
export default function BookingSheet({ doctorId, serviceId, onClose }) {
  const [state, setState] = useState({ loading: true, error: null, days: [] });
  const [dayIdx, setDayIdx] = useState(0);
  const [picked, setPicked] = useState(null);
  // ساعتی که کاربر روی آن کلیک کرده و منتظر تایید نهایی است
  const [confirming, setConfirming] = useState(null);
  const [bookError, setBookError] = useState(null);
  // وقتی خطای «نوبت آنلاین فعال دارید» رخ می دهد، لینک مشاهده همان نوبت اینجا نگه داشته می شود
  const [activeOnlineAppt, setActiveOnlineAppt] = useState(null);
  // وقتی کاربر وارد نشده باشد، فرم ورود در همین مودال نشان داده می شود
  const [needLogin, setNeedLogin] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  // اولین روزِ صفحه فعلی در نوار روزها
  const [pageStart, setPageStart] = useState(0);

  // اگر مودال فقط با شناسه خدمت باز شده (بدون پزشک مشخص)، ابتدا باید
  // پزشک انتخاب شود. تا وقتی مشخص نشده، بقیه مراحل رزرو شروع نمی شوند.
  const [doctorStep, setDoctorStep] = useState(
    doctorId ? { loading: false, error: null, doctors: null } : { loading: true, error: null, doctors: null },
  );
  const [resolvedDoctorId, setResolvedDoctorId] = useState(doctorId ?? null);

  // مرحله جاری انتخاب (مطب/بخش/نوع ویزیت/ناحیه/اپراتور) که سرور تعیین می کند
  const [step, setStep] = useState({ loading: true, error: null, name: null });
  // انتخاب های کاربر تا این لحظه
  const [choice, setChoice] = useState({
    place_id: null, service_id: serviceId ?? null, segment_ids: [], operator_id: null, visit_type: null,
  });
  // ناحیه های نیمه انتخاب شده در حالت چند انتخابی
  const [segDraft, setSegDraft] = useState([]);
  const nav = useNavigate();
  const { user, site, setUser } = useSite();
  const registrationIncomplete = user && (
    !user.first_name?.trim() || !user.last_name?.trim()
    || (site?.nationalCodeRequired && !user.national_code?.trim())
    || (site?.birthdayRequired && !user.birthday?.trim())
  );

  // اگر پزشک از قبل مشخص نبود، فهرست پزشکان ارائه دهنده این خدمت گرفته می شود
  useEffect(() => {
    if (doctorId || !serviceId) return;

    let cancelled = false;
    setDoctorStep({ loading: true, error: null, doctors: null });

    api.serviceDoctors(serviceId)
      .then((res) => {
        if (cancelled) return;
        const doctors = res?.doctors ?? [];

        if (doctors.length <= 1) {
          // فقط یک پزشک (یا هیچ): بدون پرسیدن، مستقیم رزرو شروع می شود
          setResolvedDoctorId(doctors[0]?.id ?? null);
          setDoctorStep({ loading: false, error: null, doctors: null });
        } else {
          setDoctorStep({ loading: false, error: null, doctors });
        }
      })
      .catch((err) => {
        if (!cancelled) setDoctorStep({ loading: false, error: err, doctors: null });
      });

    return () => { cancelled = true; };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [doctorId, serviceId]);

  /**
   * پرسیدن مرحله بعد از سرور.
   * سرور با توجه به انتخاب های فعلی می گوید چه چیزی باید پرسیده شود
   * و وقتی همه چیز مشخص شد، مرحله ready برمی گرداند.
   */
  const loadStep = useCallback((sel) => {
    if (!resolvedDoctorId) return Promise.resolve();

    setStep((p) => ({ ...p, loading: true, error: null }));
    setSegDraft([]);

    return api.bookingOptions(resolvedDoctorId, {
      place_id: sel.place_id,
      service_id: sel.service_id,
      segment_ids: sel.segment_ids,
      operator_id: sel.operator_id,
      visit_type: sel.visit_type,
    })
      .then((res) => {
        setStep({
          loading: false,
          error: null,
          name: res?.step ?? null,
          message: res?.message ?? null,
          places: res?.places ?? [],
          services: res?.services ?? [],
          segments: res?.segments ?? [],
          segmentTitle: res?.segment_title ?? null,
          multiple: !!res?.multiple,
          operators: res?.operators ?? [],
          description: res?.description ?? null,
          serviceTitle: res?.service_title ?? null,
          doctorName: res?.doctor_name ?? null,
        });

        // سرور شناسه هایی را که خودش تعیین کرده برمی گرداند
        if (res?.step === 'ready' || res?.step === 'online_ready') {
          setChoice((c) => ({
            ...c,
            place_id: res.place_id ?? c.place_id,
            service_id: res.service_id ?? c.service_id,
            operator_id: res.operator_id ?? c.operator_id,
          }));
        }
      })
      .catch((err) => setStep({ loading: false, error: err, name: null }));
  }, [resolvedDoctorId]);

  // مرحله های رزرو فقط پس از مشخص شدن پزشک شروع می شوند
  useEffect(() => {
    if (!resolvedDoctorId) return;
    setBookError(null);
    loadStep({ place_id: null, service_id: serviceId ?? null, segment_ids: [], operator_id: null, visit_type: null });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [loadStep, serviceId, resolvedDoctorId]);

  /** دریافت روزها و ساعت های آزاد؛ پس از پر شدن یک ساعت هم دوباره صدا زده می شود */
  const loadDays = useCallback((sel, opts = {}) => {
    const { keepDay = false } = opts;

    setState((prev) => ({ ...prev, loading: true, error: null }));
    setPicked(null);
    setConfirming(null);
    if (!keepDay) {
      setDayIdx(0);
      setPageStart(0);
    }

    return api.doctorDays(resolvedDoctorId, {
      place_id: sel.place_id,
      service_id: sel.service_id,
      segment_ids: sel.segment_ids,
    })
      .then((res) => {
        setState({
          loading: false,
          error: null,
          days: res?.days ?? [],
          // پیام سرور، مثلاً غیرفعال بودن موقت نوبت دهی
          message: res?.message ?? null,
          doctorName: res?.doctor_name ?? null,
          serviceTitle: res?.service_title ?? null,
          // شناسه هایی که برای ثبت نوبت لازم است
          context: {
            doctor_id: res?.doctor_id ?? resolvedDoctorId,
            place_id: res?.place_id ?? sel.place_id ?? null,
            service_id: res?.service_id ?? sel.service_id ?? null,
          },
        });
      })
      .catch((err) => {
        setState({ loading: false, error: err, days: [], message: null });
      });
  }, [resolvedDoctorId]);

  // به محض آماده شدن انتخاب ها، ساعت ها گرفته می شوند
  useEffect(() => {
    if (step.name === 'ready') loadDays(choice);
    // choice تنها هنگام ready تغییر می کند، پس وابستگی به step کافی است
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [step.name]);

  /** ثبت یک انتخاب و رفتن به مرحله بعد */
  const pick = (patch) => {
    const next = { ...choice, ...patch };
    setChoice(next);
    loadStep(next);
  };

  /** بازگشت از انتخاب زمان به مرحله انتخاب بخش */
  const backToServiceStep = () => {
    const next = {
      ...choice, service_id: null, visit_type: null, segment_ids: [], operator_id: null,
    };
    setChoice(next);
    loadStep(next);
  };

  useEffect(() => {
    const onKey = (e) => {
      if (e.key !== 'Escape') return;
      // ابتدا پنجره تایید بسته می شود، بعد خود مودال
      if (confirming) setConfirming(null);
      else onClose?.();
    };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose, confirming]);

  // اگر روز انتخاب شده بیرون از صفحه فعلی افتاد، به صفحه خودش برمی گردیم
  useEffect(() => {
    setPageStart((start) => (
      dayIdx < start || dayIdx >= start + DAYS_PER_PAGE
        ? Math.floor(dayIdx / DAYS_PER_PAGE) * DAYS_PER_PAGE
        : start
    ));
  }, [dayIdx]);

  const day = state.days[dayIdx];

  // روزهای صفحه فعلی، به همراه اندیس واقعی شان در کل فهرست
  const visibleDays = state.days
    .slice(pageStart, pageStart + DAYS_PER_PAGE)
    .map((d, i) => ({ day: d, index: pageStart + i }));

  // شروع آخرین صفحه، تا صفحه بندی همیشه روی مرز درست بایستد
  const lastPageStart = Math.max(
    0,
    Math.floor(Math.max(0, state.days.length - 1) / DAYS_PER_PAGE) * DAYS_PER_PAGE,
  );

  /**
   * ثبت نهایی نوبت پس از تایید بیمار.
   * در صورت موفقیت به صفحه جزئیات نوبت می رویم.
   */
  async function confirmBooking() {
    if (!confirming || submitting) return;

    // بدون ورود، فرم ورود در همین مودال باز می شود
    if (!user) {
      setBookError(null);
      setNeedLogin({ time: confirming.time, day: confirming.day });
      setConfirming(null);
      return;
    }
    if (registrationIncomplete) {
      setBookError(null);
      setNeedLogin({ registration: true, time: confirming.time, day: confirming.day });
      setConfirming(null);
      return;
    }

    const ctx = state.context ?? {};
    setSubmitting(true);
    setBookError(null);

    try {
      const res = await api.bookAppointment(ctx.doctor_id ?? resolvedDoctorId, {
        visit_type: 'in_person',
        start_time: confirming.time.start_time ?? confirming.time.id,
        place_id: ctx.place_id ?? choice.place_id ?? null,
        service_id: ctx.service_id ?? choice.service_id ?? null,
        segment_ids: choice.segment_ids,
      });

      nav(res?.redirect ?? `/appointment/${res?.tracking_code ?? ''}`);
    } catch (err) {
      setSubmitting(false);
      setBookError(err?.message ?? 'ثبت نوبت انجام نشد.');

      // اگر ساعت پر شده باشد، فهرست ساعت ها باید تازه شود
      if (err?.data?.refresh_times) {
        setConfirming(null);
        setPicked(null);
        loadDays(choice, { keepDay: true });
      }

      if (err?.data?.need_registration) {
        setBookError(null);
        if (err.data.user) setUser(err.data.user);
        setNeedLogin({ registration: true, registrationUser: err.data.user, time: confirming.time, day: confirming.day });
        setConfirming(null);
      } else if (err?.data?.need_login || err?.status === 401) {
        setBookError(null);
        setNeedLogin({ time: confirming.time, day: confirming.day });
        setConfirming(null);
      }
    }
  }

  /**
   * ثبت نهایی نوبت آنلاین پس از تایید بیمار.
   * برخلاف نوبت حضوری، ساعت مشخصی ندارد و بلافاصله به صفحه جزئیات می رود.
   */
  async function confirmOnlineBooking() {
    if (submitting) return;

    // بدون ورود، فرم ورود در همین مودال باز می شود
    if (!user) {
      setBookError(null);
      setNeedLogin({ online: true });
      return;
    }
    if (registrationIncomplete) {
      setBookError(null);
      setNeedLogin({ registration: true, online: true });
      return;
    }

    setSubmitting(true);
    setBookError(null);
    setActiveOnlineAppt(null);

    try {
      const res = await api.bookAppointment(resolvedDoctorId, {
        visit_type: 'online',
        place_id: choice.place_id ?? null,
        service_id: choice.service_id ?? null,
      });

      nav(res?.redirect ?? `/appointment/${res?.tracking_code ?? ''}`);
    } catch (err) {
      setSubmitting(false);
      setBookError(err?.message ?? 'ثبت نوبت انجام نشد.');

      // کاربر از قبل یک نوبت آنلاین فعال دارد: لینک مشاهده همان گفتگو یا جزئیات نوبت نشان داده می شود
      if (err?.data?.online_chat_id || err?.data?.appointment_code) {
        setActiveOnlineAppt({
          chatId: err.data.online_chat_id ?? null,
          code: err.data.appointment_code ?? null,
        });
      }

      if (err?.data?.need_registration) {
        setBookError(null);
        if (err.data.user) setUser(err.data.user);
        setNeedLogin({ registration: true, registrationUser: err.data.user, online: true });
      } else if (err?.data?.need_login || err?.status === 401) {
        setBookError(null);
        setNeedLogin({ online: true });
      }
    }
  }

  // عنوان شیت با مرحله جاری هماهنگ می شود
  const sheetTitle = needLogin ? (needLogin.registration ? 'تکمیل اطلاعات برای ثبت نوبت' : 'ورود برای ثبت نوبت')
    : !resolvedDoctorId ? 'انتخاب پزشک'
      : step.name === 'place' ? 'انتخاب مطب'
        : step.name === 'service' ? 'انتخاب بخش'
          : step.name === 'visit_type' ? 'نوع ویزیت'
            : step.name === 'segment' ? (step.segmentTitle || 'انتخاب ناحیه')
              : step.name === 'operator' ? 'انتخاب اپراتور'
                : step.name === 'online_ready' ? 'نوبت آنلاین'
                  : 'انتخاب زمان نوبت';
  return (
    <div role="dialog" aria-modal="true" aria-label="انتخاب زمان نوبت" className="na-sheet-overlay">
      <div onClick={onClose} style={{ position: 'absolute', inset: 0 }} />
      <div className="na-sheet">
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 18 }}>
          <strong style={{ flex: '1 1 auto', fontSize: 16 }}>{sheetTitle}</strong>
          <button type="button" aria-label="بستن" onClick={onClose} style={{
            width: 40, height: 40, borderRadius: 12, border: '1px solid var(--c-line)',
            background: '#fff', color: 'var(--c-muted)', fontSize: 18, cursor: 'pointer',
          }}>×</button>
        </div>

        {/* مهمان پس از انتخاب ساعت یا نوبت آنلاین: ورود در همین مودال، بدون رفتن به صفحه ورود */}
        {needLogin && (
          <>
            <div style={{
              background: 'var(--c-brand-soft)', border: '1px solid var(--c-line)',
              borderRadius: 14, padding: '11px 13px', marginBottom: 16,
              fontSize: 12.5, lineHeight: 1.9, color: 'var(--c-ink-2)',
            }}>
              {needLogin.online ? (
                <>نوبت انتخابی شما: <b>آنلاین</b></>
              ) : (
                <>
                  زمان انتخابی شما:{' '}
                  <b>{needLogin.day?.label ?? ''}</b>
                  {' · ساعت '}
                  <b>{needLogin.time?.label ?? ''}</b>
                </>
              )}
              <button type="button"
                onClick={() => { setNeedLogin(false); setPicked(null); }}
                style={{
                  border: 0, background: 'transparent', color: 'var(--c-brand)',
                  fontWeight: 700, fontSize: 12.5, padding: '0 6px',
                  cursor: 'pointer', fontFamily: 'inherit',
                }}>تغییر زمان</button>
            </div>

            <LoginPanel
              registrationOnly={!!needLogin.registration}
              registrationUser={needLogin.registrationUser}
              hint="برای ثبت این نوبت، شماره موبایل خود را وارد کنید."
              onDone={() => {
                // پس از ورود، همان انتخاب قبلی به مرحله تایید می رود
                const chosen = needLogin;
                setNeedLogin(false);
                // نوبت آنلاین: تاییدیه همان جا در مرحله online_ready نمایش داده می شود
                if (!chosen.online) setConfirming(chosen);
              }}
            />
          </>
        )}

        {/* مرحله انتخاب پزشک: فقط وقتی این خدمت چند پزشک دارد و هنوز مشخص نشده */}
        {!needLogin && !resolvedDoctorId && doctorStep.loading && <StepSkeleton />}

        {!needLogin && !resolvedDoctorId && doctorStep.error && (
          <p style={{ fontSize: 13, color: 'var(--c-danger)', lineHeight: 1.9 }}>
            {doctorStep.error.message}
          </p>
        )}

        {!needLogin && !resolvedDoctorId && !doctorStep.loading && !doctorStep.error && (
          doctorStep.doctors && doctorStep.doctors.length > 0 ? (
            <OptionList
              hint="این خدمت توسط چند پزشک ارائه می‌شود. پزشک مورد نظر خود را انتخاب کنید:"
              options={doctorStep.doctors.map((d) => ({
                id: d.id, title: d.name, address: d.specialty,
              }))}
              onPick={(o) => setResolvedDoctorId(o.id)}
            />
          ) : (
            <p style={{ fontSize: 13, color: 'var(--c-muted)', lineHeight: 1.9 }}>
              در حال حاضر پزشکی برای این خدمت ثبت نشده است.
            </p>
          )
        )}

        {/* مرحله های انتخاب: مطب، بخش، ناحیه و اپراتور */}
        {!needLogin && resolvedDoctorId && step.loading && <StepSkeleton />}

        {!needLogin && resolvedDoctorId && step.error && (
          <p style={{ fontSize: 13, color: 'var(--c-danger)', lineHeight: 1.9 }}>
            {step.error.message}
          </p>
        )}

        {!needLogin && resolvedDoctorId && !step.loading && !step.error && step.name === 'unavailable' && (
          <p style={{ fontSize: 13, color: 'var(--c-muted)', lineHeight: 1.9 }}>
            {step.message ?? 'در حال حاضر امکان دریافت نوبت وجود ندارد.'}
          </p>
        )}

        {!needLogin && resolvedDoctorId && !step.loading && step.name === 'place' && (
          <OptionList
            hint="این پزشک در چند مطب نوبت می‌پذیرد. مطب مورد نظر خود را انتخاب کنید:"
            options={step.places}
            onPick={(o) => pick({ place_id: o.id })}
          />
        )}

        {!needLogin && resolvedDoctorId && !step.loading && step.name === 'service' && (
          <OptionList
            hint="از کدام بخش می‌خواهید نوبت بگیرید؟"
            options={step.services}
            onPick={(o) => pick({ service_id: o.id })}
          />
        )}

        {!needLogin && resolvedDoctorId && !step.loading && step.name === 'operator' && (
          <OptionList
            hint="لطفا اپراتور مورد نظر خود را انتخاب کنید:"
            options={step.operators}
            onPick={(o) => pick({ operator_id: o.id })}
          />
        )}

        {/* نوع ویزیت: فقط وقتی هم حضوری و هم آنلاین برای این بخش فعال است */}
        {!needLogin && resolvedDoctorId && !step.loading && step.name === 'visit_type' && (
          <div>
            <p style={{ margin: '0 0 12px', fontSize: 13, lineHeight: 1.9, color: 'var(--c-muted)' }}>
              نوع نوبت مورد نظر خود را انتخاب کنید:
            </p>
            <div style={{ display: 'grid', gap: 8 }}>
              <button
                type="button" onClick={() => pick({ visit_type: 'in_person' })}
                style={{
                  textAlign: 'right', minHeight: 56, padding: '13px 15px', borderRadius: 14,
                  background: 'var(--c-bg)', border: '1px solid var(--c-line)',
                  color: 'var(--c-ink)', cursor: 'pointer', lineHeight: 1.7,
                }}
              >
                <span style={{ display: 'block', fontSize: 14, fontWeight: 700 }}>حضوری</span>
                <span style={{ display: 'block', fontSize: 12, color: 'var(--c-muted)', marginTop: 3 }}>
                  دریافت نوبت با تعیین روز و ساعت مشخص
                </span>
              </button>
              <button
                type="button" onClick={() => pick({ visit_type: 'online' })}
                style={{
                  textAlign: 'right', minHeight: 56, padding: '13px 15px', borderRadius: 14,
                  background: 'var(--c-bg)', border: '1px solid var(--c-line)',
                  color: 'var(--c-ink)', cursor: 'pointer', lineHeight: 1.7,
                }}
              >
                <span style={{ display: 'block', fontSize: 14, fontWeight: 700 }}>آنلاین</span>
                <span style={{ display: 'block', fontSize: 12, color: 'var(--c-muted)', marginTop: 3 }}>
                  گفت‌وگوی متنی با پزشک، بدون نیاز به حضور
                </span>
              </button>
            </div>
          </div>
        )}

        {/* نوبت آنلاین: بدون ساعت مشخص، همین جا تاییدیه نهایی گرفته می شود */}
        {!needLogin && resolvedDoctorId && !step.loading && step.name === 'online_ready' && (
          <div>
            <p style={{ margin: '0 0 16px', fontSize: 13, lineHeight: 1.9, color: 'var(--c-muted)' }}>
              از دریافت نوبت <b>آنلاین</b>
              {step.doctorName ? <> با <b>{step.doctorName}</b></> : null}
              {step.serviceTitle ? <> در بخش <b>{step.serviceTitle}</b></> : null}
              {' '}مطمئن هستید؟
            </p>
            {step.description && (
              <p style={{ margin: '0 0 16px', fontSize: 13, lineHeight: 1.9, color: 'var(--c-muted)' }}>
                {step.description}
              </p>
            )}

            {bookError && (
              <div style={{
                display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap',
                margin: '0 0 14px', fontSize: 12.5, lineHeight: 1.9, color: 'var(--c-danger)',
                background: 'rgba(220,38,38,.07)', border: '1px solid rgba(220,38,38,.18)',
                borderRadius: 12, padding: '9px 12px',
              }}>
                <span style={{ flex: '1 1 auto' }}>{bookError}</span>
                {activeOnlineAppt && (
                  <button
                    type="button"
                    onClick={() => {
                      onClose?.();
                      nav(activeOnlineAppt.chatId
                        ? `/chat/${activeOnlineAppt.chatId}`
                        : `/appointment/${activeOnlineAppt.code}`);
                    }}
                    style={{
                      flex: '0 0 auto', border: 0, background: 'transparent',
                      color: 'var(--c-brand)', fontWeight: 700, fontSize: 12.5,
                      cursor: 'pointer', fontFamily: 'inherit', padding: 0,
                    }}
                  >مشاهده ›</button>
                )}
              </div>
            )}

            <div style={{ display: 'flex', gap: 10 }}>
              <button
                type="button" className="na-btn" disabled={submitting}
                style={{ flex: '1 1 auto', justifyContent: 'center' }}
                onClick={() => {
                  setBookError(null);
                  if (!user) { setNeedLogin({ online: true }); return; }
                  confirmOnlineBooking();
                }}
              >
                {submitting && <span className="na-spinner"
                  style={{ borderTopColor: '#fff', borderColor: 'rgba(255,255,255,.4)', borderTopWidth: 2 }}
                  aria-hidden="true" />}
                {submitting ? 'در حال ثبت…' : 'بله، دریافت نوبت'}
              </button>

              <button
                type="button" className="na-btn na-btn--ghost" disabled={submitting}
                style={{ flex: '0 0 auto' }}
                onClick={onClose}
              >
                انصراف
              </button>
            </div>
          </div>
        )}

        {!needLogin && resolvedDoctorId && !step.loading && step.name === 'segment' && (
          <SegmentPicker
            title={step.segmentTitle}
            multiple={step.multiple}
            items={step.segments}
            draft={segDraft}
            onToggle={(id) => setSegDraft((d) => (
              step.multiple
                ? (d.includes(id) ? d.filter((x) => x !== id) : [...d, id])
                : [id]
            ))}
            onSubmit={() => segDraft.length && pick({ segment_ids: segDraft })}
          />
        )}

        {/* پس از مشخص شدن انتخاب ها، ساعت ها نمایش داده می شوند */}
        {!needLogin && step.name === 'ready' && (
          <div style={{ marginBottom: 14 }}>
            <button
              type="button" onClick={backToServiceStep}
              style={{
                display: 'inline-flex', alignItems: 'center', gap: 6, marginBottom: 10,
                border: 0, background: 'transparent', padding: 0,
                color: 'var(--c-brand)', fontSize: 12.5, fontWeight: 700, cursor: 'pointer',
                fontFamily: 'inherit',
              }}
            >
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 5l-7 7 7 7" stroke="currentColor" strokeWidth="2.2"
                  strokeLinecap="round" strokeLinejoin="round" />
              </svg>
              بازگشت به انتخاب بخش
            </button>
            <p style={{ margin: 0, fontSize: 13, fontWeight: 700, color: 'var(--c-ink-2)' }}>
              انتخاب زمان نوبت برای{' '}
              <span style={{ color: 'var(--c-brand)' }}>{state.serviceTitle ?? '...'}</span>
            </p>
          </div>
        )}

        {step.name === 'ready' && state.loading && <DaysSkeleton />}

        {step.name === 'ready' && state.error && (
          <p style={{ fontSize: 13, color: 'var(--c-danger)', lineHeight: 1.9 }}>
            {state.error.message}
          </p>
        )}

        {step.name === 'ready' && !state.loading && !state.error && state.days.length === 0 && (
          <p style={{ fontSize: 13, color: 'var(--c-muted)', lineHeight: 1.9 }}>
            {state.message ?? 'در حال حاضر زمان خالی برای این پزشک ثبت نشده است.'}
          </p>
        )}

        {!needLogin && step.name === 'ready' && !state.loading && state.days.length > 0 && (
          <>
            {/* روزها صفحه به صفحه نمایش داده می شوند؛ بدون اسکرول افقی */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 14 }}>
              <ArrowButton
                dir="prev" disabled={pageStart === 0}
                onClick={() => setPageStart(Math.max(0, pageStart - DAYS_PER_PAGE))}
              />

              <div style={{
                flex: '1 1 auto', display: 'grid', gap: 8,
                gridTemplateColumns: `repeat(${DAYS_PER_PAGE}, 1fr)`,
              }}>
                {visibleDays.map(({ day: d, index }) => (
                  <button
                    key={d.key ?? index} type="button"
                    onClick={() => { setDayIdx(index); setPicked(null); }}
                    className={'na-chip' + (index === dayIdx ? ' na-chip--on' : '')}
                    style={{
                      justifyContent: 'center', textAlign: 'center',
                      padding: '9px 6px', lineHeight: 1.5, minWidth: 0,
                    }}
                    title={d.label}
                  >
                    <span style={{
                      display: 'block', minWidth: 0,
                      overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                    }}>
                      {d.label}
                      {d.free_count != null && (
                        <span style={{ opacity: .75 }}>{' · '}{fa(d.free_count)}</span>
                      )}
                    </span>
                  </button>
                ))}

                {/* پر کردن جای خالی صفحه آخر تا چیدمان ثابت بماند */}
                {Array.from({ length: DAYS_PER_PAGE - visibleDays.length }).map((_, i) => (
                  <span key={`pad-${i}`} aria-hidden="true" />
                ))}
              </div>

              <ArrowButton
                dir="next" disabled={pageStart + DAYS_PER_PAGE >= state.days.length}
                onClick={() => setPageStart(
                  Math.min(lastPageStart, pageStart + DAYS_PER_PAGE),
                )}
              />
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(96px, 1fr))', gap: 8 }}>
              {(day?.times ?? []).map((t) => {
                const on = picked?.id === t.id;
                return (
                  <button
                    key={t.id} type="button" disabled={!t.free}
                    onClick={() => {
                      if (!t.free) return;
                      setPicked(t);
                      setBookError(null);
                      // مهمان: فرم ورود در همین مودال جای روزها و ساعت ها را می گیرد
                      if (!user) { setNeedLogin({ time: t, day }); return; }
                      setConfirming({ time: t, day });
                    }}
                    style={{
                      padding: '11px 8px', borderRadius: 12, fontSize: 13, fontWeight: 700,
                      cursor: t.free ? 'pointer' : 'not-allowed',
                      background: on ? 'var(--c-brand)' : t.free ? 'var(--c-bg)' : '#fff',
                      color: on ? '#fff' : t.free ? 'var(--c-ink-2)' : 'var(--c-muted-2)',
                      border: '1px solid ' + (on ? 'var(--c-brand)' : 'var(--c-line)'),
                      textDecoration: t.free ? 'none' : 'line-through',
                    }}
                  >{t.label}</button>
                );
              })}
              {(day?.times ?? []).length === 0 && (
                <p style={{ gridColumn: '1/-1', margin: 0, fontSize: 13, color: 'var(--c-muted)' }}>
                  برای این روز زمان خالی وجود ندارد.
                </p>
              )}
            </div>

            <button
              type="button" className="na-btn" disabled={!picked || submitting}
              style={{ width: '100%', marginTop: 18 }}
              onClick={() => {
                if (!picked || submitting) return;
                setBookError(null);
                if (!user) { setNeedLogin({ time: picked, day }); return; }
                setConfirming({ time: picked, day });
              }}
            >
              {picked ? `ادامه — ${picked.label}` : 'یک زمان را انتخاب کنید'}
            </button>
          </>
        )}
      </div>

      {confirming && (
        <ConfirmDialog
          doctorName={state.doctorName}
          day={confirming.day}
          time={confirming.time}
          error={bookError}
          submitting={submitting}
          onCancel={() => {
            if (submitting) return;
            setConfirming(null);
            setBookError(null);
          }}
          onConfirm={confirmBooking}
        />
      )}

    </div>
  );
}

/**
 * پنجره تایید نهایی.
 * پیش از ثبت، پزشک و روز و ساعت انتخابی به بیمار نشان داده می شود.
 */
function ConfirmDialog({ doctorName, serviceName, day, time, online, error, submitting, onCancel, onConfirm }) {
  return (
    <div
      role="alertdialog" aria-modal="true" aria-label="تایید ثبت نوبت"
      style={{
        // ثابت نسبت به صفحه تا در شیت بلند و اسکرول شده هم وسط دیده شود
        position: 'fixed', inset: 0, zIndex: 95, background: 'rgba(9,28,42,0.5)',
        backdropFilter: 'blur(2px)',
        display: 'grid', placeItems: 'center', padding: 16,
      }}
    >
      <div onClick={onCancel} style={{ position: 'absolute', inset: 0 }} />

      <div style={{
        position: 'relative', width: '100%', maxWidth: 380, background: '#fff',
        borderRadius: 20, padding: 22, boxShadow: '0 24px 60px -20px rgba(9,28,42,.55)',
        animation: 'naUp .18s ease',
      }}>
        <strong style={{ display: 'block', fontSize: 15, marginBottom: 12 }}>
          تایید ثبت نوبت
        </strong>

        <p style={{ margin: '0 0 14px', fontSize: 13.5, lineHeight: 2.1, color: 'var(--c-ink-2)' }}>
          {online ? (
            <>
              از دریافت نوبت <b>آنلاین</b>
              {doctorName ? <> با <b>{doctorName}</b></> : null}
              {serviceName ? <> در بخش <b>{serviceName}</b></> : null}
              {' '}مطمئن هستید؟
            </>
          ) : (
            <>
              آیا می‌خواهید
              {doctorName ? <> با <b>{doctorName}</b></> : null}
              {' '}در روز <b>{day?.label ?? ''}</b>
              {' '}ساعت <b>{time?.label ?? ''}</b> نوبت بگیرید؟
            </>
          )}
        </p>

        {error && (
          <p style={{
            margin: '0 0 14px', fontSize: 12.5, lineHeight: 1.9, color: 'var(--c-danger)',
            background: 'rgba(220,38,38,.07)', border: '1px solid rgba(220,38,38,.18)',
            borderRadius: 12, padding: '9px 12px',
          }}>{error}</p>
        )}

        <div style={{ display: 'flex', gap: 10 }}>
          <button
            type="button" className="na-btn" onClick={onConfirm} disabled={submitting}
            style={{ flex: '1 1 auto', justifyContent: 'center' }}
          >
            {submitting && <span className="na-spinner"
              style={{ borderTopColor: '#fff', borderColor: 'rgba(255,255,255,.4)', borderTopWidth: 2 }}
              aria-hidden="true" />}
            {submitting ? 'در حال ثبت…' : 'بله، نوبت را ثبت کن'}
          </button>

          <button
            type="button" className="na-btn na-btn--ghost" onClick={onCancel} disabled={submitting}
            style={{ flex: '0 0 auto' }}
          >
            انصراف
          </button>
        </div>
      </div>
    </div>
  );
}
