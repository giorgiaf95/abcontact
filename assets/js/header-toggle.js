(function () {
  var header = document.querySelector('.site-header') || document.querySelector('.header');
  if (!header) return;

  var threshold = 24;
  function refreshHeaderClasses() {
    var sc = window.scrollY || window.pageYOffset;
    if (sc > threshold) {
      header.classList.add('scrolled');
      header.classList.remove('at-top');
    } else {
      header.classList.remove('scrolled');
      header.classList.add('at-top');
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (!header.classList.contains('at-top') && !header.classList.contains('is-sticky')) {
      header.classList.add('at-top');
    }
    refreshHeaderClasses();
  });

  window.addEventListener('scroll', refreshHeaderClasses, { passive: true });
  window.addEventListener('resize', refreshHeaderClasses);
})();