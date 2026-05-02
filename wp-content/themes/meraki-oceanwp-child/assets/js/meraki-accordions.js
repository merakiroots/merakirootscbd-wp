(function () {
  var triggers = document.querySelectorAll('.mr-accordion__trigger');

  triggers.forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      var panelId = trigger.getAttribute('aria-controls');
      var panel = panelId ? document.getElementById(panelId) : null;
      var expanded = trigger.getAttribute('aria-expanded') === 'true';

      if (!panel) return;

      trigger.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      panel.hidden = expanded;
    });
  });
})();
