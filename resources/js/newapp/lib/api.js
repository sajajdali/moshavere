/**
 * کلاینت API اپ جدید.
 * همه درخواست ها به مسیرهای /api/v1/tenant/* می روند که در ماژول Api تعریف شده اند.
 */

const BASE = '/api/v1/tenant';

// پس از ورود، نشست بازسازی می شود و توکن قبلی دیگر معتبر نیست؛
// توکن تازه ای که سرور برمی گرداند اینجا نگه داشته می شود.
let freshToken = null;

export function setCsrfToken(token) {
  if (token) {
    freshToken = token;
    document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', token);
  }
}

function csrf() {
  return freshToken
    ?? document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    ?? '';
}

async function handleResponse(res, path) {
  const text = await res.text();
  let data = null;
  try {
    data = text ? JSON.parse(text) : null;
  } catch {
    // پاسخ JSON نبود؛ معمولاً صفحه خطای HTML لاراول.
    // پیام بر اساس کد وضعیت داده می شود تا کاربر بداند مشکل از کجاست.
    throw new ApiError(res.status, httpMessage(res.status, path), null);
  }

  if (!res.ok) {
    throw new ApiError(res.status, data?.message || httpMessage(res.status, path), data);
  }
  return data;
}

async function request(path, { method = 'GET', body, signal } = {}) {
  const res = await fetch(BASE + path, {
    method,
    signal,
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(body ? { 'Content-Type': 'application/json' } : {}),
      ...(method !== 'GET' ? { 'X-CSRF-TOKEN': csrf() } : {}),
    },
    ...(body ? { body: JSON.stringify(body) } : {}),
  });

  return handleResponse(res, path);
}

/**
 * ارسال با FormData (برای بارگذاری فایل)؛ برخلاف request، Content-Type را
 * دستی تنظیم نمی کند تا مرورگر خودش boundary مالتی‌پارت را اضافه کند.
 *
 * از XMLHttpRequest به جای fetch استفاده می شود چون fetch راهی برای گزارش
 * پیشرفت آپلود (upload progress) ندارد، ولی onProgress برای نوار پیشرفت لازم است.
 */
function requestForm(path, formData, { signal, onProgress } = {}) {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', BASE + path);
    xhr.responseType = 'text';
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('X-CSRF-TOKEN', csrf());
    xhr.withCredentials = true;

    if (signal) {
      if (signal.aborted) { reject(new DOMException('Aborted', 'AbortError')); return; }
      signal.addEventListener('abort', () => xhr.abort());
    }

    xhr.upload.onprogress = (e) => {
      if (e.lengthComputable) onProgress?.(e.loaded / e.total);
    };

    xhr.onerror = () => reject(new ApiError(0, 'ارتباط با سرور برقرار نشد.', null));
    xhr.onabort = () => reject(new DOMException('Aborted', 'AbortError'));

    xhr.onload = () => {
      let data = null;
      try {
        data = xhr.responseText ? JSON.parse(xhr.responseText) : null;
      } catch {
        reject(new ApiError(xhr.status, httpMessage(xhr.status, path), null));
        return;
      }

      if (xhr.status < 200 || xhr.status >= 300) {
        reject(new ApiError(xhr.status, data?.message || httpMessage(xhr.status, path), data));
        return;
      }
      resolve(data);
    };

    xhr.send(formData);
  });
}

/** پیام قابل فهم بر اساس کد وضعیت */
function httpMessage(status, path) {
  if (status === 404) return 'این بخش در دسترس نیست.';
  if (status === 401 || status === 403) return 'برای این کار باید وارد حساب خود شوید.';
  if (status === 419) return 'نشست شما منقضی شده است. صفحه را تازه کنید.';
  if (status === 429) return 'تعداد درخواست ها زیاد بود. کمی بعد دوباره تلاش کنید.';
  if (status >= 500) return 'خطایی در سرور رخ داد. کمی بعد دوباره تلاش کنید.';
  if (import.meta.env?.DEV) return `پاسخ نامعتبر از ${path} (کد ${status})`;
  return 'ارتباط با سرور برقرار نشد.';
}

/**
 * ساخت رشته پرس و جو.
 * مقدارهای خالی حذف و آرایه ها به شکل key[]=v فرستاده می شوند تا
 * سمت PHP هم آرایه دریافت شود.
 */
function qs(params) {
  const q = new URLSearchParams();
  for (const [k, v] of Object.entries(params ?? {})) {
    if (v === null || v === undefined || v === '') continue;
    if (Array.isArray(v)) v.forEach((item) => q.append(k + '[]', item));
    else q.append(k, v);
  }
  return q.toString();
}

export class ApiError extends Error {
  constructor(status, message, data) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.data = data;
  }
}

export const api = {
  bootstrap: () => request('/bootstrap'),
  home: (params) => request('/home' + (params && Object.keys(params).length ? '?' + qs(params) : '')),
  aboutUs: () => request('/about-us'),
  contactUs: () => request('/contact-us'),
  sendContactMessage: (body) => request('/contact-us', { method: 'POST', body }),
  search: (params) => request('/search?' + new URLSearchParams(params)),
  doctor: (id) => request(`/doctor/${id}`),
  // پزشکان ارائه دهنده یک خدمت؛ برای شروع رزرو فقط با شناسه خدمت
  serviceDoctors: (id) => request(`/service/${id}/doctors`),
  // گزینه های رزرو (مطب/بخش/ناحیه/اپراتور)
  bookingOptions: (id, params) => request(`/doctor/${id}/booking-options?` + qs(params)),
  doctorDays: (id, params) => request(`/doctor/${id}/days?` + qs(params)),
  // ثبت نوبت پس از تایید بیمار
  bookAppointment: (id, body) =>
    request(`/doctor/${id}/appointment`, { method: 'POST', body }),
  // نظرسنجی بیمار (بدون ورود، با کد پیگیری نوبت)
  feedback: (code) => request(`/feedback/${encodeURIComponent(code)}`),
  sendFeedback: (code, answers) =>
    request(`/feedback/${encodeURIComponent(code)}`, { method: 'POST', body: { answers } }),
  appointment: (code) => request(`/appointment/${encodeURIComponent(code)}`),
  cancelAppointment: (code, reason) =>
    request(`/appointment/${encodeURIComponent(code)}/cancel`, { method: 'POST', body: { reason } }),
  profile: () => request('/profile'),
  updateProfile: (body) => request('/profile', { method: 'POST', body }),
  chat: (id) => request(`/chat/${id}`),
  // پیام متنی و/یا یک فایل (عکس یا فایل عادی)؛ همیشه به شکل multipart فرستاده می شود
  sendChat: (id, { message, file, onProgress } = {}) => {
    const form = new FormData();
    if (message) form.append('message', message);
    if (file) form.append('file', file);
    return requestForm(`/chat/${id}/send`, form, { onProgress });
  },
  login: (mobile) => request('/auth/login', { method: 'POST', body: { mobile } })
    .then((res) => { setCsrfToken(res?.csrf_token); return res; }),
  verify: (mobile, code) => request('/auth/verify', { method: 'POST', body: { mobile, code } })
    .then((res) => { setCsrfToken(res?.csrf_token); return res; }),
  adminLogin: (mobile, password) => request('/auth/admin-login', {
    method: 'POST', body: { mobile, password },
  }).then((res) => { setCsrfToken(res?.csrf_token); return res; }),
  logout: () => request('/auth/logout', { method: 'POST' }),
  completeRegistration: (body) => request('/auth/register', { method: 'POST', body }),
};

export default api;
