(function () {
  var toggle = document.querySelector('.mr-mobile-toggle');
  var menu = document.getElementById('mr-mobile-menu');

  if (!toggle || !menu) return;

  toggle.addEventListener('click', function () {
    var expanded = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
    menu.hidden = expanded;
  });
})();
