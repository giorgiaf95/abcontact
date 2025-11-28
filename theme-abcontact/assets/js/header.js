(function () {
  'use strict';

  /* =============================== Utilities =============================== */
  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  /* ========================= 01) Menu toggle logic ========================= */
  function initMenuToggle() {
    var toggle = document.getElementById('menu-toggle');
    var menu = document.getElementById('primary-menu');

    if (!toggle || !menu) return;

    toggle.setAttribute('aria-expanded', 'false');
    menu.setAttribute('aria-hidden', 'true');

    function setMenu(open) {
      toggle.setAttribute('aria-expanded', String(open));
      menu.setAttribute('aria-hidden', String(!open));
      menu.classList.toggle('is-open', open);

      if (open) {
        document._menuPrevFocus = document.activeElement;
        var firstLink = menu.querySelector('a, button, [tabindex]:not([tabindex="-1"])');
        if (firstLink) firstLink.focus();
      } else {
        if (document._menuPrevFocus) {
          try { document._menuPrevFocus.focus(); } catch (e) {}
          document._menuPrevFocus = null;
        }
      }
    }

    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      var expanded = toggle.getAttribute('aria-expanded') === 'true';
      setMenu(!expanded);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' || e.key === 'Esc') {
        if (menu.classList.contains('is-open')) {
          setMenu(false);
          toggle.focus();
        }
      }
    });

    document.addEventListener('click', function (e) {
      var target = e.target;
      if (menu.classList.contains('is-open') && !menu.contains(target) && !toggle.contains(target)) {
        setMenu(false);
      }
    });

    // close menu when clicking a normal link inside it
    menu.addEventListener('click', function (e) {
      var targ = e.target;
      var link = targ.closest('a');
      if (!link) return;
      var href = link.getAttribute('href') || '';
      if (href && href.indexOf('#') !== 0) {
        setMenu(false);
      }
    });
  }

  /* ========================= 02) Sticky header integration ========================= */
  (function initSticky() {
    if (window.__abcontact_header_sticky_inited) return;
    window.__abcontact_header_sticky_inited = true;

    function initHeaderStickyFor(header) {
      if (!header) return;
      if (header.__abcontact_sticky_inited) return;
      header.__abcontact_sticky_inited = true;

      var lastScroll = window.pageYOffset || document.documentElement.scrollTop;
      var ticking = false;
      var threshold = 80;

      function getAdminBarOffset() {
        var bar = document.getElementById('wpadminbar');
        return bar ? bar.offsetHeight : 0;
      }

      function applyInitial() {
        var y = window.pageYOffset || document.documentElement.scrollTop;
        if (y > threshold) {
          header.classList.add('is-sticky', 'visible');
          header.classList.remove('at-top', 'hidden');
        } else {
          header.classList.remove('is-sticky', 'visible', 'hidden');
          header.classList.add('at-top');
        }

        var adminOffset = getAdminBarOffset();
        header.style.top = adminOffset ? (adminOffset + 'px') : '';
      }

      function onScroll() {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(function () {
          var y = window.pageYOffset || document.documentElement.scrollTop;
          if (y <= threshold) {
            header.classList.remove('is-sticky', 'visible');
            header.classList.add('at-top');
            header.classList.remove('hidden');
          } else {
            header.classList.add('is-sticky', 'visible');
            header.classList.remove('at-top', 'hidden');
          }
          lastScroll = y <= 0 ? 0 : y;
          ticking = false;
        });
      }

      var mq = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)');
      if (mq && mq.matches) {
        applyInitial();
      } else {
        applyInitial();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', applyInitial, { passive: true });
      }

      // API for tests/debug
      window.abcontactHeaderToggle = function () {
        if (!header) return;
        header.classList.toggle('is-sticky');
        header.classList.toggle('visible');
        header.classList.toggle('hidden');
        if (window.console && console.log) console.log('abcontactHeaderToggle ->', header.className);
      };

      document.addEventListener('abcontact-header-disable', function () {
        header.classList.remove('visible', 'is-sticky');
        header.classList.add('at-top');
      });
      document.addEventListener('abcontact-header-enable', function () {
        applyInitial();
      });

      if (window.console && console.debug) {
        console.debug('abcontact: initHeaderSticky for', header);
      }
    }

    function findHeaderAndInit() {
      var header = document.getElementById('site-header') || document.querySelector('.site-header') || document.querySelector('.header');
      if (header) {
        initHeaderStickyFor(header);
        return true;
      }
      return false;
    }

    if (!findHeaderAndInit()) {
      var mo = new MutationObserver(function (mutations, observer) {
        if (findHeaderAndInit()) {
          try { observer.disconnect(); } catch (e) {}
        }
      });
      mo.observe(document.documentElement || document.body, {
        childList: true,
        subtree: true
      });

      setTimeout(function () {
        try { mo.disconnect(); } catch (e) {}
        if (!findHeaderAndInit() && window.console && console.warn) {
          console.warn('abcontact: header element not found after timeout');
        }
      }, 2000);
    }
  })();

  /* =========================== 03) Mega-menu =========================== */
  function initMegaMenu() {
    var nav = document.getElementById('site-navigation') || document.querySelector('.main-navigation');
    if (!nav) return;

    var megaItems = nav.querySelectorAll('.menu-item.mega');

    function closeAllMega() {
      megaItems.forEach(function (mi) {
        mi.classList.remove('open');
        var sub = mi.querySelector(':scope > .sub-menu');
        if (sub) sub.style.left = '';
      });
    }

    function measureSubmenuWidth(submenu) {
      if (!submenu) return 0;
      var prev = {
        display: submenu.style.display,
        visibility: submenu.style.visibility,
        opacity: submenu.style.opacity,
        transform: submenu.style.transform
      };

      submenu.style.display = 'flex';
      submenu.style.visibility = 'hidden';
      submenu.style.opacity = '0';
      submenu.style.transform = 'translateY(-6px)';

      var w = submenu.offsetWidth;

      submenu.style.display = prev.display || '';
      submenu.style.visibility = prev.visibility || '';
      submenu.style.opacity = prev.opacity || '';
      submenu.style.transform = prev.transform || '';

      return w;
    }

    function positionMegaFor(menuItem) {
      var submenu = menuItem.querySelector(':scope > .sub-menu');
      var link = menuItem.querySelector(':scope > a');
      if (!submenu || !link || !nav) return;

      var submenuWidth = measureSubmenuWidth(submenu);
      var navRect = nav.getBoundingClientRect();
      var linkRect = link.getBoundingClientRect();

      var desiredLeft = (linkRect.left + linkRect.width / 2) - (submenuWidth / 2) - navRect.left;

      var minLeft = 8;
      var maxLeft = Math.max(8, navRect.width - submenuWidth - 8);

      desiredLeft = Math.min(Math.max(desiredLeft, minLeft), maxLeft);

      submenu.style.left = desiredLeft + 'px';
      submenu.style.transform = submenu.style.transform || 'translateY(-6px)';
    }

    function repositionAllMegas() {
      megaItems.forEach(function (mi) {
        if (mi.classList.contains('open')) positionMegaFor(mi);
      });
    }

    megaItems.forEach(function (mi) {
      var topLink = mi.querySelector(':scope > a');
      if (!topLink) return;

      topLink.addEventListener('click', function (e) {
        e.preventDefault();

        var isOpen = mi.classList.contains('open');
        if (isOpen) {
          mi.classList.remove('open');
          var sub = mi.querySelector(':scope > .sub-menu');
          if (sub) setTimeout(function () { sub.style.left = ''; }, 360);
        } else {
          closeAllMega();
          mi.classList.add('open');

          requestAnimationFrame(function () {
            positionMegaFor(mi);
            requestAnimationFrame(function () {
              var sub = mi.querySelector(':scope > .sub-menu');
              if (sub) sub.style.transform = 'translateY(0)';
            });
          });

          var firstAction = mi.querySelector('.sub-menu a');
          if (firstAction) firstAction.focus();
        }
      });

      mi.addEventListener('click', function (e) {
        var link = e.target.closest('a');
        if (!link) return;
        if (mi.contains(link) && link.closest('.sub-menu')) {
          mi.classList.remove('open');
        }
      });
    });

    document.addEventListener('click', function (e) {
      if (nav.contains(e.target)) return;
      closeAllMega();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' || e.key === 'Esc') closeAllMega();
    });

    var resizeTimer = null;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        repositionAllMegas();
      }, 120);
    }, { passive: true });

    window.addEventListener('orientationchange', function () {
      setTimeout(repositionAllMegas, 200);
    });

    window.abcontactRepositionMegas = repositionAllMegas;
    window.abcontactCloseAllMega = closeAllMega;
  }

  /* ========================= 04) Header "scrolled" ========================= */
  function applyMenuHeadingFixes() {
    var anchors = document.querySelectorAll(
      '.main-navigation a.menu-heading__anchor, ' +
      '.main-navigation a[data-menu-heading], ' +
      '.main-navigation .menu-heading > a, ' +
      '.main-navigation a.column-title'
    );

    anchors.forEach(function (anchor) {
      if (!anchor || !anchor.getAttribute) return;

      var href = (anchor.getAttribute('href') || '').trim();
      var hasData = anchor.getAttribute('data-menu-heading') === '1';
      var isPlaceholder = (href === '#' || href === '' || /javascript:void\(0\)/i.test(href));
      var shouldDisable = hasData || isPlaceholder;

      if (shouldDisable) {
        anchor.setAttribute('aria-disabled', 'true');
        anchor.setAttribute('tabindex', '-1');
        anchor.setAttribute('role', 'presentation');

        if (anchor.classList && !anchor.classList.contains('menu-heading__anchor')) {
          anchor.classList.add('menu-heading__anchor');
        }

        anchor.style.pointerEvents = 'none';
        anchor.style.cursor = 'default';

        if (!anchor.__menuHeadingClickHandler__) {
          anchor.__menuHeadingClickHandler__ = function (e) { e.preventDefault(); };
          anchor.addEventListener('click', anchor.__menuHeadingClickHandler__);
        }
      } else {
        anchor.removeAttribute('aria-disabled');
        if (anchor.getAttribute('tabindex') === '-1') anchor.removeAttribute('tabindex');
        if (anchor.getAttribute('role') === 'presentation') anchor.removeAttribute('role');

        if (anchor.classList && anchor.classList.contains('menu-heading__anchor')) {
          if (anchor.getAttribute('data-menu-heading') !== '1') {
            anchor.classList.remove('menu-heading__anchor');
          }
        }

        anchor.style.pointerEvents = '';
        anchor.style.cursor = '';

        if (anchor.__menuHeadingClickHandler__) {
          try { anchor.removeEventListener('click', anchor.__menuHeadingClickHandler__); } catch (err) {}
          anchor.__menuHeadingClickHandler__ = null;
        }
      }
    });

    var submenuLinks = document.querySelectorAll('.main-navigation .sub-menu a');
    submenuLinks.forEach(function (a) {
      if (!a) return;
      if (a.getAttribute('data-menu-heading') !== '1' && !a.classList.contains('menu-heading__anchor')) {
        a.style.pointerEvents = 'auto';
        a.style.cursor = 'pointer';
      }
    });
  }

  function cleanupColumnTitleMarkers() {
    var items = document.querySelectorAll('.main-navigation li.column-title');
    items.forEach(function(li) {
      if (!li) return;
      if (li.classList.contains('menu-heading')) li.classList.remove('menu-heading');
      var a = li.querySelector(':scope > a');
      if (!a) return;
      var href = (a.getAttribute('href') || '').trim();
      if (href && href !== '#' && a.getAttribute('data-menu-heading') === '1') {
        a.removeAttribute('data-menu-heading');
        a.removeAttribute('aria-disabled');
        if (a.getAttribute('tabindex') === '-1') a.removeAttribute('tabindex');
        if (a.getAttribute('role') === 'presentation') a.removeAttribute('role');
        if (a.classList.contains('menu-heading__anchor')) a.classList.remove('menu-heading__anchor');
      }
    });
  }

  /* ========================= 05)Init on DOM ready ========================= */
  onReady(function () {
    initMenuToggle();
    initMegaMenu();

    var headerEl = document.getElementById('site-header') || document.querySelector('.site-header') || document.querySelector('.header');
    if (headerEl) {
      var event = document.createEvent('Event');
      event.initEvent('resize', true, true);
      window.dispatchEvent(event);
    }

    applyMenuHeadingFixes();
    cleanupColumnTitleMarkers();

    var nav = document.getElementById('site-navigation') || document.querySelector('.main-navigation');
    if (nav) {
      var moNav = new MutationObserver(function () {
        applyMenuHeadingFixes();
        cleanupColumnTitleMarkers();
      });
      moNav.observe(nav, { childList: true, subtree: true });
    }
  });

  /* ========================= 06) Hero variabile ========================= */
  var bar = document.getElementById('wpadminbar');
  if (bar) {
    document.documentElement.style.setProperty('--wp-adminbar-height', bar.offsetHeight + 'px');
  }

})();