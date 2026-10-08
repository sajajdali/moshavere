import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import LoginPanel from './LoginPanel.jsx';
import { useSite } from '../lib/SiteContext.jsx';

/**
 * مودال سراسری ورود، با همان ظاهر مودال انتخاب زمان نوبت.
 * از هر جای اپ می توان با openLogin() آن را باز کرد؛ مثلا وقتی کاربر
 * مهمان روی «دریافت نوبت» یا لینک «ورود» در نوار بالا کلیک می کند.
 */
export default function LoginModal() {
  const { loginModal, closeLogin } = useSite();
  const nav = useNavigate();

  useEffect(() => {
    if (!loginModal) return undefined;

    const onKey = (e) => { if (e.key === 'Escape') closeLogin(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [loginModal, closeLogin]);

  if (!loginModal) return null;

  return (
    <div role="dialog" aria-modal="true" aria-label="ورود به حساب کاربری" className="na-sheet-overlay">
      <div onClick={closeLogin} style={{ position: 'absolute', inset: 0 }} />
      <div className="na-sheet" style={{ maxWidth: 440 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 18 }}>
          <strong style={{ flex: '1 1 auto', fontSize: 16 }}>ورود / ثبت‌نام</strong>
          <button type="button" aria-label="بستن" onClick={closeLogin} style={{
            width: 40, height: 40, borderRadius: 12, border: '1px solid var(--c-line)',
            background: '#fff', color: 'var(--c-muted)', fontSize: 18, cursor: 'pointer',
          }}>×</button>
        </div>

        <LoginPanel
          hint={loginModal.hint}
          onDone={(user) => {
            closeLogin();
            if (loginModal.next) nav(loginModal.next);
            loginModal.onDone?.(user);
          }}
        />
      </div>
    </div>
  );
}
