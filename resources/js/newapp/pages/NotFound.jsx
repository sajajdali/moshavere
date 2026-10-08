import React from 'react';
import { Link } from 'react-router-dom';

export default function NotFound() {
  return (
    <div className="na-wrap" style={{ padding: '60px 16px', maxWidth: 520, textAlign: 'center' }}>
      <strong style={{ display: 'block', fontSize: 48, color: 'var(--c-brand)', marginBottom: 12 }}>۴۰۴</strong>
      <h1 style={{ fontSize: 20, fontWeight: 800, margin: '0 0 10px' }}>صفحه مورد نظر پیدا نشد</h1>
      <p style={{ fontSize: 14, color: 'var(--c-muted)', lineHeight: 2, margin: '0 0 22px' }}>
        ممکن است آدرس تغییر کرده باشد یا صفحه حذف شده باشد.
      </p>
      <Link to="/" className="na-btn">بازگشت به صفحه اصلی</Link>
    </div>
  );
}
