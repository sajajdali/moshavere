import React, { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import api from '../lib/api';
import useAsync from '../lib/useAsync';
import { fa } from '../lib/format';
import { LoadingBlock } from '../components/States.jsx';
import '../styles/feedback.css';

function Question({ q, value, onChange, error }) {
  const set = (v) => onChange(q.id, v);

  return (
    <fieldset className={`fb-q${error ? ' fb-q--error' : ''}`}>
      <legend>
        {q.title}
        {q.required && <em aria-label="الزامی"> *</em>}
      </legend>

      {q.type === 'text' && (
        <input type="text" maxLength={255} value={value ?? ''} onChange={(e) => set(e.target.value)} />
      )}

      {q.type === 'textarea' && (
        <textarea rows={4} maxLength={2000} value={value ?? ''} onChange={(e) => set(e.target.value)} />
      )}

      {q.type === 'select' && (
        <select value={value ?? ''} onChange={(e) => set(e.target.value)}>
          <option value="">انتخاب کنید</option>
          {q.options.map((o) => <option key={o} value={o}>{o}</option>)}
        </select>
      )}

      {q.type === 'radio' && (
        <div className="fb-options">
          {q.options.map((o) => (
            <label key={o} className={value === o ? 'is-on' : ''}>
              <input type="radio" name={`q${q.id}`} checked={value === o} onChange={() => set(o)} />
              <span>{o}</span>
            </label>
          ))}
        </div>
      )}

      {q.type === 'checkbox' && (
        <div className="fb-options">
          {q.options.map((o) => {
            const list = Array.isArray(value) ? value : [];
            const on = list.includes(o);
            return (
              <label key={o} className={on ? 'is-on' : ''}>
                <input type="checkbox" checked={on}
                  onChange={() => set(on ? list.filter((x) => x !== o) : [...list, o])} />
                <span>{o}</span>
              </label>
            );
          })}
        </div>
      )}

      {q.type === 'rating' && (
        <div className="fb-rating" role="radiogroup" aria-label={q.title}>
          {[1, 2, 3, 4, 5].map((n) => (
            <button key={n} type="button" role="radio" aria-checked={Number(value) === n}
              className={Number(value) >= n ? 'is-on' : ''} onClick={() => set(n)} aria-label={`${fa(n)} از ۵`}>
              ★
            </button>
          ))}
          {value ? <span>{fa(value)} از ۵</span> : null}
        </div>
      )}

      {error && <p className="fb-error">{error}</p>}
    </fieldset>
  );
}

function Message({ title, text, done }) {
  return (
    <div className="fb-card fb-center">
      <span className={`fb-icon${done ? ' is-done' : ''}`}>{done ? '✓' : '!'}</span>
      <h1>{title}</h1>
      <p>{text}</p>
      <Link to="/" className="na-btn">بازگشت به صفحه اصلی</Link>
    </div>
  );
}

/** نظرسنجی بیمار؛ بدون ورود، با کد پیگیری نوبت */
export default function Feedback() {
  const { code } = useParams();
  const { data, error, loading } = useAsync(() => api.feedback(code), [code]);
  const [answers, setAnswers] = useState({});
  const [errors, setErrors] = useState({});
  const [sending, setSending] = useState(false);
  const [done, setDone] = useState(false);
  const [formError, setFormError] = useState('');

  if (loading) return <div className="na-wrap" style={{ padding: 24 }}><LoadingBlock rows={3} height={90} /></div>;

  if (error) {
    return (
      <div className="na-wrap fb-wrap">
        <Message title="نظرسنجی در دسترس نیست"
          text="این لینک نامعتبر است یا نظرسنجی برای این نوبت فعال نیست." />
      </div>
    );
  }

  if (done) {
    return (
      <div className="na-wrap fb-wrap">
        <Message done title="سپاس از شما" text="نظر شما با موفقیت ثبت شد و به ما در بهتر شدن خدمات کمک می‌کند." />
      </div>
    );
  }

  if (data.answered) {
    return (
      <div className="na-wrap fb-wrap">
        <Message done title="نظر شما قبلا ثبت شده است" text="برای این نوبت یک‌بار نظر ثبت شده و امکان ثبت دوباره وجود ندارد." />
      </div>
    );
  }

  const { form, appointment } = data;
  const change = (id, v) => {
    setAnswers((a) => ({ ...a, [id]: v }));
    setErrors((e) => ({ ...e, [id]: undefined }));
  };

  const submit = async (e) => {
    e.preventDefault();
    setFormError('');

    const missing = {};
    form.questions.forEach((q) => {
      const v = answers[q.id];
      if (q.required && (v === undefined || v === '' || (Array.isArray(v) && !v.length))) {
        missing[q.id] = 'این پرسش الزامی است.';
      }
    });
    if (Object.keys(missing).length) {
      setErrors(missing);
      setFormError('لطفا به پرسش‌های الزامی پاسخ دهید.');
      return;
    }

    setSending(true);
    try {
      await api.sendFeedback(code, answers);
      setDone(true);
    } catch (err) {
      if (err.status === 409) { setDone(true); return; }
      setErrors(err.data?.errors ?? {});
      setFormError(err.message || 'ثبت نظر انجام نشد. دوباره تلاش کنید.');
    } finally {
      setSending(false);
    }
  };

  return (
    <div className="na-wrap fb-wrap">
      <form className="fb-card" onSubmit={submit} noValidate>
        <h1>{form.title}</h1>
        <p className="fb-sub">
          {[appointment.doctor && `پزشک: ${appointment.doctor}`, appointment.service, appointment.date && fa(appointment.date)]
            .filter(Boolean).join(' · ')}
        </p>

        {form.questions.map((q) => (
          <Question key={q.id} q={q} value={answers[q.id]} onChange={change} error={errors[q.id]} />
        ))}

        {formError && <p className="fb-error fb-error--form">{formError}</p>}

        <button type="submit" className="na-btn fb-submit" disabled={sending}>
          {sending ? 'در حال ثبت…' : 'ثبت نظر'}
        </button>
      </form>
    </div>
  );
}
