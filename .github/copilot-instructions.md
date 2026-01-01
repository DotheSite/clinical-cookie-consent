<!-- Copilot instructions for agents working on this repo -->
# Clinical Cookie Consent — Agent Guidance

Purpose: help an AI coding agent become productive quickly in this lightweight WordPress plugin.

- **Big picture:** This repo is a single, self-contained WordPress plugin implemented in one main PHP class file: [clinical-cookie-consent.php](clinical-cookie-consent.php). Frontend behavior and styling live in [assets/js/frontend.js](assets/js/frontend.js) and [assets/css/frontend.css](assets/css/frontend.css). The plugin exposes a small admin settings UI and injects CSS variables at runtime.

- **Major components & flow:**
  - `ClinicalCookieConsent` class in `clinical-cookie-consent.php` is the source of truth: activation/uninstall hooks, admin settings, asset enqueuing, banner rendering, and head/footer script hooks.
  - Frontend: `enqueue_assets()` registers `ccc-frontend` style/script and localizes `cccConsentData` (see [clinical-cookie-consent.php](clinical-cookie-consent.php)).
  - Consent persistence: the key is `ccc_choice` (localized as `preferencesKey`), stored in a cookie and mirrored to localStorage by `assets/js/frontend.js`.
  - Events & globals: code dispatches `clinicalCookieConsentSaved` and sets `window.clinicalCookieConsent` (status + policyUrl). Use these hooks to trigger loading third-party scripts.
  - Blocking behavior: `block_head_scripts()` injects a small script that intercepts script element creation to delay external script loads until consent is accepted. Be careful modifying this — it's sensitive and runs very early (hooked at priority 1).

- **Project-specific conventions & patterns:**
  - Options are stored under the option name constant `ClinicalCookieConsent::OPTION_NAME` (ccc_options). See defaults() for canonical keys and value formats.
  - Color values accept CSS variable references (e.g. `var(--brand-accent)`), color functions (`rgb()`) or hex codes. Sanitization is performed by `sanitize_color()`.
  - Admin fields are defined programmatically in `get_fields()`; new UI fields should follow that structure (id, title, type, section, label_for, optional attr).
  - CSS uses runtime-injected variables via `wp_add_inline_style` — prefer editing defaults or providing new variables rather than hardcoding colors into `frontend.css`.

- **Integration points to be aware of:**
  - To add third-party tracking scripts, use `conditional_scripts()` and listen for `clinicalCookieConsentSaved` (status === 'accept'), or modify `loadThirdPartyScripts()` placeholder in `conditional_scripts()`.
  - `block_head_scripts()` runs at `wp_head` priority 1 to intercept early scripts — changes should preserve the mechanism of queueing and replaying blocked scripts when consent is granted.

- **Dev / test workflow (practical commands):**
  - There is no build step. Files are plain PHP/CSS/JS.
  - To run locally: copy the plugin directory into a WordPress installation's `wp-content/plugins/`, activate the plugin via WP admin, then visit a front-end page to test the banner.
  - Useful quick tests: open devtools console, run `window.clinicalCookieConsent` to inspect status; dispatch `document.dispatchEvent(new CustomEvent('clinicalCookieConsentSaved', { detail: { status: 'accept' } }));` to simulate consent.

- **When editing code, watch for these pitfalls:**
  - Do not change the option keys or `preferencesKey` without updating all usages in PHP and JS.
  - Avoid breaking the early `block_head_scripts()` interception logic; minor changes can prevent third-party scripts from loading correctly after consent.
  - Keep `wp_enqueue_script(..., true)` (footer) and use `wp_localize_script` for config; JS depends on `cccConsentData` being available.

- **Examples to reference when making changes:**
  - Injected CSS variables: `enqueue_assets()` → `wp_add_inline_style` in [clinical-cookie-consent.php](clinical-cookie-consent.php).
  - Consent storage + events: `assets/js/frontend.js` shows `getPreference()` / `setPreference()` and event dispatch.
  - Blocking/release logic: `block_head_scripts()` and `conditional_scripts()` in [clinical-cookie-consent.php](clinical-cookie-consent.php).

If anything here seems incomplete or you want examples for a specific change (adding a field, changing the blocking logic, or adding a third-party integration), tell me which area and I'll expand examples or update this file.
