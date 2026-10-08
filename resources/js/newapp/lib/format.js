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
