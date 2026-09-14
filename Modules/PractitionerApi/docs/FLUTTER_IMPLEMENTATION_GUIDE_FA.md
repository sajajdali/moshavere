# راهنمای جامع پیاده‌سازی اپلیکیشن Flutter پزشک و مشاور

> نسخه قرارداد: Practitioner API v1
> مخاطب: برنامه‌نویس Flutter، توسعه‌دهنده Native بخش تلفن و عامل هوش مصنوعی مجری پروژه
> وضعیت: مبنای پیاده‌سازی کامل نسخه اول اپلیکیشن

## 1. هدف و قاعده اصلی

این اپلیکیشن پنل کاری پزشک/مشاور برای ورود با OTP، ثبت Softphone، مشاهده نوبت‌ها، دریافت و مدیریت تماس، ثبت گزارش، نهایی‌کردن پرونده، تسویه و گزارش روزانه است.

آدرس دامنه عمداً در این سند درج نشده است. مقدار زیر باید از تنظیمات Build/Flavor یا Remote Configuration محیط دریافت شود و نباید داخل کد Featureها نوشته شود:

```text
TENANT_BASE_URL = آدرس پایه Tenant بدون اسلش انتهایی
API_BASE_PATH   = /api/practitioner/v1
API_BASE_URL    = {TENANT_BASE_URL}{API_BASE_PATH}
```

هر Tenant دیتابیس، تنظیمات، کاربران، سرور VoIP و توکن‌های خودش را دارد. توکن یک Tenant هرگز نباید برای Tenant دیگر استفاده شود. تغییر Tenant یعنی خروج کامل، Unregister حساب SIP، پاک‌کردن داده امن و ورود مجدد.

## 2. تصمیم فنی قطعی VoIP

راهکار فعلی **SIP Softphone بومی، مشابه Zoiper** است؛ WebRTC نیست. اپ باید با اطلاعاتی که API می‌دهد یک حساب SIP بسازد و روی PBX ثبت (`SIP REGISTER`) شود.

- Signaling: پروتکل SIP با `TLS`، `TCP` یا `UDP` مطابق فیلد `transport`.
- Registrar/Proxy host: فقط `server_host`.
- Registrar/Proxy port: فقط `server_port`.
- Authentication user: فیلد `username`.
- Authentication password: فیلد `password`.
- Extension/identity: فیلد `extension`.
- AOR پیشنهادی: `sip:{extension}@{server_host}`؛ اگر SDK برای User ID همان نام احراز هویت را لازم داشت، مقدار `username` را جداگانه به‌عنوان auth username بدهید.
- Display name: نام پزشک از پروفایل.

`server_address` مقدار خام تنظیم‌شده در پنل است و فقط برای نمایش/عیب‌یابی کنترل‌شده ارائه می‌شود. برای اتصال، host یا port را از آن استخراج نکنید؛ همیشه `server_host` و `server_port` منبع قطعی هستند.

استفاده از WebRTC فقط زمانی مجاز است که PBX در آینده صریحاً SIP-over-WebSocket امن (`WSS`) و media سازگار با WebRTC ارائه کند و قرارداد API نیز transport مربوطه را اضافه کند. در نسخه فعلی transport فقط یکی از `tls|tcp|udp` است؛ بنابراین یک SIP SDK بومی مناسب Android و iOS لازم است. انتخاب SDK باید این قابلیت‌ها را داشته باشد:

- SIP REGISTER و re-register پایدار؛
- تماس ورودی و خروجی، DTMF، mute، speaker و Bluetooth؛
- codecهای مورد پذیرش PBX؛ حداقل G.711 در صورت تأیید زیرساخت؛
- TLS و مدیریت certificate؛
- Android ConnectionService/Foreground Service و iOS CallKit؛
- رویدادهای registration/call/media/network؛
- امکان Unregister و حذف کامل account؛
- عدم ثبت password و SIP messageهای حساس در log نسخه Release.

Codec، STUN/TURN، outbound proxy، realm، ICE و سیاست NAT در API فعلی تعریف نشده‌اند؛ آن‌ها را حدس یا hardcode نکنید. SDK را با defaults استاندارد SIP اجرا کنید و اگر زیرساخت این موارد را لازم داشت، ابتدا باید قرارداد Backend توسعه یابد.

## 3. توالی اجباری شروع اپلیکیشن

### 3.1 اولین اجرا

1. یک `device_identifier` تصادفی و پایدار بسازید (UUID) و در Keychain/Keystore نگه دارید. از IMEI، advertising ID یا شناسه قابل تغییر سیستم استفاده نکنید.
2. تنظیمات محیط و `TENANT_BASE_URL` را بارگیری کنید.
3. `GET /status` را برای دسترس‌پذیری سرویس بزنید.
4. `GET /landing/content` را برای متن صفحه ورود، حداقل نسخه و لینک قوانین بگیرید.
5. اگر توکن ندارید صفحه ورود را باز کنید.

### 3.2 هر بار بازشدن اپ با توکن

این ترتیب الزامی است:

1. توکن و زمان انقضا را از Secure Storage بخوانید.
2. اگر منقضی است، نشست محلی را پاک و صفحه ورود را نمایش دهید.
3. `GET /me` را با Bearer token اجرا کنید.
4. اگر `401` گرفتید، نشست را پاک کنید؛ اگر موفق بود، پاسخ سرور منبع قطعی پروفایل و اطلاعات SIP است.
5. آبجکت `data.softphone` را اعتبارسنجی کنید.
6. اگر `configured=true` است، حساب SIP را بسازید/به‌روزرسانی کنید و REGISTER بزنید.
7. سپس Dashboard و داده‌های لازم را بارگیری کنید.
8. FCM token فعلی را در صورت ایجاد یا تغییر با `PATCH /devices/current` همگام کنید.

نباید اطلاعات SIP ذخیره‌شده از اجرای قبلی بدون `GET /me` استفاده شود؛ مدیر ممکن است host، port، transport یا credential پزشک را تغییر داده باشد.

### 3.3 بلافاصله پس از ورود موفق

پاسخ `POST /auth/otp/verify` در `data.practitioner.softphone` قرارداد کامل SIP را دارد. قبل از ورود کاربر به صفحه اصلی:

1. access token و expiry را امن ذخیره کنید.
2. پروفایل را در حافظه state قرار دهید.
3. Softphone را با همان اطلاعات configure کنید.
4. اگر کامل بود REGISTER را شروع کنید؛ UI می‌تواند Dashboard را نمایش دهد، اما وضعیت «در حال اتصال» باید مشخص باشد.
5. اگر ناقص بود، تلاش برای اتصال نکنید و `missing_fields` را به یک پیام قابل فهم تبدیل کنید.

### 3.4 تغییر اطلاعات SIP

از فیلدهای `server_host + server_port + transport + extension + username + password` یک fingerprint در حافظه بسازید. با تغییر آن:

1. تماس جاری را بی‌دلیل قطع نکنید؛ تغییر account را پس از پایان تماس انجام دهید.
2. account قبلی را Unregister و حذف کنید.
3. account جدید را بسازید و REGISTER کنید.
4. نتیجه registration را فقط در state محلی نگه دارید؛ `dashboard.voip.connected=null` به معنی قطع بودن نیست، بلکه Backend از اتصال زنده SDK خبر ندارد.

### 3.5 Resume، تغییر شبکه و خروج

- روی resume و تغییر Wi-Fi/Mobile Data، وضعیت registration را بررسی و در صورت نیاز با backoff اتصال مجدد انجام دهید.
- backoff پیشنهادی: 1، 2، 5، 10، 30 ثانیه با سقف 60 ثانیه و reset پس از اتصال موفق.
- در Logout ابتدا تماس فعال را مدیریت/تأیید کنید، SIP Unregister بزنید، سپس `POST /auth/logout` را اجرا و تمام secrets محلی را پاک کنید.
- در `401` نیز حتی اگر logout API قابل فراخوانی نیست، Unregister و پاک‌سازی محلی الزامی است.

## 4. امنیت اطلاعات تماس

`password` در پاسخ Login، `/me` و `/voip/config` محرمانه است.

- token و در صورت اجبار credential SIP فقط در Keychain/Keystore نگهداری شود؛ ترجیحاً password SIP پس از configureشدن SDK فقط در حافظه بماند.
- این داده‌ها در SharedPreferences، SQLite بدون رمزنگاری، analytics، crash report، log، clipboard، notification، screenshot تست یا backup نوشته نشوند.
- interceptor شبکه در Release نباید Authorization، OTP، mobile، password، گزارش پزشکی یا پاسخ پروفایل را log کند.
- cache بیمار، شرح شکایت، یادداشت و گزارش در Logout پاک شود.
- ارتباط API و SIP/TLS باید certificate معتبر داشته باشد. Pinning اختیاری است و بدون برنامه rotation گواهی اجرا نشود.
- نمایش کامل SIP password در UI ممنوع است؛ ویرایش با مقدار خالی یعنی حفظ رمز موجود.

## 5. قرارداد عمومی HTTP

Headerهای استاندارد:

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {ACCESS_TOKEN}   # فقط مسیرهای محافظت‌شده
```

پاسخ تکی معمولاً:

```json
{"data": {}}
```

پاسخ لیستی صفحه‌بندی‌شده:

```json
{"data": [], "links": {}, "meta": {}}
```

خطا:

```json
{"message": "متن قابل نمایش", "errors": {"field": ["خطای فیلد"]}}
```

کدها:

| Status | رفتار کلاینت |
|---:|---|
| 200/201/202 | موفق؛ `202` یعنی عملیات پذیرفته شده و ممکن است غیرهمزمان باشد |
| 204 | موفق بدون body |
| 401 | نشست نامعتبر؛ یک بار global logout، بدون حلقه retry |
| 403 | حساب/دسترسی اپ غیرفعال؛ پیام سرور و خروج کنترل‌شده |
| 404 | منبع در دسترس این پزشک نیست؛ وجود منبع را افشا نکنید |
| 409 | تضاد state، درخواست تکراری، زمان نامناسب یا VoIP ناقص؛ داده را refresh کنید |
| 422 | validation یا قانون کسب‌وکار؛ خطای فیلد/پیام را نمایش دهید |
| 423 | OTP قفل؛ شمارنده را از `Retry-After` بسازید |
| 429 | محدودیت درخواست؛ `Retry-After` را رعایت کنید |
| 503 | سرویس VoIP/Provider موقتاً در دسترس نیست؛ retry کنترل‌شده |

برای منطق UI از متن فارسی خطا parse نکنید. وضعیت HTTP، فیلد `errors` و خصوصاً `actions` دریافتی از appointment ملاک‌اند. پس از 409/422 مربوط به state، جزئیات نوبت را دوباره دریافت کنید.

Retry خودکار فقط برای GETهای امن و خطاهای شبکه مجاز است. mutationهای مالی، گزارش، complete/no-show و auto-call را کورکورانه retry نکنید. با double tap نیز از قفل محلی عملیات جلوگیری کنید.

### 5.1 مسیرهای عمومی راه‌اندازی

#### `GET /status`

بدون توکن و برای health check سرویس همان Tenant است. پاسخ موفق:

```json
{
  "data": {
    "service": "practitioner-api",
    "version": "1.0.0",
    "tenant": "tenant-identifier",
    "online_consultation_enabled": true
  }
}
```

- مقدار `service` شناسه ثابت سرویس و `version` نسخه قرارداد Backend است.
- مقدار `tenant` فقط برای تشخیص اشتباه محیط/پیکربندی استفاده شود و در UI عمومی نمایش داده نشود.
- اگر `online_consultation_enabled=false` بود، ادامه ورود و راه‌اندازی Softphone متوقف و پیام عدم دسترس‌پذیری سرویس نمایش داده شود.

#### `GET /landing/content`

بدون توکن و Tenant-based است. پاسخ `data` شامل این فیلدهاست:

```text
app_name, app_subtitle, headline, description,
features[{order,title,body}], cta_label, footnote,
terms_url, min_supported_version
```

`features` را بر اساس ترتیب پاسخ نمایش دهید. `terms_url` می‌تواند `null` باشد و نباید در اپ جایگزین محیطی hardcode شود. مقایسه `min_supported_version` باید به‌صورت نسخه معنایی انجام شود؛ رفتار اجباری/اختیاری ارتقا فقط طبق سیاستی اجرا شود که Backend صریحاً در قرارداد آینده ارائه می‌کند.

## 6. زمان، پول و نوع داده

- تمام زمان‌ها ISO-8601 همراه offset هستند؛ به `DateTime` timezone-aware تبدیل شوند.
- `server_time` را با زمان monotonic دستگاه ترکیب کنید و offset بسازید؛ countdown نباید به ساعت اشتباه گوشی وابسته باشد.
- تاریخ شمسی و labelهای آماده صرفاً نمایشی‌اند؛ درخواست `date` با `YYYY-MM-DD` ارسال می‌شود.
- همه مبلغ‌ها integer و واحد آن‌ها `TOMAN` است؛ هیچ تقسیم بر 10 یا تبدیل ریال انجام ندهید.
- مدت تماس برحسب ثانیه و مدت رزرو/مالی برحسب دقیقه است؛ واحدها را در نام مدل حفظ کنید.
- enumها را tolerant پیاده کنید: مقدار ناشناخته باید به `unknown(rawValue)` برود، نه crash.
- nullable با absent متفاوت است؛ DTOها presence را در updateهای حساس حفظ کنند.

## 7. احراز هویت OTP

### `POST /auth/otp/request`

بدون توکن. Body:

```json
{"mobile":"09xxxxxxxxx","device_identifier":"stable-installation-uuid"}
```

- mobile باید شماره فعال پزشک/مشاور Tenant باشد.
- OTP چهاررقمی، اعتبار 120 ثانیه، فاصله ارسال مجدد 60 ثانیه، حداکثر 5 تلاش ناموفق و قفل 15 دقیقه است.
- پاسخ `202` شامل `expires_in` و `resend_after` است.
- اگر «ورود تستی» در پنل Tenant فعال باشد، پیامک ارسال نمی‌شود و فقط در همان حالت پاسخ شامل `test_mode:true` و `test_code:"1234"` است.
- اگر حالت تست خاموش باشد این دو فیلد در پاسخ وجود ندارند. اپ هرگز نباید 1234 را hardcode یا برای محیط عادی پیشنهاد کند؛ فقط داده پاسخ را نمایش دهد.
- در حالت تست، حساب همچنان باید پزشک/مشاور فعال باشد، اما محدودیت شخصی `app_access` bypass می‌شود.

### `POST /auth/otp/verify`

```json
{
  "mobile":"09xxxxxxxxx",
  "code":"1234",
  "device_name":"نام دستگاه قابل فهم",
  "device_os":"android",
  "device_identifier":"stable-installation-uuid",
  "fcm_token":"optional",
  "device_version":"نسخه اپ یا دستگاه",
  "device_info":{"model":"...","os_version":"...","app_version":"..."}
}
```

- `device_os`: فقط `android` یا `ios`.
- `device_identifier` الزامی و حداکثر 191 کاراکتر؛ `device_name` حداکثر 100؛ `fcm_token` حداکثر 4096.
- ورود مجدد همان installation فقط token قبلی همان installation را باطل می‌کند؛ چند دستگاه مستقل مجازند.
- token از نوع Bearer و دارای `expires_at` است؛ اعتبار پیش‌فرض 90 روز.
- پاسخ موفق شامل `data.token_type`, `data.access_token`, `data.expires_at`, `data.practitioner` و `data.practitioner.softphone` است.
- در حالت تست فعال، `data.test_mode` و `data.test_code` نیز وجود دارند.

### `POST /auth/logout`

فقط token جاری را باطل می‌کند. پس از پاسخ یا حتی خطای شبکه، اطلاعات محلی همان نشست را پاک کنید؛ برای retry logout نباید کاربر در UI گیر کند.

## 8. پروفایل، وضعیت و Softphone

### `GET /me`

منبع قطعی boot اپ. فیلدهای اصلی:

```text
id, user_id, display_name, initials, kind, specialty,
specialties[], activity_centers[], avatar_url, license_number,
availability, booking_enabled, extension, softphone,
default_duration_minutes, patient_hourly_rate,
practitioner_hourly_rate, server_time
```

`kind` پزشک یا مشاور را مشخص می‌کند؛ برچسب UI را از آن بسازید ولی دسترسی را با حدس نقش کنترل نکنید.

ساختار کامل Softphone:

```json
{
  "configured": true,
  "server_address": "raw-admin-value-or-null",
  "server_host": "normalized-host-or-null",
  "server_port": 5061,
  "transport": "tls",
  "extension": "102",
  "username": "sip-user",
  "password": "secret",
  "missing_fields": []
}
```

`configured=true` فقط وقتی host، extension، username و password موجودند. اگر false است، با SDK تماس نگیرید و فهرست `missing_fields` را برای پشتیبانی/کاربر ترجمه کنید.

### `PATCH /me/availability`

```json
{"availability":"ready"}
```

مقادیر: `ready`, `busy`, `offline`. این وضعیت کسب‌وکاری است و جایگزین registration state محلی SIP نیست.

### `GET /me/sections`

فهرست بخش‌ها/برنامه کاری پزشک؛ read-only است و برای نمایش برنامه و فیلترها استفاده می‌شود.

### `GET /voip/config`

همان قرارداد کامل Softphone را برمی‌گرداند. برای refresh دستی یا بعد از بازگشت از صفحه تنظیمات استفاده شود.

### `PUT /voip/config`

```json
{"extension":"102","username":"sip-user","password":"new-secret-or-omit"}
```

- extension الزامی، فقط 1 تا 20 رقم؛ username الزامی و حداکثر 255؛ password اختیاری و حداکثر 1024.
- حذف یا خالی‌فرستادن password باید رمز فعلی را حفظ کند.
- server host/port/transport Tenant-level هستند و اپ اجازه تغییر آن‌ها را ندارد؛ فیلد اضافه ارسال نکنید.
- پس از موفقیت، از پاسخ جدید account را rebuild و register کنید.

## 9. Dashboard

### `GET /dashboard?date=YYYY-MM-DD`

فیلدهای سطح بالا:

```text
server_time, timezone, date, date_jalali, date_label,
practitioner, booking, voip, next_appointment, upcoming_today,
today_stats, important_messages, financial_summary, empty_state, week_strip
```

- `voip.configured` آمادگی credential را نشان می‌دهد.
- `voip.connected` در Backend می‌تواند `null` باشد؛ اتصال واقعی را از SIP SDK نمایش دهید.
- countdown را با `server_time` و زمان شروع/پایان بسازید.
- `important_messages` و `empty_state` از سرور نمایش داده شوند.
- Dashboard را هنگام resume معقول refresh کنید، ولی polling سنگین نسازید.

## 10. نوبت‌ها و مجوز عملیات

### `GET /appointments`

Queryها:

```text
scope=today|tomorrow|past|all   # پیش‌فرض today
date=YYYY-MM-DD                 # در صورت وجود بر scope مقدم است
q=نام یا موبایل بیمار
page=1
per_page=1..100
```

pagination را از `links` و `meta` بخوانید؛ صفحه را با حدس total نسازید.

### `GET /appointments/{appointment}`

مدل جزئیات شامل این گروه‌هاست:

```text
id, file_no, starts_at, ends_at, duration_minutes,
phase, status, status_reason, countdown,
patient, section, complaint, calls, reports_count,
settlement, actions
```

`phase`: `upcoming|in_progress|past|unknown`. هر action معمولاً دارای `allowed`, `reason_code`, `reason` و داده تکمیلی است. نمایش و فعال‌بودن دکمه‌های auto-call، complete، no-show و settlement فقط باید از `actions` بیاید. زمان‌های grace را در Flutter hardcode نکنید؛ سرور تصمیم‌گیر نهایی است.

قبل از mutation حساس، جزئیات تازه دریافت کنید. در double tap دکمه را تا پایان درخواست disable کنید.

## 11. تماس‌ها

### `GET /appointments/{appointment}/calls`

هر رکورد می‌تواند شامل `id`, `sequence`, `direction`, `started_at`, `answered_at`, `ended_at`, `duration_seconds`, `result`, `early`, `ended_by`, `channel`, `note` باشد. `meta` شامل total/answered/missed/talk_seconds است.

### `GET /calls/active`

`data` یا تماس فعال است یا `null` و appointment مربوطه نیز مشخص می‌شود. این endpoint همراه state محلی SDK برای بازیابی UI پس از restart/resume استفاده شود. هیچ‌کدام به تنهایی جای دیگری را حذف نمی‌کند:

- SDK: وضعیت زنده signaling/media روی دستگاه؛
- API: وضعیت ثبت‌شده و قابل اعتماد کسب‌وکاری روی سرور.

### `PUT /calls/{call}/note`

```json
{"note":"متن تا 3000 کاراکتر یا null"}
```

برای auto-save debounce بگذارید و فقط آخرین نسخه را ارسال کنید. تنظیم `auto_note_save` تعیین می‌کند ذخیره خودکار فعال باشد یا دکمه ذخیره نمایش داده شود.

### `POST /appointments/{appointment}/auto-call`

درخواست تماس خودکار را ثبت می‌کند و معمولاً `202` با `request_id`, `appointment_id`, `state`, `requested_at`, `extension` می‌دهد.

- فقط وقتی `actions.auto_call.allowed=true` دکمه فعال باشد.
- VoIP ناقص، اقدام زودهنگام یا درخواست تکراری می‌تواند 409 دهد.
- اختلال Provider می‌تواند 503 دهد.
- موفقیت `202` به معنی پذیرفته‌شدن درخواست است، نه برقرارشدن قطعی تماس؛ وضعیت را refresh کنید.
- Backend فعلی رویداد آغاز زنگ را از WebSocket ارسال نمی‌کند؛ FCM و refresh API مکانیزم هماهنگی‌اند.

## 12. گزارش مشاوره و تاریخچه بیمار

### `GET /appointments/{appointment}/reports`

case state، بسته/باز بودن، گزارش‌ها و لیست outcomeهای مجاز را برمی‌گرداند. outcomeها:

```text
SUCCESSFUL
FOLLOW_UP_REQUIRED
PRESCRIPTION_PROVIDED
REFERRED
PATIENT_NO_ANSWER
CONSULTANT_NO_ANSWER
CANCELLED
OTHER
```

### `POST /appointments/{appointment}/reports`

```json
{
  "outcome":"SUCCESSFUL",
  "subject":"عنوان 3 تا 200 کاراکتر",
  "report_text":"متن گزارش 10 تا 10000 کاراکتر",
  "follow_up_at":"ISO-8601 date-time or null"
}
```

اگر outcome یا قرارداد سرور follow-up را لازم بداند، validation سرور ملاک نهایی است. گزارش پزشکی را offline queue نکنید و پس از موفقیت تعداد گزارش و actions نوبت را refresh کنید.

### `GET /patients/{patient}/history`

فقط سوابق مجاز مرتبط با همین پزشک را برمی‌گرداند؛ اپ نباید امکان کشف شناسه یا سابقه پزشکان دیگر ایجاد کند. 404 را «عدم دسترسی/یافت نشد» نمایش دهید.

## 13. پایان پرونده و عدم حضور

### `POST /appointments/{appointment}/complete`

```json
{"completion_confirmed":true}
```

تکمیل معمولاً نیازمند گزارش، نوبت مالی معتبر و شرایط زمانی مناسب است. endpoint idempotent است و پاسخ شامل operation، case_state، idempotent و appointment تازه است.

### `POST /appointments/{appointment}/no-show`

```json
{"no_show_confirmed":true}
```

فقط بعد از پایان و در نبود شواهد تماس معتبر بیمار مجاز است. تضاد با تماس پاسخ‌داده‌شده یا state نهایی رد می‌شود. complete و no-show دو state رقیب هستند و تبدیل یکی به دیگری از اپ مجاز نیست.

برای هر دو عملیات:

1. action تازه را چک کنید.
2. modal تأیید صریح نشان دهید.
3. درخواست را یک بار ارسال کنید.
4. از appointment پاسخ یا GET مجدد برای بازسازی کل صفحه استفاده کنید.

## 14. تسویه

### `GET /appointments/{appointment}/settlement`

فیلدهای مهم:

```text
id, appointment_id, status, finalized, can_confirm,
reserved_minutes, raw_talk_seconds, ignored_talk_seconds,
connection_overhead_minutes, billable_talk_seconds,
system_unused_minutes, approved_unused_minutes,
manual_adjustment_requires_reason, total_paid,
suggested_refund_amount, effective_refund_amount,
used_amount, practitioner_earned_amount, platform_profit_amount,
currency, approved_at, wallet_transaction, audit_count
```

تمام محاسبات سرورمحورند. Flutter نباید مبلغ را دوباره محاسبه یا نتیجه خودش را جایگزین کند. صرفاً برای توضیح UI: سرور تماس پاسخ‌داده‌شده و overhead را محاسبه، به زمان رزرو cap، ثانیه را به دقیقه گرد و refund را با قواعد مالی سامانه محاسبه می‌کند.

### `POST /appointments/{appointment}/settlement`

```json
{
  "settlement_confirmed":true,
  "approved_unused_minutes":15,
  "reason":"علت تغییر نسبت به پیشنهاد سیستم"
}
```

- `approved_unused_minutes`: عدد صحیح 0 تا 1440 و حداکثر زمان رزرو.
- اگر مقدار تأیید پزشک با پیشنهاد سیستم فرق دارد، reason تا 2000 کاراکتر الزامی است.
- فقط بعد از پایان و وقتی `can_confirm=true` مجاز است.
- عملیات idempotent است؛ بعد از finalize از سمت پزشک تغییرپذیر نیست.
- response شامل `idempotent`, `settlement`, `appointment` است؛ هر دو مدل را جایگزین state قبلی کنید.

## 15. گزارش روزانه

### `GET /reports/daily`

```text
scope=today
scope=date&date=YYYY-MM-DD
scope=last_7_days
```

پاسخ شامل `scope`, `timezone`, `from`, `to`, `currency`, `totals`, `days` است. برای `last_7_days` دقیقاً هفت ردیف انتظار می‌رود و روز بدون فعالیت با صفر می‌آید. نمودار و کارت‌ها فقط از `totals/days` ساخته شوند؛ محاسبه کل از لیست نوبت‌های صفحه‌بندی‌شده اشتباه است.

## 16. تنظیمات اپ پزشک

### `GET /settings`

لیست آیتم‌های `{key,label,value}`. کلیدهای نسخه فعلی:

| key | کاربرد |
|---|---|
| `push_appointments` | اعلان نوبت‌های جدید |
| `push_calls` | اعلان تماس ورودی |
| `ring_sound` | صدای زنگ |
| `auto_note_save` | ذخیره خودکار یادداشت |

### `PATCH /settings`

```json
{"key":"push_calls","value":true}
```

UI باید label سرور را نمایش دهد و کلید ناشناخته آینده را بدون crash پشتیبانی کند. optimistic update فقط با rollback کامل در خطا قابل قبول است.

## 17. ثبت دستگاه و اعلان Push

### `PATCH /devices/current`

```json
{
  "device_identifier":"stable-installation-uuid",
  "fcm_token":"new-token-or-null",
  "device_version":"app/device version"
}
```

- در login/verify، refresh شدن token و reinstall/update لازم است ارسال شود.
- `fcm_token:null` یعنی حذف اعلان برای دستگاه.
- پاسخ شامل `device_identifier`, `notifications_enabled`, `device_version`, `updated_at` است.

payloadهای شناخته‌شده اعلان:

```text
type=voip_appointment_reminder
type=voip_call_result
appointment_id
call_id (در صورت وجود)
link (در صورت وجود)
```

Payload منبع حقیقت پزشکی/مالی نیست. در foreground، background و terminated فقط route مقصد را تعیین کنید و سپس API مربوطه را refresh کنید. روی Android محدودیت اجرای background و Foreground Service و روی iOS CallKit/PushKit باید طبق سیاست سیستم‌عامل رعایت شود؛ وجود FCM معمولی به تنهایی تضمین زنگ SIP در حالت killed نیست. اگر تماس ورودی واقعی در حالت killed لازم است، زیرساخت باید PushKit/APNs VoIP و lifecycle متناظر را به‌صورت رسمی فراهم کند؛ آن را شبیه‌سازی نکنید.

## 18. معماری پیشنهادی Flutter

ساختار بدون وابستگی به state-management خاص:

```text
lib/
  app/                 routing, bootstrap, lifecycle
  core/
    config/            flavor و TenantConfig
    network/           API client, auth interceptor, error mapper
    security/          SecureStorage, redaction
    time/              ServerClock
    voip/              interface و event models
  data/
    dto/               JSON DTOها
    repositories/      auth/profile/appointments/calls/...
  domain/
    models/            مدل‌های immutable و enumهای tolerant
    services/          BootCoordinator, SessionManager
  features/
    auth/
    dashboard/
    appointments/
    call/
    reports/
    settlement/
    profile/
    settings/
  platform_voip/       bridge به SDK بومی Android/iOS
```

Riverpod یا BLoC مطابق استاندارد تیم قابل استفاده است؛ قرارداد مهم این است که View مستقیماً HTTP یا SIP SDK را صدا نزند. `VoipService` حداقل این interface را داشته باشد:

```text
configure(SoftphoneConfig)
register()
unregister()
answer(callId)
hangup(callId)
setMuted(bool)
setSpeaker(bool)
sendDtmf(value)
registrationStateStream
callStateStream
audioRouteStream
```

state ثبت SIP:

```text
unconfigured -> configuring -> registering -> registered
                                      |            |
                                      v            v
                                    failed <-> reconnecting
```

state تماس حداقل: `idle`, `incoming`, `outgoing`, `ringing`, `connecting`, `active`, `held`, `ending`, `ended`, `failed`. رویدادها باید serial شوند تا callback هم‌زمان SDK باعث state معیوب نشود.

## 19. صفحه‌ها و Navigation

حداقل صفحه‌ها:

1. Splash/Bootstrap با health، session و SIP registration.
2. Landing و ورود موبایل.
3. OTP با countdown، resend و نمایش کنترل‌شده Test Mode.
4. Dashboard.
5. فهرست نوبت‌ها با scope/date/search/pagination.
6. جزئیات نوبت و actions.
7. UI تماس ورودی/فعال با mute، speaker، timer و hangup.
8. تاریخچه تماس و note.
9. گزارش مشاوره و تاریخچه بیمار.
10. complete/no-show confirmation.
11. settlement و confirmation.
12. گزارش روزانه.
13. پروفایل، availability و sections.
14. تنظیمات اعلان/صدا/auto-save.
15. تنظیمات VoIP با عدم نمایش password موجود.

Deep link اعلان ابتدا باید session و tenant را validate کند؛ اگر داده مقصد مجاز نبود به Dashboard برگردد.

## 20. Cache و حالت Offline

- cache کوتاه‌مدت Dashboard/appointments فقط برای skeleton/نمایش آخرین داده مجاز است و باید برچسب قدیمی‌بودن داشته باشد.
- mutation مالی، گزارش، complete، no-show، note و auto-call offline queue نشوند.
- داده حساس بیمار به صورت دائمی cache نشود؛ اگر نیاز قطعی شد، storage رمزنگاری‌شده، TTL و purge در logout الزامی است.
- پس از بازگشت اینترنت: `/me`، registration SIP، `/calls/active` و صفحه جاری refresh شوند.
- stale data نباید action فعال ایجاد کند.

## 21. مدیریت خطا و UX

- خطای field زیر همان ورودی؛ `message` عمومی در banner/snackbar.
- registration SIP از API login مستقل نمایش داده شود: کاربر ممکن است وارد باشد ولی تلفن پیکربندی یا متصل نباشد.
- statusهای پیشنهادی: «در حال اتصال تلفن»، «تلفن آماده»، «تنظیمات تلفن ناقص»، «اتصال تلفن قطع است».
- permission میکروفن را درست قبل از اولین نیاز با توضیح فارسی بگیرید؛ ردشدن آن نباید اپ را crash کند.
- در تماس فعال screen lock، audio focus، interruption، Bluetooth disconnect و app lifecycle تست شود.
- هیچ تصمیم مالی یا clinical فقط با cache/client clock گرفته نشود.

## 22. آزمون‌های الزامی قبل از تحویل

### Auth و Tenant

- ورود عادی، OTP اشتباه، expiry، resend، 5 تلاش و قفل.
- حالت تست روشن: نمایش 1234 و ورود حساب فعال؛ حالت خاموش: نبود کامل test fields و عدم پذیرش فرضی 1234.
- حساب غیرفعال، `app_access` خاموش در حالت عادی، token منقضی و tenant اشتباه.
- ورود مجدد همان installation و ورود هم‌زمان دو دستگاه.

### SIP

- Register با TLS/TCP/UDP مطابق response، credential درست/غلط و host قطع.
- `configured=false` برای هر missing field.
- تغییر credential در Backend و refresh `/me`.
- تغییر شبکه، airplane mode، resume، killed/relaunch و logout.
- تماس ورودی/خروجی، answer/hangup، mute/speaker/Bluetooth، audio interruption.
- اطمینان از نبود password/token/OTP در log و crash report.

### API و کسب‌وکار

- pagination/filter/search و timezone/countdown.
- actionهای مجاز/غیرمجاز، double tap و 409/422.
- auto-call با 202، conflict و provider failure.
- گزارش معتبر/نامعتبر، history غیرمجاز.
- complete/no-show و idempotency.
- settlement پیشنهادی، تغییر دستی با reason، finalized و integer TOMAN.
- daily today/date/7 days و روزهای صفر.
- FCM token rotation/removal و هر سه lifecycle اعلان.

### کیفیت

- RTL کامل، فونت و اعداد، accessibility و dynamic text.
- شبکه کند، timeout، پاسخ malformed، enum ناشناخته و nullableها.
- Release build Android/iOS، permission declarations، background policies و crash-free boot.

## 23. ترتیب اجرای پروژه برای برنامه‌نویس یا AI

این ترتیب dependencyها را رعایت می‌کند و نباید جابه‌جا شود:

1. Config/Flavor، مدل Tenant و API client امن.
2. DTOها، error mapping، Secure Storage، Device ID و ServerClock.
3. Auth OTP، SessionManager و logout سراسری.
4. Profile `/me` و BootCoordinator.
5. abstraction و bridge بومی SIP، registration lifecycle و Call UI.
6. Dashboard.
7. appointments list/detail و action rendering.
8. calls/active/history/note و auto-call.
9. reports و patient history.
10. complete/no-show.
11. settlement.
12. daily reports.
13. settings، availability، sections و VoIP config.
14. FCM/deep links/background integration.
15. offline policy، hardening امنیت، integration/E2E tests و Release QA.

در پایان هر مرحله mock حذف و endpoint واقعی وصل شود. قرارداد دقیق schemaها باید از مستندات OpenAPI همان Tenant در مسیرهای زیر خوانده شود:

```text
{TENANT_BASE_URL}/docs/practitioner
{TENANT_BASE_URL}/docs/practitioner/openapi.json
```

اگر بین این راهنما و OpenAPI محیط اجرا اختلافی بود، OpenAPI همان نسخه Backend برای نام و nullable بودن فیلدها مرجع serialization است؛ اما قواعد امنیتی، عدم hardcode دامنه، SIP بودن Softphone و server-driven بودن actions/مالی باید حفظ شوند.

## 24. معیار پایان کار

اپ زمانی آماده تحویل است که پزشک بتواند از cold start وارد شود، credential تازه SIP را بگیرد، مثل Zoiper روی PBX register شود، تماس را با audio صحیح مدیریت کند، تمام نوبت‌ها و مجوزهای server-driven را ببیند، گزارش و settlement را بدون محاسبه کلاینت ثبت کند، اعلان را در lifecycleهای مختلف مدیریت کند و در هیچ log/storage ناامن، token، OTP، SIP password یا اطلاعات بیمار باقی نماند.
