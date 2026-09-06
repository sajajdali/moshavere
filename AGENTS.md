# Local execution preferences

When the user asks to run or start this project (including «پروژه رو ران کن» or
«پروژه رو اجرا کن»), use these exact local URLs and port unless they explicitly
request a change:

- Website: http://nobat1.test:8084
- Site administration login: http://nobat1.test:8084/shemiranWebLogin
- Central panel login: http://central.test:8084/centralLogin

Read LOCAL_SETUP.md for existing setup details. Run ./start-local.command from
this repository; it selects Homebrew PHP 8.3 and serves on 127.0.0.1:8084.
Check whether the project is already running first and reuse a healthy existing
server. Keep the server running after the request completes. Both domains must
resolve to 127.0.0.1, and the configured XAMPP MySQL service must be available.
Preserve the existing .env and databases; starting the app does not require
reinstalling dependencies, reseeding, or resetting data. Do not substitute port
8000, port 80, localhost URLs, or another checkout for these requested URLs.

Verify all three URLs after starting. Follow redirects with a cookie jar for
the login routes so session-dependent dashboard responses are checked correctly.
Report actual HTTP results and any unresolved startup problem.
