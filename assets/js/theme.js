(function () {
  'use strict';
  var root = document.documentElement;
  var storageKey = 'orion26-theme';
  var searchTimer = null;

  function secureExternalLinks(scope) {
    if (!window.OrionThemeConfig || !window.OrionThemeConfig.externalNewTab) return;
    var siteHost = String(window.OrionThemeConfig.homeHost || location.hostname).replace(/^www\./i, '').toLowerCase();
    (scope || document).querySelectorAll('a[href]').forEach(function (link) {
      var raw = link.getAttribute('href');
      if (!raw || raw.charAt(0) === '#' || /^(mailto|tel|sms|javascript):/i.test(raw)) return;
      try {
        var url = new URL(raw, location.href);
        if (!/^https?:$/.test(url.protocol)) return;
        if (url.hostname.replace(/^www\./i, '').toLowerCase() === siteHost) return;
        link.target = '_blank';
        var rel = new Set((link.getAttribute('rel') || '').split(/\s+/).filter(Boolean));
        ['noopener', 'noreferrer', 'external'].forEach(function (token) { rel.add(token); });
        link.setAttribute('rel', Array.from(rel).join(' '));
      } catch (error) {}
    });
  }

  function legacyTheme() {
    try {
      var match = document.cookie.match(/(?:^|; )wp_user_stylesheet_switcher_js=([^;]*)/);
      if (!match) return null;
      var legacy = JSON.parse(decodeURIComponent(match[1]));
      return String(legacy.s0) === '1' ? 'dark' : 'light';
    } catch (error) { return null; }
  }

  function saveLegacyTheme(theme) {
    try {
      var match = document.cookie.match(/(?:^|; )wp_user_stylesheet_switcher_js=([^;]*)/);
      var legacy = match ? JSON.parse(decodeURIComponent(match[1])) : {};
      legacy.s0 = theme === 'dark' ? 1 : 0;
      document.cookie = 'wp_user_stylesheet_switcher_js=' + encodeURIComponent(JSON.stringify(legacy)) + ';path=/;max-age=31536000;SameSite=Lax';
    } catch (error) {}
  }

  function preferredTheme() {
    var stored = null;
    try { stored = window.localStorage.getItem(storageKey); } catch (error) {}
    if (stored === 'light' || stored === 'dark') return stored;
    var legacy = legacyTheme();
    if (legacy) return legacy;
    if (window.OrionThemeDefault === 'light' || window.OrionThemeDefault === 'dark') return window.OrionThemeDefault;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function applyTheme(theme) {
    root.dataset.theme = theme;
    document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
      var isDark = theme === 'dark';
      button.setAttribute('aria-pressed', String(isDark));
      button.setAttribute('aria-label', isDark ? 'Activer le thème clair' : 'Activer le thème sombre');
      button.querySelector('[data-theme-icon]').textContent = isDark ? '☼' : '☾';
    });
  }

  applyTheme(preferredTheme());
  secureExternalLinks(document);

  document.addEventListener('click', function (event) {
	var date = event.target.closest('.orion-date');
	if (date) {
	  event.preventDefault();
	  event.stopPropagation();
	  toggleDate(date);
	}
    var toggle = event.target.closest('[data-theme-toggle]');
    if (toggle) {
      var next = root.dataset.theme === 'dark' ? 'light' : 'dark';
      try { window.localStorage.setItem(storageKey, next); } catch (error) {}
      saveLegacyTheme(next);
      applyTheme(next);
    }
    var menu = event.target.closest('[data-menu-toggle]');
    if (menu) {
      var nav = document.getElementById('site-navigation');
      var expanded = menu.getAttribute('aria-expanded') === 'true';
      menu.setAttribute('aria-expanded', String(!expanded));
      nav.hidden = expanded;
      document.body.classList.toggle('menu-open', !expanded);
    }
    var search = event.target.closest('[data-search-toggle]');
    if (search) {
      var panel = document.getElementById('site-search');
      var searchExpanded = search.getAttribute('aria-expanded') === 'true';
      search.setAttribute('aria-expanded', String(!searchExpanded));
	  document.body.classList.toggle('search-open', !searchExpanded);
      window.clearTimeout(searchTimer);
      if (searchExpanded) {
        panel.classList.remove('is-open');
        searchTimer = window.setTimeout(function () { panel.hidden = true; }, 240);
      } else {
        panel.hidden = false;
        window.requestAnimationFrame(function () {
          panel.classList.add('is-open');
          panel.querySelector('input[type="search"]').focus();
        });
      }
    }
  });

  document.addEventListener('keydown', function (event) {
	var date = event.target.closest && event.target.closest('.orion-date');
	if (date && (event.key === 'Enter' || event.key === ' ')) {
	  event.preventDefault();
	  toggleDate(date);
	  return;
	}
    if (event.key !== 'Escape') return;
    var searchButton = document.querySelector('[data-search-toggle][aria-expanded="true"]');
    if (searchButton) {
      var searchPanel = document.getElementById('site-search');
      searchPanel.classList.remove('is-open');
      window.clearTimeout(searchTimer);
      searchTimer = window.setTimeout(function () { searchPanel.hidden = true; }, 240);
      searchButton.setAttribute('aria-expanded', 'false');
	  document.body.classList.remove('search-open');
      searchButton.focus();
    }
  });

  function toggleDate(date) {
	var exact = date.getAttribute('aria-checked') === 'true';
	date.textContent = exact ? date.dataset.relative : date.dataset.absolute;
	date.setAttribute('aria-checked', String(!exact));
	date.setAttribute('title', exact ? 'Afficher la date exacte' : 'Afficher la date relative');
  }

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (event) {
    try {
      if (!window.localStorage.getItem(storageKey)) applyTheme(event.matches ? 'dark' : 'light');
    } catch (error) {}
  });
})();
