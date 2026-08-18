(function () {
  'use strict';

  document.addEventListener('click', function (event) {
    var selectButton = event.target.closest('[data-orion-media-select]');
    if (selectButton) {
      event.preventDefault();
      var control = selectButton.closest('[data-orion-media]');
      var frame = wp.media({ title: OrionAdmin.mediaTitle, button: { text: OrionAdmin.mediaButton }, multiple: false, library: { type: 'image' } });
      frame.on('select', function () {
        var attachment = frame.state().get('selection').first().toJSON();
        control.querySelector('input[type="hidden"]').value = attachment.id;
        control.querySelector('[data-orion-media-preview]').innerHTML = '<img src="' + (attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url) + '" alt="">';
        control.querySelector('[data-orion-media-remove]').hidden = false;
      });
      frame.open();
    }
    var removeButton = event.target.closest('[data-orion-media-remove]');
    if (removeButton) {
      event.preventDefault();
      var mediaControl = removeButton.closest('[data-orion-media]');
      mediaControl.querySelector('input[type="hidden"]').value = '';
      mediaControl.querySelector('[data-orion-media-preview]').innerHTML = '<span class="dashicons dashicons-format-image" aria-hidden="true"></span>';
      removeButton.hidden = true;
    }
  });

  document.querySelectorAll('[data-orion-checklist]').forEach(function (list) {
    var items = Array.from(list.querySelectorAll('.orion-checklist__items label'));
    var search = list.querySelector('[data-orion-checklist-search]');
    search.addEventListener('input', function () {
      var query = search.value.toLocaleLowerCase().trim();
      items.forEach(function (item) { item.hidden = query && item.textContent.toLocaleLowerCase().indexOf(query) === -1; });
    });
    list.querySelector('[data-orion-checklist-all]').addEventListener('click', function () { items.filter(function (item) { return !item.hidden; }).forEach(function (item) { item.querySelector('input').checked = true; }); });
    list.querySelector('[data-orion-checklist-none]').addEventListener('click', function () { items.filter(function (item) { return !item.hidden; }).forEach(function (item) { item.querySelector('input').checked = false; }); });
  });

  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
  });

  var preview = document.querySelector('[data-orion-preview] .orion-preview-page');
  if (!preview) return;
  var fontStacks = {
    'system': 'system-ui, sans-serif',
    'condensed': '"Arial Narrow", Arial, sans-serif',
    'source-serif-4': '"Source Serif 4", Georgia, serif',
    'atkinson-hyperlegible-next': '"Atkinson Hyperlegible Next", Arial, sans-serif',
    'lora': 'Lora, Georgia, serif',
    'merriweather': 'Merriweather, Georgia, serif',
    'literata': 'Literata, Georgia, serif',
    'ibm-plex-sans': '"IBM Plex Sans", Arial, sans-serif'
  };

  function updatePreview(control) {
    var target = control.dataset.orionPreviewControl;
    var value = control.value;
    if (!target) return;
    if (target.indexOf('--') === 0) preview.style.setProperty(target, value);
    if (target === 'body-font') preview.style.fontFamily = fontStacks[value] || fontStacks.system;
    if (target === 'article-font') preview.querySelector('.orion-preview-article').style.fontFamily = fontStacks[value] || fontStacks['source-serif-4'];
    if (target === 'display-font') preview.querySelector('h2').style.fontFamily = fontStacks[value] || fontStacks.condensed;
    if (target === 'base-size') preview.style.fontSize = value + 'px';
    if (target === 'article-size') preview.querySelector('.orion-preview-article').style.fontSize = value + 'px';
    if (target === 'line-height') preview.querySelector('.orion-preview-article').style.lineHeight = value;
    if (target === 'title-case') preview.querySelector('h2').style.textTransform = value;
    if (target === 'radius') preview.querySelectorAll('blockquote,pre').forEach(function (node) { node.style.borderRadius = value + 'px'; });
    if (target === 'quote-style') preview.querySelector('blockquote').style.fontStyle = value;
  }

  document.querySelectorAll('[data-orion-preview-control]').forEach(function (control) {
    control.addEventListener('input', function () { updatePreview(control); });
    control.addEventListener('change', function () { updatePreview(control); });
    updatePreview(control);
  });

  document.querySelectorAll('[data-heading-level]').forEach(function (row) {
    var heading = preview.querySelector('[data-preview-heading="' + row.dataset.headingLevel + '"]');
    if (!heading) return;
    row.querySelectorAll('[data-heading-prop]').forEach(function (control) {
      function updateHeading() {
        var property = control.dataset.headingProp;
        var value = control.value;
        if (property === 'font') heading.style.fontFamily = fontStacks[value] || fontStacks.system;
        if (property === 'size') heading.style.fontSize = Math.max(12, Number(value) * .62) + 'px';
        if (property === 'weight') heading.style.fontWeight = value;
        if (property === 'case') heading.style.textTransform = value;
        if (property === 'line-height') heading.style.lineHeight = value;
        if (property === 'color') heading.style.color = value;
      }
      control.addEventListener('input', updateHeading);
      control.addEventListener('change', updateHeading);
      updateHeading();
    });
  });
})();
