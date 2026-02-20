// components.js - small behaviors:
// - manages header classes .at-top and .scrolled for header color switching
// - non-invasive: if other code already toggles those classes, this script will not override them unnecessarily.

(function () {
  if ( typeof window === 'undefined' ) return;

  var doc = document;
  var win = window;
  var header = doc.querySelector('.site-header, .header');

  if ( ! header ) return;

  var ticking = false;

  function updateHeaderState() {
    var y = win.scrollY || win.pageYOffset;
    var atTop = ( y <= 8 ); // allow small tolerance
    if ( atTop ) {
      header.classList.add('at-top');
      header.classList.remove('scrolled');
    } else {
      header.classList.remove('at-top');
      header.classList.add('scrolled');
    }
  }

  // Initialize
  updateHeaderState();

  // Throttled scroll
  win.addEventListener('scroll', function () {
    if ( ! ticking ) {
      ticking = true;
      win.requestAnimationFrame(function () {
        updateHeaderState();
        ticking = false;
      });
    }
  }, { passive: true });

  // Also update on resize and load
  win.addEventListener('resize', function () { updateHeaderState(); }, { passive: true });
  win.addEventListener('load', function () { updateHeaderState(); });

})();