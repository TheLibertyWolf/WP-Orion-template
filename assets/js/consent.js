(function () {
  'use strict';
  var config = window.OrionConsentConfig;
  if (!config) return;
  var executed = {};

  function readChoice() {
    try {
      var match = document.cookie.match(new RegExp('(?:^|; )' + config.cookieName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)'));
      if (!match) return null;
      var choice = JSON.parse(decodeURIComponent(match[1]));
      return choice.version === config.version ? choice : null;
    } catch (error) { return null; }
  }

  function writeChoice(categories) {
    var choice = { version: config.version, necessary: true, categories: categories, updatedAt: new Date().toISOString() };
    document.cookie = config.cookieName + '=' + encodeURIComponent(JSON.stringify(choice)) + ';path=/;max-age=' + (config.days * 86400) + ';SameSite=Lax' + (location.protocol === 'https:' ? ';Secure' : '');
    return choice;
  }

  function runMarkup(markup, locationName) {
    if (!markup) return;
    var template = document.createElement('template');
    template.innerHTML = markup.trim();
    Array.from(template.content.childNodes).forEach(function (node) {
      if (node.nodeName === 'SCRIPT') {
        var script = document.createElement('script');
        Array.from(node.attributes).forEach(function (attribute) { script.setAttribute(attribute.name, attribute.value); });
        script.text = node.text || node.textContent || '';
        (locationName === 'head' ? document.head : document.body).appendChild(script);
      } else {
        (locationName === 'head' ? document.head : document.body).appendChild(node.cloneNode(true));
      }
    });
  }

  function applyChoice(choice) {
    if (!choice || !choice.categories) return;
    Object.keys(config.payloads).forEach(function (category) {
      if (!choice.categories[category] || executed[category]) return;
      executed[category] = true;
      runMarkup(config.payloads[category].head, 'head');
      runMarkup(config.payloads[category].footer, 'footer');
    });
    window.dispatchEvent(new CustomEvent('orion:consent', { detail: choice }));
  }

  function categoryState(value) {
    var state = {};
    Object.keys(config.categories).forEach(function (category) { state[category] = config.categories[category] ? Boolean(value) : false; });
    return state;
  }

  var stored = readChoice();
  if (stored) applyChoice(stored);

  function initBanner() {
    var banner = document.querySelector('[data-orion-consent]');
    if (!banner) return;
    var details = banner.querySelector('[data-consent-details]');
    var customize = banner.querySelector('[data-consent-customize]');
    var save = banner.querySelector('[data-consent-save]');
    var lastFocus = null;

    function show(editing) {
      lastFocus = document.activeElement;
      banner.hidden = false;
      document.body.classList.add('orion-consent-open');
      if (editing && stored && stored.categories) {
        banner.querySelectorAll('[data-consent-category]').forEach(function (input) { input.checked = Boolean(stored.categories[input.dataset.consentCategory]); });
        details.hidden = false; customize.hidden = true; save.hidden = false;
      }
      banner.querySelector('button').focus();
    }
    function hide() {
      banner.hidden = true;
      document.body.classList.remove('orion-consent-open');
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
    function commit(categories) {
      stored = writeChoice(categories);
      applyChoice(stored);
      hide();
    }
    banner.querySelector('[data-consent-accept]').addEventListener('click', function () { commit(categoryState(true)); });
    banner.querySelector('[data-consent-reject]').addEventListener('click', function () { commit(categoryState(false)); });
    customize.addEventListener('click', function () { details.hidden = false; customize.hidden = true; save.hidden = false; });
    save.addEventListener('click', function () {
      var categories = categoryState(false);
      banner.querySelectorAll('[data-consent-category]').forEach(function (input) { categories[input.dataset.consentCategory] = input.checked; });
      commit(categories);
    });
    document.querySelectorAll('[data-consent-manage]').forEach(function (button) { button.addEventListener('click', function () { show(true); }); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !banner.hidden && stored) hide(); });
    if (!stored) {
      if (config.respectGpc && navigator.globalPrivacyControl === true) banner.querySelectorAll('[data-consent-category]').forEach(function (input) { input.checked = false; });
      show(false);
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initBanner); else initBanner();
})();
