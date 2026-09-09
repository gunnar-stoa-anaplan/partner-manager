# Playwright automation service

A long-running Playwright microservice that automates an external website on a
schedule: log in (username/password), run a flow, wait for the response, return
JSON. The Laravel app triggers it; this service does the browser work.

## Architecture (and why)

- **Warm microservice, not per-run process.** Chromium launches once at boot and
  stays warm; each `/run` opens a fresh browser context and closes it. This keeps
  the logged-in session available between runs and gives Laravel a clean HTTP
  boundary.
- **Session persistence via `storageState`.** After a successful run the context's
  cookies/localStorage are written to `state/storageState.json` and reused next
  time, so we don't log in every run. Target currently needs only
  username/password (no MFA).
- **Loopback only.** Binds to `127.0.0.1:3001` — never exposed publicly. Laravel
  calls it over localhost.
- **Runs natively in dev, Docker in prod.** `server.mjs` loads `.env` itself
  (`process.loadEnvFile`) for `npm start`; the Docker image gets vars via
  `env_file`. The official Playwright image is used in prod so Chromium's system
  libraries are correct (Amazon Linux can't use `playwright install --with-deps`).

## Files

| File | Purpose |
|------|---------|
| `server.mjs` | Express + Playwright service. Edit `doWork()` (the flow) and `ensureLoggedIn()` (login). |
| `Dockerfile` | Official Playwright image; for EC2. Keep the tag's version in sync with `package.json`. |
| `docker-compose.yml` | Prod run: `shm_size: 1gb`, loopback port, `state/` + `artifacts/` volumes. |
| `.env.example` | Copy to `.env` and fill in. `.env` is gitignored. |

`node_modules/`, `state/`, `artifacts/`, `.env` are all gitignored.

## Endpoints

- `GET /health` → `{ ok, browser, hasSession }`
- `POST /run` (JSON body is passed to `doWork` as `payload`) →
  `{ ok, ms, result }` on success, or `{ ok:false, error, screenshot }` on
  failure (a full-page screenshot is saved under `artifacts/`). Returns `409`
  if a run is already in progress.

## Run it (local dev)

```bash
cd automation
cp .env.example .env      # then fill in the real values
npm install               # first time only
npm run install:browser   # first time only — downloads Chromium
npm start                 # serves http://127.0.0.1:3001
```

Smoke test:

```bash
curl -s -X POST http://127.0.0.1:3001/run -H 'Content-Type: application/json' -d '{}'
```

## Configuration (`.env`)

| Var | Meaning |
|-----|---------|
| `TARGET_URL` | Page the flow operates on. |
| `LOGIN_URL` | Login page (defaults to `TARGET_URL`). |
| `TARGET_USERNAME` / `TARGET_PASSWORD` | Credentials. In prod, source from AWS SSM Parameter Store / Secrets Manager — don't bake into the image. |
| `SEL_USERNAME` / `SEL_PASSWORD` / `SEL_SUBMIT` | Login-form selectors. |
| `SEL_LOGGED_IN` | A selector present only when authenticated; lets the service detect a live session and skip re-login. Strongly recommended. |
| `HOST` / `PORT` | Bind address (leave as loopback locally). |

## Laravel integration

- Config: `config('services.playwright.url')` ← `PLAYWRIGHT_URL`
  (default `http://127.0.0.1:3001`). **From inside Sail, set it to
  `http://host.docker.internal:3001`** — `127.0.0.1` is the container, not the host.
- Command: `php artisan automation:run` POSTs to `/run` and logs the outcome
  (`app/Console/Commands/RunSiteAutomation.php`).
- Schedule: `routes/console.php` runs `automation:run` a few times a day with
  `withoutOverlapping()`. Fires wherever `php artisan schedule:run` is cron'd.

## Deploy to EC2 (later)

Same box as partner-manager. Docker is the reliable path:

```bash
cd automation && docker compose up -d --build
```

Keep the port loopback-only (security group closed); manage via SSM Session
Manager (the box is SSH-less). Consider pushing failure screenshots/traces to S3,
and use a `t3.medium`+ with swap — headless Chromium is memory-hungry.

## Extending the flow

Everything task-specific lives in two functions in `server.mjs`:

- `ensureLoggedIn(page)` — navigates to `LOGIN_URL`, fills the form, submits, and
  (if `SEL_LOGGED_IN` is set) confirms the session. Reused-session runs skip it.
- `doWork(page, payload)` — the actual steps. Do the actions, `await` the response
  you care about (prefer Playwright's auto-waiting: `waitForSelector`,
  `waitForResponse`, `locator(...).textContent({ timeout })`), and return a result
  object. Whatever you return is echoed back to Laravel in `result`.
