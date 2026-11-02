/** تبدیل ارقام لاتین به فارسی */
const FA = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

export function fa(input) {
  if (input === null || input === undefined) return '';
  return String(input).replace(/\d/g, (d) => FA[+d]);
}

/** جداکننده هزارگان + ارقام فارسی */
export function money(value) {
  if (value === null || value === undefined || value === '') return '';
  const n = Number(String(value).replace(/[^\d.-]/g, ''));
  if (Number.isNaN(n)) return fa(value);
  return fa(n.toLocaleString('en-US').replace(/,/g, '٬'));
}

/** فقط ارقام لاتین - برای ارسال به سرور */
export function toEnDigits(s) {
  return String(s ?? '')
    .replace(/[۰-۹]/g, (d) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d)))
    .replace(/[٠-٩]/g, (d) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(d)));
}

/** صحت سنجی کد ملی: ۱۰ رقم که رقم آخر آن رقم کنترل است (همان الگوریتم NationalCodeRule سرور) */
export function isValidNationalCode(code) {
  const s = toEnDigits(code).trim();
  // کدهای با ارقام یکسان از رقم کنترل عبور می کنند ولی صادر نمی شوند
  if (!/^\d{10}$/.test(s) || /^(\d)\1{9}$/.test(s)) return false;

  let sum = 0;
  for (let i = 0; i < 9; i++) sum += Number(s[i]) * (10 - i);
  const remainder = sum % 11;
  const check = Number(s[9]);

  return remainder < 2 ? check === remainder : check === 11 - remainder;
}
