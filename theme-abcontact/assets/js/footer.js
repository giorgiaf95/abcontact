(function () {
  'use strict';

  function initBackToTop() {
    var btn = document.getElementById('back-to-top');
    if (!btn) return;

    function updateVisibility() {
      var scrollY = window.scrollY || window.pageYOffset || 0;
      if (scrollY > 320) {
        btn.removeAttribute('hidden');
      } else {
        btn.setAttribute('hidden', 'true');
      }
    }

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    updateVisibility();
    window.addEventListener('scroll', updateVisibility, { passive: true });

    var mo = new MutationObserver(function () {
      var nb = document.getElementById('back-to-top');
      if (nb && nb !== btn) {
        btn = nb;
        btn.addEventListener('click', function (e) {
          e.preventDefault();
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  }

  if ( document.readyState === 'loading' ) {
    document.addEventListener('DOMContentLoaded', initBackToTop);
  } else {
    initBackToTop();
  }
})();