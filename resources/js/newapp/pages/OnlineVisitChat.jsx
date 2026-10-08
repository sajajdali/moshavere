import React, { useEffect, useRef, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { Fancybox } from '@fancyapps/ui';
import '@fancyapps/ui/dist/fancybox.css';
import api from '../lib/api';
import { Loading, ErrorBox } from '../components/States.jsx';
import { fa } from '../lib/format';

/** معادل طرح: Online Visit Chat.dc.html */

/** فرمت های مجاز پیوست؛ باید با CHAT_ALLOWED_FILE_MIMES سمت سرور یکی باشد */
const ALLOWED_FILE_MIMES = [
  'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/heic', 'image/heif',
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/vnd.ms-excel',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'text/plain',
  'audio/webm', 'video/webm', 'audio/ogg', 'audio/mpeg', 'audio/mp4', 'audio/wav', 'audio/x-wav',
];

const MAX_FILE_BYTES = 10 * 1024 * 1024;
const COMPOSER_MIN_HEIGHT = 52;
const COMPOSER_MAX_HEIGHT = 160;

/** بررسی نوع و حجم فایل پیش از ارسال؛ در صورت مشکل پیام خطا برمی گرداند */
function validateAttachment(file) {
  if (file.size > MAX_FILE_BYTES) return 'حجم فایل نباید بیشتر از ۱۰ مگابایت باشد.';
  // فایل ضبط صدا (voice-*.webm) از خود مرورگر می آید و همیشه مجاز است
  if (file.type && !ALLOWED_FILE_MIMES.includes(file.type)) {
    return 'این نوع فایل پشتیبانی نمی‌شود. فقط عکس، سند یا فایل صوتی مجاز است.';
  }
  return null;
}

/** ثانیه ها را به شکل mm:ss فارسی نشان می دهد */
function clockLabel(totalSeconds) {
  const s = Math.max(0, Math.floor(totalSeconds));
  const m = Math.floor(s / 60);
  const r = s % 60;
  return fa(m) + ':' + fa(r < 10 ? '0' + r : r);
}

/** حباب یک پیام سیستمی، وسط چین */
function SystemBubble({ text }) {
  return (
    <div style={{ textAlign: 'center' }}>
      <span style={{
        fontSize: 12, lineHeight: 1.8, color: 'var(--c-muted)', background: '#fff',
        border: '1px solid var(--c-line)', borderRadius: 999, padding: '7px 14px',
        display: 'inline-block',
      }}>{text}</span>
    </div>
  );
}

/** حباب یک پیام معمولی: متن، عکس یا فایل */
function MessageBubble({ m }) {
  const mine = m.who === 'me';

  return (
    <div style={{ display: 'flex', animation: 'naFade .18s ease', justifyContent: mine ? 'flex-start' : 'flex-end' }}>
      <div style={{
        maxWidth: 'min(78%, 460px)',
        borderRadius: mine ? '16px 16px 16px 4px' : '16px 16px 4px 16px',
        padding: '11px 13px', fontSize: 14,
        background: mine ? '#fff' : 'var(--c-brand)',
        color: mine ? 'var(--c-ink)' : '#fff',
        border: mine ? '1px solid var(--c-line)' : 'none',
        boxShadow: 'var(--shadow-sm)',
      }}>
        {m.kind === 'image' && m.url && (
          <a
            href={m.url}
            data-fancybox="chat-images"
            data-caption={m.file_name ?? ''}
            aria-label="نمایش تصویر در اندازه بزرگ"
            style={{ display: 'block', cursor: 'zoom-in', marginBottom: m.text ? 8 : 0 }}
          >
            <img src={m.url} alt={m.file_name ?? ''}
              style={{ display: 'block', width: '100%', maxWidth: 220, borderRadius: 12 }} />
          </a>
        )}

        {m.kind === 'voice' && m.url && (
          <audio controls src={m.url} style={{ display: 'block', minWidth: 220, maxWidth: '100%', marginBottom: m.text ? 8 : 0 }} />
        )}

        {m.kind === 'file' && (
          <a href={m.url} target="_blank" rel="noreferrer" style={{
            display: 'flex', alignItems: 'center', gap: 10, minWidth: 180,
            color: mine ? 'var(--c-brand)' : '#fff', marginBottom: m.text ? 8 : 0,
          }}>
            <span style={{
              flex: '0 0 auto', width: 38, height: 38, borderRadius: 11,
              background: mine ? 'var(--c-brand-soft)' : 'rgba(255,255,255,.2)',
              display: 'grid', placeItems: 'center', fontSize: 11, fontWeight: 700,
            }}>{m.ext ?? 'فایل'}</span>
            <span style={{ flex: '1 1 auto', minWidth: 0 }}>
              <strong style={{
                display: 'block', fontSize: 13, whiteSpace: 'nowrap',
                overflow: 'hidden', textOverflow: 'ellipsis',
              }}>{m.file_name}</strong>
              {m.file_size && <span style={{ display: 'block', fontSize: 11, opacity: .8, marginTop: 2 }}>{m.file_size}</span>}
            </span>
          </a>
        )}

        {m.text && (
          <p style={{ margin: 0, fontSize: 14, lineHeight: 1.95, whiteSpace: 'pre-wrap', overflowWrap: 'anywhere' }}>{m.text}</p>
        )}

        {m.time != null && (
          <span style={{ display: 'block', marginTop: 6, fontSize: 10, opacity: .7, textAlign: 'left' }}>
            {new Date(m.time * 1000).toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' })}
          </span>
        )}
      </div>
    </div>
  );
}

export default function OnlineVisitChat() {
  const { id } = useParams();
  const [state, setState] = useState({ loading: true, error: null, data: null });
  const [draft, setDraft] = useState('');
  const [pendingFile, setPendingFile] = useState(null);
  const [attachError, setAttachError] = useState('');
  const [sending, setSending] = useState(false);
  // درصد پیشرفت آپلود فایل جاری؛ null یعنی در حال حاضر آپلودی در جریان نیست
  const [uploadProgress, setUploadProgress] = useState(null);
  const [remaining, setRemaining] = useState(null);
  // منوی پیوست: باز/بسته بودن فهرست سه گزینه (صدا/فایل/تصویر)
  const [attachOpen, setAttachOpen] = useState(false);
  // ضبط پیام صوتی
  const [recording, setRecording] = useState(false);
  const [recordSeconds, setRecordSeconds] = useState(0);
  const [recordError, setRecordError] = useState('');
  const listRef = useRef(null);
  const textareaRef = useRef(null);
  const fileInputRef = useRef(null);
  const imageInputRef = useRef(null);
  const attachMenuRef = useRef(null);
  const mediaRecorderRef = useRef(null);
  const recordedChunksRef = useRef([]);
  const recordStreamRef = useRef(null);
  const recordTimerRef = useRef(null);

  const load = React.useCallback(async () => {
    setState((s) => ({ ...s, loading: !s.data, error: null }));
    try {
      const data = await api.chat(id);
      setState({ loading: false, error: null, data });
      setRemaining(data?.remaining_seconds ?? null);
    } catch (err) {
      setState({ loading: false, error: err, data: null });
    }
  }, [id]);

  useEffect(() => { load(); }, [load]);

  // پیام های جدید را دوره ای می گیرد
  useEffect(() => {
    const t = setInterval(() => {
      api.chat(id).then((data) => {
        setState((s) => ({ ...s, data }));
        setRemaining(data?.remaining_seconds ?? null);
      }).catch(() => {});
    }, 15000);
    return () => clearInterval(t);
  }, [id]);

  // شمارش معکوس محلی، هر ثانیه یک واحد کم می شود
  useEffect(() => {
    if (remaining == null) return;
    const t = setInterval(() => {
      setRemaining((r) => (r != null && r > 0 ? r - 1 : r));
    }, 1000);
    return () => clearInterval(t);
  }, [remaining != null]);

  useEffect(() => {
    listRef.current?.scrollTo({ top: listRef.current.scrollHeight, behavior: 'smooth' });
  }, [state.data?.messages?.length]);

  // پیام ها با polling به DOM اضافه می شوند؛ binding با تغییر تعداد پیام ها تازه می شود.
  useEffect(() => {
    const selector = '[data-fancybox="chat-images"]';
    Fancybox.bind(selector, {
      groupAll: true,
      hideScrollbar: false,
    });

    return () => Fancybox.unbind(selector);
  }, [state.data?.messages?.length]);

  // بستن منوی پیوست با کلیک بیرون از آن
  useEffect(() => {
    if (!attachOpen) return undefined;
    const onClick = (e) => {
      if (attachMenuRef.current && !attachMenuRef.current.contains(e.target)) setAttachOpen(false);
    };
    document.addEventListener('mousedown', onClick);
    return () => document.removeEventListener('mousedown', onClick);
  }, [attachOpen]);

  // پاک سازی ضبط صدا در صورت خروج از صفحه
  useEffect(() => () => {
    clearInterval(recordTimerRef.current);
    recordStreamRef.current?.getTracks().forEach((t) => t.stop());
  }, []);

  const closed = !!state.data?.closed;

  const sendPayload = async ({ message, file }) => {
    setSending(true);
    if (file) setUploadProgress(0);
    try {
      const res = await api.sendChat(id, {
        message: message || undefined,
        file: file || undefined,
        onProgress: file ? (ratio) => setUploadProgress(Math.round(ratio * 100)) : undefined,
      });
      setState((s) => ({
        ...s,
        data: { ...s.data, messages: [...(s.data?.messages ?? []), res.message] },
      }));
    } catch (err) {
      setState((s) => ({ ...s, error: err }));
    } finally {
      setSending(false);
      setUploadProgress(null);
    }
  };

  // ارتفاع textarea با متن بزرگ می شود تا سقف مشخص، سپس خودش اسکرول می خورد
  useEffect(() => {
    const el = textareaRef.current;
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = Math.min(Math.max(el.scrollHeight, COMPOSER_MIN_HEIGHT), COMPOSER_MAX_HEIGHT) + 'px';
  }, [draft]);

  const send = async (e) => {
    e.preventDefault();
    const text = draft.trim();
    if ((!text && !pendingFile) || sending || closed) return;
    await sendPayload({ message: text, file: pendingFile });
    setDraft('');
    setPendingFile(null);
    if (fileInputRef.current) fileInputRef.current.value = '';
    if (imageInputRef.current) imageInputRef.current.value = '';
  };

  const pickFile = (e) => {
    const f = e.target.files?.[0];
    setAttachOpen(false);
    if (!f) return;

    const problem = validateAttachment(f);
    if (problem) {
      setAttachError(problem);
      if (fileInputRef.current) fileInputRef.current.value = '';
      if (imageInputRef.current) imageInputRef.current.value = '';
      return;
    }

    setAttachError('');
    setPendingFile(f);
  };

  /** شروع ضبط پیام صوتی با MediaRecorder مرورگر */
  const startRecording = async () => {
    setAttachOpen(false);
    setRecordError('');
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      recordStreamRef.current = stream;
      recordedChunksRef.current = [];

      const recorder = new MediaRecorder(stream);
      mediaRecorderRef.current = recorder;
      recorder.ondataavailable = (e) => { if (e.data.size > 0) recordedChunksRef.current.push(e.data); };
      recorder.start();

      setRecording(true);
      setRecordSeconds(0);
      recordTimerRef.current = setInterval(() => setRecordSeconds((s) => s + 1), 1000);
    } catch {
      setRecordError('دسترسی به میکروفون امکان‌پذیر نشد.');
    }
  };

  const stopRecordingStream = () => {
    clearInterval(recordTimerRef.current);
    mediaRecorderRef.current?.stop();
    recordStreamRef.current?.getTracks().forEach((t) => t.stop());
    setRecording(false);
  };

  const cancelRecording = () => {
    const recorder = mediaRecorderRef.current;
    if (recorder) recorder.ondataavailable = null;
    stopRecordingStream();
    setRecordSeconds(0);
  };

  /** پایان ضبط و ارسال بلافاصله پیام صوتی */
  const sendRecording = () => {
    const recorder = mediaRecorderRef.current;
    if (!recorder) return;

    recorder.onstop = async () => {
      const blob = new Blob(recordedChunksRef.current, { type: recorder.mimeType || 'audio/webm' });
      const file = new File([blob], `voice-${Date.now()}.webm`, { type: blob.type });
      setRecordSeconds(0);
      await sendPayload({ file });
    };
    stopRecordingStream();
  };

  if (state.loading) return <Loading label="در حال بارگذاری گفتگو" />;
  if (state.error && !state.data) return <ErrorBox error={state.error} onRetry={load} />;

  const chat = state.data ?? {};
  const messages = chat.messages ?? [];
  const canSend = !closed;

  return (
    <div dir="rtl" style={{ display: 'flex', flexDirection: 'column', height: '100vh', overflowX: 'hidden', background: 'var(--c-bg)' }}>
      {/* سربرگ گفتگو */}
      <header style={{
        flex: '0 0 auto', background: '#fff', borderBottom: '1px solid var(--c-line)',
      }}>
        <div style={{
          maxWidth: 820, margin: '0 auto', padding: '12px 16px',
          display: 'flex', alignItems: 'center', gap: 12,
        }}>
          <Link to="/profile" aria-label="بازگشت" style={{
            flex: '0 0 auto', width: 42, height: 42, borderRadius: 12, border: '1px solid var(--c-line)',
            display: 'grid', placeItems: 'center', fontSize: 18, color: 'var(--c-muted)',
          }}>›</Link>

          {chat.doctor?.avatar
            ? <img src={chat.doctor.avatar} alt=""
                style={{ width: 44, height: 44, borderRadius: '50%', objectFit: 'cover', flex: '0 0 auto' }} />
            : <span style={{
                width: 44, height: 44, borderRadius: '50%', flex: '0 0 auto',
                background: 'var(--c-brand-soft)', color: 'var(--c-brand)',
                display: 'grid', placeItems: 'center', fontWeight: 800,
              }}>{(chat.doctor?.name ?? 'د').trim().charAt(0)}</span>}

          <span style={{ flex: '1 1 auto', minWidth: 0, display: 'flex', flexDirection: 'column', lineHeight: 1.35 }}>
            <strong style={{
              fontSize: 15, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis',
            }}>{chat.doctor?.name ?? 'ویزیت آنلاین'}</strong>
            <span style={{ fontSize: 12, color: closed ? 'var(--c-muted)' : 'var(--c-accent-2)' }}>
              {closed ? (chat.status_label ?? 'پایان‌یافته') : 'آنلاین · در حال گفتگو'}
            </span>
          </span>

          {!closed && remaining != null && (
            <span style={{
              flex: '0 0 auto', fontSize: 12, fontWeight: 700, color: 'var(--c-accent)',
              background: 'var(--c-brand-soft)', borderRadius: 999, padding: '7px 11px', whiteSpace: 'nowrap',
            }}>{clockLabel(remaining)} باقی‌مانده</span>
          )}
          {closed && (
            <span style={{
              flex: '0 0 auto', fontSize: 12, fontWeight: 700, color: 'var(--c-muted)',
              background: 'var(--c-bg)', borderRadius: 999, padding: '7px 11px', whiteSpace: 'nowrap',
            }}>پایان‌یافته</span>
          )}
        </div>
      </header>

      {/* پیام ها */}
      <main ref={listRef} style={{ flex: '1 1 auto', overflowY: 'auto', padding: '14px 16px 20px' }}>
        <div style={{ maxWidth: 820, margin: '0 auto', display: 'flex', flexDirection: 'column', gap: 10 }}>
          {messages.map((m) => (
            m.who === 'system'
              ? <SystemBubble key={m.id} text={m.text} />
              : <MessageBubble key={m.id} m={m} />
          ))}

          {messages.length === 0 && (
            <p style={{ textAlign: 'center', fontSize: 13, color: 'var(--c-muted)' }}>
              هنوز پیامی رد و بدل نشده است.
            </p>
          )}
        </div>
      </main>

      {/* نوشتن پیام */}
      <footer style={{ flex: '0 0 auto', background: '#fff', borderTop: '1px solid var(--c-line)' }}>
        <div style={{ maxWidth: 820, margin: '0 auto', padding: '10px 16px 14px' }}>

          {closed ? (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12, padding: '6px 0' }}>
              <div style={{
                display: 'flex', alignItems: 'flex-start', gap: 10,
                background: 'var(--c-bg)', border: '1px solid var(--c-line)', borderRadius: 16, padding: 14,
              }}>
                <span style={{
                  flex: '0 0 auto', width: 34, height: 34, borderRadius: '50%',
                  background: 'var(--c-brand-soft)', color: 'var(--c-muted)',
                  display: 'grid', placeItems: 'center', fontSize: 15,
                }}>✓</span>
                <span style={{ flex: '1 1 auto' }}>
                  <strong style={{ display: 'block', fontSize: 14 }}>این گفتگو بسته شده است</strong>
                  <span style={{ display: 'block', fontSize: 12, lineHeight: 1.9, color: 'var(--c-muted)', marginTop: 4 }}>
                    امکان ارسال پیام جدید در این گفتگو وجود ندارد.
                  </span>
                </span>
              </div>
              <div style={{ display: 'flex', flexWrap: 'wrap', gap: 10 }}>
                {chat.tracking_code && (
                  <Link to={`/appointment/${chat.tracking_code}`} className="na-btn na-btn--ghost"
                    style={{ flex: '1 1 160px', justifyContent: 'center' }}>
                    مشاهده خلاصه ویزیت
                  </Link>
                )}
                <Link to="/" className="na-btn" style={{ flex: '1 1 160px', justifyContent: 'center' }}>
                  رزرو ویزیت جدید
                </Link>
              </div>
            </div>
          ) : (
            <form onSubmit={send} style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
              {recording ? (
                <div style={{
                  display: 'flex', alignItems: 'center', gap: 12,
                  background: 'var(--c-danger-soft)', border: '1px solid #e6c9c4',
                  borderRadius: 16, padding: '10px 14px',
                }}>
                  <span style={{
                    flex: '0 0 auto', width: 10, height: 10, borderRadius: '50%',
                    background: 'var(--c-danger)', animation: 'dcPulse 1s infinite',
                  }} />
                  <span style={{ flex: '1 1 auto', fontSize: 13, fontWeight: 700, color: 'var(--c-danger)' }}>
                    در حال ضبط پیام صوتی — {clockLabel(recordSeconds)}
                  </span>
                  <button type="button" onClick={cancelRecording} style={{
                    flex: '0 0 auto', minHeight: 40, padding: '0 14px', borderRadius: 11,
                    border: '1px solid #e6c9c4', background: '#fff', color: 'var(--c-danger)',
                    fontSize: 12.5, fontWeight: 700, cursor: 'pointer', fontFamily: 'inherit',
                  }}>لغو</button>
                  <button type="button" onClick={sendRecording} style={{
                    flex: '0 0 auto', minHeight: 40, padding: '0 16px', borderRadius: 11,
                    border: 0, background: 'var(--c-brand)', color: '#fff',
                    fontSize: 12.5, fontWeight: 700, cursor: 'pointer', fontFamily: 'inherit',
                  }}>ارسال</button>
                </div>
              ) : (
                <>
                  {pendingFile && (
                    <div style={{
                      display: 'flex', flexDirection: 'column', gap: 8, fontSize: 12.5,
                      background: 'var(--c-brand-soft)', border: '1px solid var(--c-line)',
                      borderRadius: 12, padding: '8px 12px', color: 'var(--c-ink-2)',
                    }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <span style={{ flex: '1 1 auto', minWidth: 0, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                          {pendingFile.name}
                        </span>
                        {uploadProgress != null ? (
                          <span style={{ flex: '0 0 auto', fontSize: 12, fontWeight: 700, color: 'var(--c-brand)' }}>
                            {fa(uploadProgress)}٪
                          </span>
                        ) : (
                          <button type="button" onClick={() => {
                            setPendingFile(null);
                            if (fileInputRef.current) fileInputRef.current.value = '';
                            if (imageInputRef.current) imageInputRef.current.value = '';
                          }} style={{
                            border: 0, background: 'transparent', color: 'var(--c-danger)',
                            fontWeight: 700, cursor: 'pointer', fontFamily: 'inherit',
                          }}>حذف</button>
                        )}
                      </div>

                      {uploadProgress != null && (
                        <div style={{ height: 6, borderRadius: 999, background: 'rgba(0,0,0,.08)', overflow: 'hidden' }}>
                          <div style={{
                            height: '100%', width: `${uploadProgress}%`, borderRadius: 999,
                            background: 'var(--c-brand)', transition: 'width .15s ease',
                          }} />
                        </div>
                      )}
                    </div>
                  )}

                  {attachError && (
                    <p style={{
                      margin: 0, fontSize: 12.5, lineHeight: 1.9, color: 'var(--c-danger)',
                      background: 'var(--c-danger-soft)', border: '1px solid rgba(220,38,38,.18)',
                      borderRadius: 12, padding: '9px 12px',
                    }}>{attachError}</p>
                  )}

                  {recordError && (
                    <p style={{ margin: 0, fontSize: 12, color: 'var(--c-danger)' }}>{recordError}</p>
                  )}

                  <div style={{ display: 'flex', alignItems: 'flex-end', gap: 8 }}>
                    {(draft.trim() || pendingFile) ? (
                      <button key="send" type="submit" aria-label="ارسال پیام" title="ارسال پیام"
                        disabled={sending}
                        style={{
                          flex: '0 0 auto', width: 52, height: 52, borderRadius: 16, border: 0,
                          cursor: sending ? 'not-allowed' : 'pointer',
                          color: '#fff', display: 'grid', placeItems: 'center',
                          background: sending ? 'var(--c-line-2)' : 'var(--c-brand)',
                          transition: 'background .15s ease',
                        }}>
                        {sending
                          ? <span className="na-spinner" style={{ borderTopColor: '#fff', borderColor: 'rgba(255,255,255,.4)', borderTopWidth: 2 }} aria-hidden="true" />
                          : (
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"
                              style={{ transform: 'scaleX(-1) rotate(-45deg)', animation: 'dcIconIn .2s ease' }}>
                              <path d="M4 20l16-8L4 4v6.5L16 12 4 13.5V20z" fill="currentColor" />
                            </svg>
                          )}
                      </button>
                    ) : (
                      <button key="mic" type="button" aria-label="ضبط پیام صوتی" title="ضبط پیام صوتی"
                        onClick={startRecording}
                        disabled={sending}
                        style={{
                          flex: '0 0 auto', width: 52, height: 52, borderRadius: 16, border: 0,
                          cursor: sending ? 'not-allowed' : 'pointer',
                          color: '#fff', display: 'grid', placeItems: 'center',
                          background: sending ? 'var(--c-line-2)' : 'var(--c-brand)',
                          transition: 'background .15s ease',
                        }}>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" style={{ animation: 'dcIconIn .2s ease' }}>
                          <rect x="9" y="2" width="6" height="12" rx="3" stroke="currentColor" strokeWidth="1.8" />
                          <path d="M5 11a7 7 0 0014 0M12 18v4" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                        </svg>
                      </button>
                    )}

                    <div style={{
                      flex: '1 1 auto', minWidth: 0, minHeight: COMPOSER_MIN_HEIGHT, maxHeight: COMPOSER_MAX_HEIGHT,
                      display: 'flex', alignItems: 'center',
                      background: 'var(--c-bg)', border: '1px solid var(--c-line)', borderRadius: 18,
                      padding: 0, boxSizing: 'border-box', overflow: 'hidden',
                    }}>
                      <textarea
                        ref={textareaRef}
                        value={draft} onChange={(e) => setDraft(e.target.value)}
                        onKeyDown={(e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(e); } }}
                        rows={1} placeholder="پیام خود را بنویسید…"
                        style={{
                          flex: '1 1 auto', minWidth: 0, resize: 'none', border: 0, background: 'transparent',
                          outline: 'none', fontSize: 15, lineHeight: 1.7, color: 'var(--c-ink)',
                          padding: '14px 12px', boxSizing: 'border-box', fontFamily: 'inherit',
                          maxHeight: COMPOSER_MAX_HEIGHT, overflowY: 'auto',
                        }}
                      />
                    </div>

                    <div ref={attachMenuRef} style={{ position: 'relative', flex: '0 0 auto' }}>
                      {attachOpen && (
                        <div style={{
                          position: 'absolute', bottom: '100%', insetInlineEnd: 0, marginBottom: 10,
                          background: '#fff', border: '1px solid var(--c-line)', borderRadius: 16,
                          boxShadow: '0 14px 34px -14px rgba(9,28,42,.35)', padding: 6,
                          display: 'flex', flexDirection: 'column', gap: 2, width: 190,
                          animation: 'naUp .16s ease',
                        }}>
                          <button type="button" onClick={startRecording} style={{
                            display: 'flex', alignItems: 'center', gap: 10, border: 0, background: 'transparent',
                            padding: '10px 12px', borderRadius: 11, fontSize: 13.5, fontWeight: 600,
                            color: 'var(--c-ink-2)', cursor: 'pointer', fontFamily: 'inherit', textAlign: 'right',
                          }}>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                              <rect x="9" y="2" width="6" height="12" rx="3" stroke="currentColor" strokeWidth="1.8" />
                              <path d="M5 11a7 7 0 0014 0M12 18v4" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                            </svg>
                            ضبط پیام صوتی
                          </button>

                          <button type="button" onClick={() => fileInputRef.current?.click()} style={{
                            display: 'flex', alignItems: 'center', gap: 10, border: 0, background: 'transparent',
                            padding: '10px 12px', borderRadius: 11, fontSize: 13.5, fontWeight: 600,
                            color: 'var(--c-ink-2)', cursor: 'pointer', fontFamily: 'inherit', textAlign: 'right',
                          }}>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                              <path d="M21.44 11.05l-9.19 9.19a5.5 5.5 0 01-7.78-7.78l9.19-9.19a3.5 3.5 0 014.95 4.95l-9.2 9.19a1.5 1.5 0 01-2.12-2.12l8.49-8.49"
                                stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                            ارسال فایل
                          </button>

                          <button type="button" onClick={() => imageInputRef.current?.click()} style={{
                            display: 'flex', alignItems: 'center', gap: 10, border: 0, background: 'transparent',
                            padding: '10px 12px', borderRadius: 11, fontSize: 13.5, fontWeight: 600,
                            color: 'var(--c-ink-2)', cursor: 'pointer', fontFamily: 'inherit', textAlign: 'right',
                          }}>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                              <rect x="3" y="4" width="18" height="16" rx="3" stroke="currentColor" strokeWidth="1.8" />
                              <circle cx="8.5" cy="9.5" r="1.6" stroke="currentColor" strokeWidth="1.8" />
                              <path d="M4 17l5-5 4 4 3-3 4 4" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                            ارسال تصویر
                          </button>
                        </div>
                      )}

                      <input ref={fileInputRef} type="file" onChange={pickFile} style={{ display: 'none' }} />
                      <input ref={imageInputRef} type="file" accept="image/*" onChange={pickFile} style={{ display: 'none' }} />

                      <button type="button" onClick={() => setAttachOpen((v) => !v)}
                        aria-label="افزودن پیوست" aria-expanded={attachOpen} title="افزودن پیوست" style={{
                          width: 52, height: 52, borderRadius: 16, border: '1px solid var(--c-line)',
                          background: attachOpen ? 'var(--c-brand-soft)' : '#fff', color: 'var(--c-brand)', cursor: 'pointer',
                          display: 'grid', placeItems: 'center', boxSizing: 'border-box',
                          transition: 'background .15s ease',
                        }}>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"
                          style={{
                            transition: 'transform .18s ease',
                            transform: attachOpen ? 'rotate(45deg)' : 'rotate(0deg)',
                          }}>
                          <path d="M21.44 11.05l-9.19 9.19a5.5 5.5 0 01-7.78-7.78l9.19-9.19a3.5 3.5 0 014.95 4.95l-9.2 9.19a1.5 1.5 0 01-2.12-2.12l8.49-8.49"
                            stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                        </svg>
                      </button>
                    </div>
                  </div>

                  <span style={{ fontSize: 11, color: 'var(--c-muted-2)', lineHeight: 1.8 }}>
                    حجم هر فایل تا ۱۰ مگابایت.
                  </span>
                </>
              )}
            </form>
          )}

          {state.error && (
            <p style={{
              margin: '10px 0 0', fontSize: 12.5, lineHeight: 1.9, color: 'var(--c-danger)',
              background: 'var(--c-danger-soft)', border: '1px solid rgba(220,38,38,.18)',
              borderRadius: 12, padding: '9px 12px',
            }}>{state.error.message}</p>
          )}
        </div>
      </footer>
    </div>
  );
}
