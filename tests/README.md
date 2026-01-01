# Blocking test

This folder contains a small static test to validate the plugin's blocked→replayed script behavior.

Files:
- `blocking-test.html` — interactive page that simulates a header plugin injecting `tracker.js` before consent. The page contains a minimal blocker implementation that queues external scripts and replays them when the `clinicalCookieConsentSaved` event with `status: 'accept'` is dispatched.
- `tracker.js` — simple script that appends a small DOM element when executed (used to prove the script ran).

How to run:

1. Serve the repo directory over HTTP (recommended) so scripts load correctly. From the repo root run:

```bash
python -m http.server 8000
```

2. Open the test page in a browser:

http://localhost:8000/tests/blocking-test.html

3. Test steps:
- Click **Inject header plugin script** — the page will attempt to append `tracker.js` to the head. The blocker will intercept and queue it (you should see a log entry "Blocked script: tracker.js").
- Click **Show status** to view queued count.
- Click **Grant consent (dispatch accept)** — this will fire the `clinicalCookieConsentSaved` event and replay queued scripts. You should see a floating box "Tracker executed" and a log entry "Replayed script: tracker.js".

Notes:
- This static test is intentionally minimal and uses an in-page blocker similar to the plugin's `block_head_scripts()` logic. Real WordPress behavior depends on the PHP-injected blocker running before other plugins output scripts (the plugin uses `wp_head` priority 1).
- To test the plugin in WordPress, install the plugin in a local WP instance and enable it; then add a header plugin that injects a script with `data-ccc-allow="1"` if you want it to be whitelisted.
