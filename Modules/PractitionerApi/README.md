# PractitionerApi

راهنمای کامل پیاده‌سازی کلاینت Flutter: [docs/FLUTTER_IMPLEMENTATION_GUIDE_FA.md](docs/FLUTTER_IMPLEMENTATION_GUIDE_FA.md)

Versioned, tenant-aware API for the Android and iOS applications used by doctors
and online-consultation practitioners.

The authoritative roadmap, implementation checklist, decisions, and test log are
maintained in [DEVELOPMENT_PLAN.md](DEVELOPMENT_PLAN.md).

## URLs

- API base: `/api/practitioner/v1`
- Interactive OpenAPI documentation: `/docs/practitioner`
- OpenAPI JSON: `/docs/practitioner/openapi.json`
- Manual Postman collection: [`docs/PractitionerApi.postman_collection.json`](docs/PractitionerApi.postman_collection.json)
- Staging and mobile release checklist: [`docs/STAGING.md`](docs/STAGING.md)

## Authentication

- `POST /api/practitioner/v1/auth/otp/request` sends a four-digit OTP using the
  existing login SMS template.
- `POST /api/practitioner/v1/auth/otp/verify` verifies the OTP and issues a
  90-day Sanctum bearer token with the `practitioner-app` ability.
- `POST /api/practitioner/v1/auth/logout` revokes the current installation's
  token and device record without signing out other devices.
- Only active consultation practitioners with `app_access` enabled can request
  or verify an OTP.
- OTPs are four digits, expire after 120 seconds, can be resent after 60
  seconds, and lock verification for 15 minutes after five invalid attempts.
- A stable `device_identifier` is required when verifying the OTP. Logging in
  again from the same installation revokes that installation's previous token.
- `PATCH /api/practitioner/v1/devices/current` rotates or removes the FCM token
  for the authenticated installation without requiring another login.

## Softphone registration

- Successful OTP verification returns the complete SIP contract in
  `data.practitioner.softphone`.
- `GET /api/practitioner/v1/me` returns the same current contract in
  `data.softphone` whenever the application starts or refreshes the profile.
- Tenant-wide `voip_host`, `voip_port`, and `voip_transport` are the fixed server
  settings. Each practitioner has an individual `extension`, `sip_username`, and
  encrypted `sip_secret`.
- Clients register only when `configured` is true and use `missing_fields` to
  handle incomplete administration settings.
- The returned password is sensitive: never log it or send it to analytics or
  crash reporting; keep it only in memory or operating-system secure storage.

## Architecture rules

- Controllers only coordinate HTTP input and output.
- Validation belongs in versioned FormRequest classes.
- Responses use versioned API Resources.
- Appointment and consultation business rules remain in their owning modules and
  are called through services/actions.
- Every route is tenant-aware and must be covered by a feature test.
- Authenticated routes require Sanctum plus active practitioner app access.
