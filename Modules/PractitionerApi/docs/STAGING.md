# Practitioner API v1 — staging checklist

## Environment

- Serve the API only over HTTPS on the tenant domain; central domains must return 404.
- Run tenant migrations before deployment and keep `APP_DEBUG=false`.
- Configure queue workers and the scheduler used by consultation reminders.
- Configure Firebase credentials on the server. Never place Firebase server credentials, VoIP API tokens, or SIP secrets in the mobile bundle.
- Set the Tenant VoIP host, SIP port/transport and call token in the administration panel.
- Keep test login disabled. If temporarily enabled, the API intentionally discloses `test_mode=true` and `test_code=1234`; disable it before production sign-off.

## Client contract

- API prefix and version: `/api/practitioner/v1`.
- Store the Sanctum bearer token and SIP password in OS secure storage. Exclude both from logs, analytics and crash reports.
- Refresh `PATCH /devices/current` whenever Firebase rotates the installation token; send `null` after notification permission is revoked.
- Use `GET /calls/active` as the source of truth after an FCM wake-up or app resume. FCM delivery is not a call-state protocol.
- Monetary values are integer toman, call durations are seconds, and reserved/unused durations are minutes.
- Treat 401 as an expired/revoked login, 403 as disabled app access, 404 as unavailable or unauthorized resource, 409 as a call-state conflict, 422 as validation/business-rule failure, and 503 as VoIP provider failure.

## Release verification

1. Import `PractitionerApi.postman_collection.json` and set a non-production tenant URL.
2. Verify normal OTP and controlled test-login-off behavior.
3. Verify login and `/me` return identical Softphone configuration.
4. Exercise appointment ownership with IDs belonging to two practitioners.
5. Test FCM token rotation on Android and iOS, background/terminated delivery, notification permission denial, and token removal.
6. Test auto-call success/409/503, report creation, final actions, wallet settlement and repeated submissions.
7. Compare the seven-day report totals with the administration CSV.
8. Run the OpenAPI analyzer and archive its output with the release.

## Realtime decision

Version 1 does not require WebSocket. PBX call logs and `GET /calls/active` are authoritative; FCM may wake or notify the app, which then refreshes active-call state. Reconsider WebSocket only if product testing requires sub-second in-app ringing/state transitions that PBX plus FCM cannot meet.
