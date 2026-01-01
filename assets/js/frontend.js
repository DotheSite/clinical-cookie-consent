(function (window, document) {
  'use strict';

  const config = window.cccConsentData || {};
  const storageKey = config.preferencesKey || 'ccc_choice';

  const getCookie = (key) => {
    const match = document.cookie.match(new RegExp('(?:^|; )' + key + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
  };

  const setCookie = (key, value, days) => {
    const expires = new Date();
    expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
    let cookieStr = `${key}=${encodeURIComponent(value)}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
    // Add Secure flag when running on HTTPS so modern browsers accept the cookie
    if (typeof location !== 'undefined' && location.protocol === 'https:') {
      cookieStr += '; Secure';
    }
    document.cookie = cookieStr;
  };

  const getPreference = () => {
    const cookieValue = getCookie(storageKey);
    if (cookieValue) {
      return cookieValue;
    }

    try {
      return window.localStorage.getItem(storageKey);
    } catch (error) {
      return null;
    }
  };

  const setPreference = (value) => {
    setCookie(storageKey, value, Number(config.expirationDays || 180));
    try {
      window.localStorage.setItem(storageKey, value);
    } catch (error) {
      // Ignore storage errors; cookie persists the preference.
    }

    document.documentElement.classList.remove('ccc-status-accept', 'ccc-status-reject', 'ccc-status-required');
    document.documentElement.classList.add(`ccc-status-${value}`);
    window.clinicalCookieConsent = { status: value, policyUrl: config.policyUrl };
    document.dispatchEvent(new CustomEvent('clinicalCookieConsentSaved', { detail: { status: value } }));
  };

  const hideBanner = (banner) => {
    banner.classList.add('ccc-hidden');
    banner.classList.remove('ccc-visible');
  };

  const showBanner = (banner) => {
    banner.classList.remove('ccc-hidden');
    banner.classList.add('ccc-visible');
  };

  const bindActions = (banner) => {
    const closeButton = banner.querySelector('.ccc-close');
    const buttons = banner.querySelectorAll('[data-ccc-action]');

    if (closeButton) {
      closeButton.addEventListener('click', () => hideBanner(banner));
    }

    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        const action = button.getAttribute('data-ccc-action');
        setPreference(action);
        hideBanner(banner);
      });
    });
  };

  const init = () => {
    const banner = document.getElementById('ccc-banner');
    if (!banner) return;

    const preference = getPreference();
    if (preference) {
      document.documentElement.classList.add(`ccc-status-${preference}`);
      window.clinicalCookieConsent = { status: preference, policyUrl: config.policyUrl };
      return;
    }

    showBanner(banner);
    bindActions(banner);
  };

  if (document.readyState !== 'loading') {
    init();
  } else {
    document.addEventListener('DOMContentLoaded', init);
  }
})(window, document);
