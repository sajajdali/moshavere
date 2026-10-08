import React, { useState } from 'react';
import { Link, NavLink, useLocation } from 'react-router-dom';
import { useSite } from '../lib/SiteContext.jsx';
import { fa, toEnDigits } from '../lib/format';

const navLinkStyle = ({ isActive }) => ({
  padding: '9px 12px',
  borderRadius: 9,
  color: isActive ? 'var(--c-brand)' : 'var(--c-ink-2)',
  background: isActive ? 'var(--c-brand-soft)' : 'transparent',
  fontSize: 14,
  fontWeight: isActive ? 700 : 500,
});

export function Header({ doctor, sections, onBook } = {}) {
  const { site, user, openLogin, images } = useSite();
  const [open, setOpen] = useState(false);
  const loc = useLocation();

  React.useEffect(() => { setOpen(false); }, [loc.pathname]);

  const links = [
    { to: '/', label: 'خانه' },
    { to: '/search', label: 'پزشکان' },
    { to: '/aboutus', label: 'درباره ما' },
    { to: '/contact-us', label: 'تماس با ما' },
    ...(user ? [{ to: '/profile', label: 'نوبت های من' }] : []),
  ];
  const title = doctor?.name || site?.title || 'نوبت دهی';
  const subtitle = doctor?.specialty || site?.subtitle;
  const logo = doctor ? (images?.doctor_avatar || doctor.avatar || site?.logo) : site?.logo;
  const sectionLinks = sections?.map((section) => section.booking
    ? <button key={section.id} type="button" className="sd-nav-link" onClick={() => { setOpen(false); onBook(); }}>{section.label}</button>
    : <a key={section.id} className="sd-nav-link" href={'#' + section.id} onClick={() => setOpen(false)}>{section.label}</a>);

  return (
    <header style={{
      position: 'sticky', top: 0, zIndex: 40,
      background: 'rgba(255,255,255,0.94)', backdropFilter: 'blur(10px)',
      borderBottom: '1px solid var(--c-line)',
    }}>
      <div className="na-wrap" style={{ padding: '12px 16px', display: 'flex', alignItems: 'center', gap: 12 }}>
        <Link to="/" style={{ display: 'flex', alignItems: 'center', gap: 10, flex: '1 1 auto', minWidth: 0 }}>
          {logo
            ? <img src={logo} alt={title} style={{ width: 40, height: 40, borderRadius: '50%', objectFit: 'cover', flex: '0 0 auto' }} />
            : <span style={{
                width: 40, height: 40, borderRadius: '50%', flex: '0 0 auto',
                background: 'linear-gradient(140deg, var(--c-brand), var(--c-accent-2))',
                display: 'grid', placeItems: 'center', color: '#fff', fontWeight: 800,
              }}>{title.trim().charAt(0)}</span>}
          <span style={{ display: 'flex', flexDirection: 'column', lineHeight: 1.25, minWidth: 0 }}>
            <strong style={{ fontSize: 15, color: 'var(--c-ink)', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
              {title}
            </strong>
            {subtitle && (
              <span style={{ fontSize: 11, color: 'var(--c-muted)', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                {subtitle}
              </span>
            )}
          </span>
        </Link>

        <nav className="na-show-desk" style={{ alignItems: 'center', gap: 4 }}>
          {sectionLinks ?? links.map((l) => (
            <NavLink key={l.to} to={l.to} end={l.to === '/'} style={navLinkStyle}>{l.label}</NavLink>
          ))}
        </nav>

        {user
          ? <Link to="/profile" className="na-btn na-btn--ghost na-show-desk" style={{ padding: '9px 16px' }}>{user.name || 'حساب من'}</Link>
          : (
            <button type="button" className={`na-btn na-show-desk${onBook ? ' na-btn--ghost' : ''}`} style={{ padding: '9px 18px' }}
              onClick={() => openLogin()}>{onBook ? 'ورود / ثبت‌نام' : 'ورود'}</button>
          )}
        {onBook && <button type="button" className="na-btn na-show-desk" onClick={onBook}>دریافت نوبت</button>}

        <button
          type="button" aria-label="منو" aria-expanded={open}
          className="na-hide-desk"
          onClick={() => setOpen((v) => !v)}
          style={{
            width: 40, height: 40, borderRadius: 12, border: '1px solid var(--c-line)',
            background: '#fff', color: 'var(--c-ink-2)', fontSize: 18, cursor: 'pointer',
          }}
        >☰</button>
      </div>

      {open && (
        <div className="na-hide-desk" style={{
          borderTop: '1px solid var(--c-line)', background: '#fff',
          padding: '10px 16px', display: 'flex', flexDirection: 'column', gap: 4,
          animation: 'naFade .18s ease',
        }}>
          {sectionLinks ?? links.map((l) => (
            <NavLink key={l.to} to={l.to} end={l.to === '/'} style={navLinkStyle}>{l.label}</NavLink>
          ))}
          {!user && (
            <button type="button" className="na-btn" style={{ marginTop: 6 }}
              onClick={() => openLogin()}>ورود / ثبت نام</button>
          )}
          {sections && user && <Link to="/profile" className="sd-nav-link">نوبت‌های من</Link>}
          {onBook && <button type="button" className="na-btn" onClick={() => { setOpen(false); onBook(); }}>دریافت نوبت</button>}
        </div>
      )}
    </header>
  );
}

export function Footer({ singleDoctor = false, onBook, contact } = {}) {
  const { site } = useSite();
  if (site?.hideFooter) return null;
  const phones = (Array.isArray(contact?.phone) ? contact.phone : [contact?.phone]).filter(Boolean);

  return (
    <footer style={{ background: '#fff', borderTop: '1px solid var(--c-line)', marginTop: 40 }}>
      <div style={{
        maxWidth: 1180, margin: '0 auto', padding: '28px 16px',
        display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: 24,
      }}>
        <div>
          <strong style={{ display: 'block', fontSize: 15, marginBottom: 8 }}>{site?.title}</strong>
          {site?.footerDescription && (
            <p style={{ margin: 0, fontSize: 13, lineHeight: 1.9, color: 'var(--c-muted)' }}>{site.footerDescription}</p>
          )}
        </div>

        <div>
          <strong style={{ display: 'block', fontSize: 14, marginBottom: 8 }}>دسترسی سریع</strong>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 6, fontSize: 13 }}>
            {singleDoctor ? <>
              <a href="#services">خدمات</a>
              <button type="button" className="sd-footer-book" onClick={onBook}>نوبت‌دهی</button>
            </> : <Link to="/search">پزشکان</Link>}
            <Link to="/aboutus">درباره ما</Link>
            <Link to="/contact-us">تماس با ما</Link>
            <Link to="/profile">نوبت های من</Link>
          </div>
        </div>

        {singleDoctor && phones.length > 0 && (
          <div>
            <strong style={{ display: 'block', fontSize: 14, marginBottom: 8 }}>تماس</strong>
            <div className="sd-contact-links">
              {phones.map((phone, i) => <a key={i} href={'tel:' + toEnDigits(phone).replace(/[^+\d]/g, '')}><bdi>{fa(phone)}</bdi></a>)}
              <a href="#contact">آدرس و ساعات کاری مطب</a>
            </div>
          </div>
        )}

        {(site?.instagram || site?.whatsapp || site?.telegram) && (
          <div>
            <strong style={{ display: 'block', fontSize: 14, marginBottom: 8 }}>ارتباط با ما</strong>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 6, fontSize: 13 }}>
              {site.instagram && <a href={site.instagram} target="_blank" rel="noreferrer">اینستاگرام</a>}
              {site.whatsapp && <a href={site.whatsapp} target="_blank" rel="noreferrer">واتس اپ</a>}
              {site.telegram && <a href={site.telegram} target="_blank" rel="noreferrer">تلگرام</a>}
            </div>
          </div>
        )}

        {site?.enamad && (
          <div>
            <strong style={{ display: 'block', fontSize: 14, marginBottom: 8 }}>نمادها</strong>
            <div className="na-footer-enamad" dangerouslySetInnerHTML={{ __html: site.enamad }} />
          </div>
        )}
      </div>
      <div style={{
        borderTop: '1px solid var(--c-line)', padding: '14px 16px',
        textAlign: 'center', fontSize: 12, color: '#8fa0a3',
      }}>
        © {site?.title} — تمامی حقوق محفوظ است.
      </div>
    </footer>
  );
}

export default function Layout({ children }) {
  const { mode, site } = useSite();
  const { pathname } = useLocation();
  if (mode === 'single_doctor' && pathname === '/') return children;

  // وقتی ui بیماران غیر فعال است، هدر و فوتر در صفحه جزئیات نوبت نمایش داده نمی شوند
  const bare = site?.patientUiDisabled && pathname.startsWith('/appointment/');
  // خانه کلینیک هدر مخصوص خودش را دارد
  const ownHeader = mode === 'clinic' && pathname === '/';

  return (
    <div style={{ minHeight: '100vh', overflowX: 'hidden', display: 'flex', flexDirection: 'column' }}>
      {!bare && !ownHeader && <Header />}
      <main style={{ flex: '1 0 auto' }}>{children}</main>
      {!bare && <Footer />}
    </div>
  );
}

