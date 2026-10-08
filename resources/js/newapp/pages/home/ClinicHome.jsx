import React, { useMemo, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useSite } from '../../lib/SiteContext.jsx';
import { fa } from '../../lib/format';
import { Empty } from '../../components/States.jsx';
import DoctorTile, { nextLabel } from '../../components/ClinicDoctorTile.jsx';
import '../../styles/clinic.css';

/** معادل طرح: Clinic Booking Home.dc.html */

const CHIP_LIMIT = 6;

const NAV = [
  { href: '#booking', label: 'نوبت‌دهی' },
  { href: '#doctors', label: 'پزشکان' },
  { href: '#specialties', label: 'تخصص‌ها' },
  { href: '#departments', label: 'بخش‌ها' },
  { href: '#about', label: 'درباره ما' },
  { href: '#contact', label: 'تماس' },
];

function Header({ onBook }) {
  const { site, images, user, openLogin } = useSite();
  const [open, setOpen] = useState(false);
  const imageHeader = !!images?.hero;
  const title = site?.title || 'نوبت دهی';
  const logo = images?.clinic_logo || site?.logo;
  const cls = imageHeader ? 'cl-header cl-header--image' : 'cl-header';

  return (
    <header className={cls} style={imageHeader ? { backgroundImage: `linear-gradient(180deg, rgba(9,34,36,.86), rgba(12,48,50,.72)), url(${images.hero})` } : undefined}>
      <div className="cl-wrap cl-header__bar">
        <a href="#top" className="cl-brand">
          {logo
            ? <img src={logo} alt={title} className="cl-brand__logo" />
            : <span className="cl-brand__mark">{title.trim().charAt(0)}</span>}
          <span className="cl-brand__text">
            <strong>{title}</strong>
            {site?.subtitle && <span>{site.subtitle}</span>}
          </span>
        </a>

        <nav className="cl-nav na-show-desk">
          {NAV.map((n) => <a key={n.href} href={n.href}>{n.label}</a>)}
        </nav>

        <div className="cl-actions na-show-desk">
          {user
            ? <Link to="/profile" className="cl-btn-outline">{user.name || 'نوبت‌های من'}</Link>
            : <button type="button" className="cl-btn-outline" onClick={() => openLogin()}>ورود / ثبت‌نام</button>}
          <a href="#booking" className="cl-btn-solid">دریافت نوبت</a>
        </div>

        <button type="button" aria-label="منو" aria-expanded={open} className="cl-burger na-hide-desk"
          onClick={() => setOpen((v) => !v)}>
          <span />
        </button>
      </div>

      {imageHeader && (
        <div className="cl-wrap cl-header__hero">
          <h1>پزشک موردنظر خود را پیدا کنید و نوبت بگیرید</h1>
          <p>جستجو بر اساس نام پزشک، تخصص یا بخش — نزدیک‌ترین زمان آزاد را ببینید و نوبت را ثبت کنید.</p>
          <div className="cl-header__cta">
            <a href="#booking" className="cl-cta-primary">جستجوی پزشک</a>
            <a href="#doctors" className="cl-cta-ghost">مشاهده زمان‌های خالی</a>
          </div>
        </div>
      )}

      {open && (
        <nav className="cl-mobile-nav na-hide-desk">
          {NAV.map((n) => <a key={n.href} href={n.href} onClick={() => setOpen(false)}>{n.label}</a>)}
          <div className="cl-mobile-nav__row">
            {user
              ? <Link to="/profile" onClick={() => setOpen(false)} className="cl-btn-outline">نوبت‌های من</Link>
              : <button type="button" className="cl-btn-outline" onClick={() => { setOpen(false); openLogin(); }}>ورود / ثبت‌نام</button>}
            <a href="#booking" className="cl-btn-solid" onClick={() => setOpen(false)}>دریافت نوبت</a>
          </div>
        </nav>
      )}
    </header>
  );
}

export default function ClinicHome({ data, onBook, province, onProvinceChange }) {
  const { site } = useSite();
  const nav = useNavigate();

  const doctors = data.doctors ?? [];
  const specialities = data.specialities ?? [];
  const departments = data.departments ?? [];
  const provinces = data.provinces ?? [];
  const stats = data.stats ?? {};
  const hero = data.hero ?? {};

  const [query, setQuery] = useState('');
  const [specId, setSpecId] = useState('');
  const [deptId, setDeptId] = useState('');
  const [allChips, setAllChips] = useState(false);

  const activeSpecs = specialities.filter((s) => (s.doctors_count ?? 1) > 0);

  const filtered = useMemo(() => {
    const q = query.trim();
    return doctors.filter((d) => {
      if (specId && !(d.speciality_ids ?? []).includes(Number(specId))) return false;
      if (deptId && !(d.department_ids ?? []).includes(Number(deptId))) return false;
      if (q && ![d.name, d.specialty, d.department].some((v) => v && v.includes(q))) return false;
      return true;
    });
  }, [doctors, query, specId, deptId]);

  const suggestions = query.trim().length >= 2 ? filtered.slice(0, 3) : [];

  const featured = useMemo(
    () => doctors.filter((d) => d.next).sort((a, b) => (a.next.date < b.next.date ? -1 : 1)).slice(0, 3),
    [doctors],
  );

  const goDoctors = () => document.getElementById('doctors')?.scrollIntoView({ behavior: 'smooth' });
  const pick = (s, dep) => { setSpecId(s ?? ''); setDeptId(dep ?? ''); setQuery(''); setTimeout(goDoctors, 0); };

  const search = (e) => {
    e?.preventDefault();
    // نتیجه جستجو همین صفحه فیلتر می‌شود؛ جستجوی بدون نتیجه به صفحه جستجو می‌رود
    if (filtered.length === 0 && query.trim()) {
      nav('/search?' + new URLSearchParams({ query: query.trim() }).toString());
      return;
    }
    goDoctors();
  };

  const chips = [{ id: '', title: 'همه' }, ...activeSpecs];
  const shownChips = allChips ? chips : chips.slice(0, CHIP_LIMIT);
  const quick = activeSpecs.slice(0, 4);
  const soonest = (list) => list.filter((d) => d.next).sort((a, b) => (a.next.date < b.next.date ? -1 : 1))[0]?.next;

  const statItems = [
    ['پزشک متخصص', stats.doctors ?? doctors.length],
    ['بخش درمانی', stats.departments ?? departments.length],
    stats.monthly_appointments > 0 && ['نوبت ماه گذشته', stats.monthly_appointments],
    stats.rating && ['میانگین رضایت', stats.rating, true],
  ].filter(Boolean);

  const heroTitle = hero.title || 'پزشک موردنظر خود را پیدا کنید و نوبت بگیرید';
  const heroText = hero.description
    || 'جستجو بر اساس نام پزشک، تخصص یا بخش درمانی. نزدیک‌ترین زمان آزاد را ببینید و در سه مرحله نوبت خود را ثبت کنید.';

  return (
    <div id="top">
      <Header onBook={onBook} />

      <main>
        <section id="booking" className="cl-booking">
          <div className="cl-wrap cl-booking__in">
            <span className="cl-pill"><i />{hero.badge || 'نوبت‌دهی ۲۴ ساعته، بدون تماس تلفنی'}</span>
            <h1 className="cl-h1">{heroTitle}</h1>
            <p className="cl-lead">{heroText}</p>

            <form className="cl-search" onSubmit={search}>
              <div className="cl-search__row">
                <label className="cl-search__field">
                  <span className="cl-search__icon" />
                  <input type="search" value={query} onChange={(e) => setQuery(e.target.value)}
                    placeholder="نام پزشک، تخصص یا بخش…" aria-label="جستجوی پزشک" />
                </label>
                <select value={specId} onChange={(e) => setSpecId(e.target.value)} aria-label="تخصص">
                  <option value="">همه تخصص‌ها</option>
                  {activeSpecs.map((s) => <option key={s.id} value={s.id}>{s.title}</option>)}
                </select>
                {provinces.length > 0 && (
                  <select value={province} onChange={(e) => onProvinceChange?.(e.target.value)} aria-label="شهر">
                    <option value="">همه شهرها</option>
                    {provinces.map((p) => <option key={p.id} value={p.id}>{p.title}</option>)}
                  </select>
                )}
                <button type="submit" className="cl-search__btn">جستجوی پزشک</button>
              </div>

              {suggestions.length > 0 && (
                <div className="cl-suggest">
                  {suggestions.map((d) => (
                    <a key={d.id} href="#doctors" onClick={(e) => { e.preventDefault(); goDoctors(); }}>
                      <span>
                        <strong>{d.name}</strong>
                        <span>{[d.specialty, d.department && `بخش ${d.department}`].filter(Boolean).join(' · ')}</span>
                      </span>
                      <em>{nextLabel(d.next)}</em>
                    </a>
                  ))}
                </div>
              )}
            </form>

            {quick.length > 0 && (
              <div className="cl-quick">
                <span>جستجوی سریع:</span>
                {quick.map((s) => (
                  <a key={s.id} href="#doctors" onClick={(e) => { e.preventDefault(); pick(s.id); }}>{s.title}</a>
                ))}
              </div>
            )}

            <dl className="cl-stats">
              {statItems.map(([label, value, accent]) => (
                <div key={label}>
                  <dt>{label}</dt>
                  <dd className={accent ? 'is-accent' : ''}>{fa(value)}</dd>
                </div>
              ))}
            </dl>
          </div>
        </section>

        {activeSpecs.length > 0 && (
          <section id="specialties" className="cl-wrap cl-section">
            <h2 className="cl-h2">تخصص‌های پرکاربرد</h2>
            <p className="cl-sub">یک تخصص را انتخاب کنید تا پزشکان و نزدیک‌ترین زمان‌های آزاد آن را ببینید.</p>
            <div className="cl-spec-grid">
              {activeSpecs.map((s) => {
                const list = doctors.filter((d) => (d.speciality_ids ?? []).includes(s.id));
                return (
                  <a key={s.id} href="#doctors" className="cl-spec" onClick={(e) => { e.preventDefault(); pick(s.id); }}>
                    <span className="cl-spec__mark">{s.title.trim().charAt(0)}</span>
                    <strong>{s.title}</strong>
                    <span className="cl-spec__meta">{fa(s.doctors_count ?? list.length)} پزشک · {nextLabel(soonest(list))}</span>
                  </a>
                );
              })}
            </div>
          </section>
        )}

        {featured.length > 0 && (
          <section className="cl-wrap cl-section">
            <div className="cl-section__head">
              <h2 className="cl-h2">نزدیک‌ترین نوبت‌ها</h2>
              <Link to="/search" className="cl-link">همه پزشکان</Link>
            </div>
            <p className="cl-sub">پزشکانی که سریع‌ترین زمان آزاد را دارند.</p>
            <div className="cl-grid cl-grid--feat">
              {featured.map((d) => <DoctorTile key={d.id} d={d} featured onBook={onBook} />)}
            </div>
          </section>
        )}

        <section id="doctors" className="cl-wrap cl-section" style={{ scrollMarginTop: 80 }}>
          <h2 className="cl-h2">پزشکان کلینیک</h2>
          <p className="cl-sub">
            {filtered.length === doctors.length
              ? `${fa(doctors.length)} پزشک در ${site?.title || 'کلینیک'}`
              : `${fa(filtered.length)} پزشک از ${fa(doctors.length)} پزشک`}
          </p>

          <div className="cl-chips">
            {shownChips.map((c) => (
              <button key={c.id} type="button" className={String(c.id) === String(specId) ? 'is-on' : ''}
                onClick={() => setSpecId(String(c.id))}>{c.title}</button>
            ))}
            {chips.length > CHIP_LIMIT && (
              <button type="button" className="cl-chips__more" onClick={() => setAllChips((v) => !v)}>
                {allChips ? 'کمتر' : `بیشتر (${fa(chips.length - CHIP_LIMIT)})`}
              </button>
            )}
            {deptId && (
              <button type="button" className="is-on" onClick={() => setDeptId('')}>
                بخش {departments.find((d) => String(d.id) === String(deptId))?.title} ×
              </button>
            )}
          </div>

          {filtered.length === 0
            ? (
              <div className="cl-empty">
                <strong>پزشکی با این جستجو پیدا نشد</strong>
                <span>عبارت دیگری را امتحان کنید یا فیلتر را پاک کنید.</span>
                <button type="button" className="cl-btn-fill" style={{ flex: '0 0 auto', padding: '0 20px' }}
                  onClick={() => { setQuery(''); setSpecId(''); setDeptId(''); }}>پاک کردن جستجو</button>
              </div>
            )
            : <div className="cl-grid">{filtered.map((d) => <DoctorTile key={d.id} d={d} onBook={onBook} />)}</div>}
          {doctors.length === 0 && <Empty title="پزشکی برای نمایش وجود ندارد" />}
        </section>

        {departments.length > 0 && (
          <section id="departments" className="cl-wrap cl-section">
            <h2 className="cl-h2">بخش‌های کلینیک</h2>
            <p className="cl-sub">هر بخش، تیم پزشکان و برنامه نوبت‌دهی مستقل خود را دارد.</p>
            <div className="cl-grid cl-grid--dep">
              {departments.map((dep) => {
                const list = doctors.filter((d) => (d.department_ids ?? []).includes(dep.id));
                return (
                  <article key={dep.id} className="cl-dep">
                    <div className="cl-dep__head">
                      {dep.image && <img src={dep.image} alt="" />}
                      <strong>بخش {dep.title}</strong>
                      <span>{fa(dep.doctors_count ?? list.length)} پزشک</span>
                    </div>
                    {dep.description && <p>{dep.description}</p>}
                    <span className="cl-dep__soon">نزدیک‌ترین نوبت بخش: {nextLabel(soonest(list))}</span>
                    <Link to={`/service/${dep.id}/${encodeURIComponent(dep.title.replace(/\s+/g, '-'))}`} className="cl-btn-line">
                      مشاهده پزشکان و نوبت‌ها
                    </Link>
                  </article>
                );
              })}
            </div>
          </section>
        )}

        <section id="about" className="cl-wrap cl-section">
          <h2 className="cl-h2">{data.about_title || 'فرآیند نوبت‌دهی در سه مرحله'}</h2>
          <p className="cl-sub">{data.about || 'از جستجو تا تأیید نوبت، کمتر از یک دقیقه.'}</p>
          <ol className="cl-steps">
            {[
              ['۱', 'پیدا کردن پزشک', 'با نام پزشک، تخصص یا بخش جستجو کنید و نتایج را با فیلتر تخصص محدود کنید.'],
              ['۲', 'انتخاب زمان', 'زمان‌های خالی پزشک را ببینید و نزدیک‌ترین ساعت مناسب خود را انتخاب کنید.'],
              ['۳', 'ثبت نوبت', 'شماره موبایل را وارد کنید؛ کد پیگیری و یادآور نوبت پیامک می‌شود.'],
            ].map(([n, t, d], i) => (
              <li key={n}>
                <span className={i === 2 ? 'is-accent' : ''}>{n}</span>
                <strong>{t}</strong>
                <em>{d}</em>
              </li>
            ))}
          </ol>
        </section>

        <section className="cl-wrap cl-section cl-section--last">
          <div className="cl-cta">
            <h2>هنوز پزشک خود را انتخاب نکرده‌اید؟</h2>
            <p>فهرست پزشکان و زمان‌های آزاد امروز را ببینید و نوبت خود را همین حالا ثبت کنید.</p>
            <Link to="/search">جستجوی پزشک</Link>
          </div>
        </section>
      </main>
    </div>
  );
}
