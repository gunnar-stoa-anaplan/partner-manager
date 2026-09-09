// Playwright automation microservice.
//
// A long-running service that keeps a headless Chromium warm and exposes a small
// HTTP API on loopback. The Laravel scheduler triggers /run a few times a day;
// the service logs into the target (username/password), persists the session as
// storageState so it isn't repeated every run, and returns JSON.
//
// SECURITY: binds to 127.0.0.1 only. Never expose this port publicly.

import express from 'express';
import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';

// Load .env for native runs (`npm start`). In Docker the vars come from env_file,
// and there may be no .env on disk — so this is best-effort.
try { process.loadEnvFile(path.resolve('.env')); } catch { /* env supplied by the environment */ }

const PORT = Number(process.env.PORT || 3001);
const HOST = process.env.HOST || '127.0.0.1';
const STATE_DIR = process.env.STATE_DIR || path.resolve('state');
const ARTIFACT_DIR = process.env.ARTIFACT_DIR || path.resolve('artifacts');
const STATE_FILE = path.join(STATE_DIR, 'storageState.json');

const TARGET_URL = process.env.TARGET_URL || '';
const LOGIN_URL = process.env.LOGIN_URL || TARGET_URL;
const USERNAME = process.env.TARGET_USERNAME || '';
const PASSWORD = process.env.TARGET_PASSWORD || '';
// Selectors are configurable so the flow can be tuned without code changes.
const SEL_USER = process.env.SEL_USERNAME || 'input[name="username"]';
const SEL_PASS = process.env.SEL_PASSWORD || 'input[name="password"]';
const SEL_SUBMIT = process.env.SEL_SUBMIT || 'button[type="submit"]';
const SEL_LOGGED_IN = process.env.SEL_LOGGED_IN || ''; // a selector only present when authenticated

for (const d of [STATE_DIR, ARTIFACT_DIR]) fs.mkdirSync(d, { recursive: true });

const app = express();
app.use(express.json());

let browser;
let running = false; // simple in-process guard against overlapping runs

function hasState() {
  try { return fs.statSync(STATE_FILE).size > 0; } catch { return false; }
}

async function newContext() {
  return hasState()
    ? browser.newContext({ storageState: STATE_FILE })
    : browser.newContext();
}

async function ensureLoggedIn(page) {
  // If a logged-in marker is configured and already visible, we're done.
  if (SEL_LOGGED_IN) {
    try { await page.waitForSelector(SEL_LOGGED_IN, { timeout: 3000 }); return; } catch { /* fall through */ }
  }
  await page.goto(LOGIN_URL, { waitUntil: 'domcontentloaded' });
  await page.fill(SEL_USER, USERNAME);
  await page.fill(SEL_PASS, PASSWORD);
  await Promise.all([
    page.waitForLoadState('networkidle'),
    page.click(SEL_SUBMIT),
  ]);
  if (SEL_LOGGED_IN) await page.waitForSelector(SEL_LOGGED_IN, { timeout: 15000 });
}

// The actual task. Edit this to do the real work and return a result object.
async function doWork(page, payload) {
  await page.goto(TARGET_URL, { waitUntil: 'domcontentloaded' });
  // TODO: perform the steps, then wait for the response you care about, e.g.:
  //   await page.click('#start');
  //   const result = await page.locator('#result').textContent({ timeout: 60000 });
  const title = await page.title();
  return { title, payload: payload ?? null };
}

app.get('/health', (_req, res) => {
  res.json({ ok: true, browser: !!browser, hasSession: hasState() });
});

app.post('/run', async (req, res) => {
  if (running) return res.status(409).json({ ok: false, error: 'a run is already in progress' });
  running = true;
  const started = Date.now();
  const ctx = await newContext();
  const page = await ctx.newPage();
  try {
    if (USERNAME) await ensureLoggedIn(page);
    const result = await doWork(page, req.body);
    await ctx.storageState({ path: STATE_FILE }); // refresh the saved session
    res.json({ ok: true, ms: Date.now() - started, result });
  } catch (e) {
    const shot = path.join(ARTIFACT_DIR, `fail-${started}.png`);
    try { await page.screenshot({ path: shot, fullPage: true }); } catch { /* ignore */ }
    res.status(500).json({ ok: false, ms: Date.now() - started, error: String(e), screenshot: shot });
  } finally {
    await ctx.close();
    running = false;
  }
});

const server = app.listen(PORT, HOST, async () => {
  browser = await chromium.launch({ headless: true });
  console.log(`playwright automation listening on http://${HOST}:${PORT}`);
});

async function shutdown() {
  try { await browser?.close(); } catch { /* ignore */ }
  server.close(() => process.exit(0));
}
process.on('SIGTERM', shutdown);
process.on('SIGINT', shutdown);
