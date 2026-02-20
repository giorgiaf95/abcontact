/* drawer-menu.js - aggiornamento: X senza riquadro + slide-out on close + fallback lines */
(function () {
  'use strict';
  var BREAKPOINT = 800;

  function isMobile() { return window.innerWidth <= BREAKPOINT; }
  function onReady(fn) { if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn); else fn(); }

  function ensureOverlay() {
    var o = document.querySelector('.drawer-overlay');
    if (o) return o;
    o = document.createElement('div');
    o.className = 'drawer-overlay';
    o.setAttribute('aria-hidden', 'true');
    document.body.appendChild(o);
    return o;
  }
  function ensureDrawer() {
    var d = document.querySelector('.mobile-drawer');
    if (d) return d;
    d = document.createElement('div');
    d.className = 'mobile-drawer';
    d.setAttribute('role','dialog');
    d.setAttribute('aria-modal','true');
    d.setAttribute('aria-hidden','true');

    // close button (no box — styling controlled by CSS)
    var closeBtn = document.createElement('button');
    closeBtn.className = 'mobile-drawer-close';
    closeBtn.setAttribute('type', 'button');
    closeBtn.setAttribute('aria-label', 'Chiudi menu');
    // use a simple cross character as fallback (CSS will style it)
    closeBtn.innerHTML = '<span class="close-x" aria-hidden="true">×</span>';
    d.appendChild(closeBtn);

    // container where we inject the menu
    var wrapper = document.createElement('div');
    wrapper.className = 'mobile-drawer-inner';
    d.appendChild(wrapper);

    document.body.appendChild(d);
    return d;
  }

  // build a clean menu from source markup
  function buildCleanMenu(srcRoot) {
    if (!srcRoot) return null;
    var sourceUl = (srcRoot.tagName && srcRoot.tagName.toLowerCase() === 'ul') ? srcRoot : (srcRoot.querySelector && srcRoot.querySelector('ul'));
    if (!sourceUl) return null;

    function walk(ulSrc) {
      var ul = document.createElement('ul'); ul.className = 'menu';
      Array.prototype.forEach.call(ulSrc.children || [], function (srcLi) {
        if (!srcLi || srcLi.tagName.toLowerCase() !== 'li') return;
        var li = document.createElement('li'); li.className = 'menu-item';

        var anchorSrc = srcLi.querySelector(':scope > a');
        var text = anchorSrc ? (anchorSrc.textContent||'').trim() : (srcLi.textContent||'').trim();
        var href = anchorSrc ? (anchorSrc.getAttribute('href') || '#') : '#';

        var row = document.createElement('div'); row.className = 'row';
        var a = document.createElement('a'); a.href = href; a.textContent = text; a.setAttribute('role','menuitem');

        if (anchorSrc && anchorSrc.getAttribute('data-menu-heading') === '1') {
          a.setAttribute('aria-disabled','true'); a.setAttribute('tabindex','-1'); a.classList.add('menu-heading__anchor');
        }
        row.appendChild(a);
        li.appendChild(row);

        var childSub = srcLi.querySelector(':scope > .sub-menu, :scope > ul');
        if (childSub) {
          var childUl = walk(childSub);
          if (childUl) {
            childUl.classList.add('sub-menu');
            li.appendChild(childUl);
            li.classList.add('menu-item-has-children');
          }
        }
        ul.appendChild(li);
      });
      return ul;
    }
    return walk(sourceUl);
  }

  function attachToggles(drawer) {
    var parents = drawer.querySelectorAll('.menu-item-has-children');
    parents.forEach(function (li) {
      var row = li.querySelector(':scope > .row');
      var a = row ? row.querySelector(':scope > a') : null;
      if (!row) return;

      if (!row.querySelector(':scope > button.submenu-toggle')) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'submenu-toggle';
        btn.setAttribute('aria-expanded','false');

        var caret = document.createElement('span');
        caret.className = 'drawer-caret from-toggle';
        btn.appendChild(caret);

        row.appendChild(btn);

        var submenu = li.querySelector(':scope > .sub-menu');

        btn.addEventListener('click', function (e) {
          e.preventDefault();
          if (!submenu) return;
          var open = li.classList.contains('open');
          li.classList.toggle('open', !open);
          btn.setAttribute('aria-expanded', String(!open));
        });

        if (a && !a.__drawer_bound) {
          a.__drawer_bound = true;
          a.addEventListener('click', function (ev) {
            var href = a.getAttribute('href') || '';
            var isPlaceholder = (href === '#' || href.trim() === '' || /javascript:void\(0\)/i.test(href));
            if (isPlaceholder && submenu) {
              ev.preventDefault();
              var isOpen = li.classList.contains('open');
              li.classList.toggle('open', !isOpen);
              var btnInner = row.querySelector('.submenu-toggle');
              if (btnInner) btnInner.setAttribute('aria-expanded', String(!isOpen));
            } else {
              // real navigation allowed; drawer will close via click handler
            }
          });
        }
      }
    });
  }

  function populateDrawer() {
    var sourceRoot = document.getElementById('primary-menu') || document.getElementById('site-navigation') || document.querySelector('.main-navigation');
    var drawer = ensureDrawer(); if (!drawer) return;
    var inner = drawer.querySelector('.mobile-drawer-inner');
    inner.innerHTML = '';

    var cleanMenu = buildCleanMenu(sourceRoot);
    if (!cleanMenu) {
      var ul = document.createElement('ul'); ul.className = 'menu'; var li = document.createElement('li'); li.className='menu-item'; li.textContent = 'Menu non disponibile'; ul.appendChild(li); inner.appendChild(ul);
    } else {
      inner.appendChild(cleanMenu);
    }

    var headerCta = document.querySelector('.header-cta');
    if (headerCta) {
      var src = headerCta.querySelector('a, button, .btn');
      if (src) {
        var clone = src.cloneNode(true);
        clone.classList.add('drawer-cta');
        var innerCaret = clone.querySelector('.caret, .icon-caret'); if (innerCaret) innerCaret.remove();
        inner.appendChild(clone);
      } else {
        var fb = document.createElement('a'); fb.className = 'drawer-cta'; fb.href = '/contatti'; fb.textContent = headerCta.textContent.trim() || 'Contattaci'; inner.appendChild(fb);
      }
    } else {
      var fb2 = document.createElement('a'); fb2.className = 'drawer-cta'; fb2.href = '/contatti'; fb2.textContent = 'Contattaci'; inner.appendChild(fb2);
    }

    attachToggles(inner);
  }

  function destroyDrawer() {
    var overlay = document.querySelector('.drawer-overlay'); if (overlay) try { overlay.remove(); } catch(e) {}
    var d = document.querySelector('.mobile-drawer'); if (d) try { d.remove(); } catch(e) {}
  }

  function initHandlers() {
    var toggle = document.getElementById('menu-toggle');
    var drawer = ensureDrawer();
    var overlay = ensureOverlay();
    if (!toggle || !drawer || !overlay) return;

    function openDrawer() {
      populateDrawer();
      // ensure not in closing state
      document.body.classList.remove('drawer-closing');
      document.body.classList.add('drawer-open');
      drawer.setAttribute('aria-hidden','false');
      overlay.setAttribute('aria-hidden','false');
      toggle.setAttribute('aria-expanded','true');
      setTimeout(function(){
        var f = drawer.querySelector('a,button,[tabindex]:not([tabindex="-1"])');
        if (f) try{ f.focus(); } catch(e){}
      },20);
      document.addEventListener('focus', focusTrap, true);
    }

    function closeDrawerSmooth() {
      // start closing animation: add 'drawer-closing' (keeps drawer in DOM while animating)
      if (!document.body.classList.contains('drawer-open')) return;
      document.body.classList.add('drawer-closing');
      // wait for transition end on drawer then finalize
      var onEnd = function (ev) {
        if (ev && ev.target !== drawer) return;
        drawer.removeEventListener('transitionend', onEnd);
        document.body.classList.remove('drawer-closing');
        document.body.classList.remove('drawer-open');
        drawer.setAttribute('aria-hidden','true');
        overlay.setAttribute('aria-hidden','true');
        toggle.setAttribute('aria-expanded','false');
        document.removeEventListener('focus', focusTrap, true);
        try{ if (document._prevActive) document._prevActive.focus(); } catch(e){}
      };
      drawer.addEventListener('transitionend', onEnd);
      // also fallback timeout in case transitionend doesn't fire
      setTimeout(function () {
        if (document.body.classList.contains('drawer-closing')) {
          try { document.body.classList.remove('drawer-closing'); document.body.classList.remove('drawer-open'); drawer.setAttribute('aria-hidden','true'); overlay.setAttribute('aria-hidden','true'); toggle.setAttribute('aria-expanded','false'); } catch(e){}
        }
      }, 420);
    }

    function focusTrap(e){
      if (!document.body.classList.contains('drawer-open')) return;
      if (!drawer.contains(e.target) && e.target !== toggle && !overlay.contains(e.target)) {
        var f = drawer.querySelector('a,button,[tabindex]:not([tabindex="-1"])');
        if (f) try{ f.focus(); } catch(err){}
      }
    }

    if (!toggle.__drawer_attached) {
      toggle.addEventListener('click', function (ev) {
        ev.preventDefault();
        if (document.body.classList.contains('drawer-open')) {
          closeDrawerSmooth();
        } else {
          document._prevActive = document.activeElement;
          openDrawer();
        }
      });
      toggle.__drawer_attached = true;
    }

    if (!overlay.__drawer_attached) {
      overlay.addEventListener('click', function () { try{ closeDrawerSmooth(); } catch(e){} });
      overlay.__drawer_attached = true;
    }

    if (!document.__drawer_esc_attached) {
      document.addEventListener('keydown', function (ev) {
        if ((ev.key === 'Escape' || ev.key === 'Esc') && document.body.classList.contains('drawer-open')) {
          try{ closeDrawerSmooth(); } catch(e) {}
        }
      });
      document.__drawer_esc_attached = true;
    }

    drawer.addEventListener('click', function (ev) {
      var a = ev.target.closest('a');
      if (!a) return;
      var href = a.getAttribute('href') || '';
      if (href && href.indexOf('#') !== 0) {
        setTimeout(function(){ try{ closeDrawerSmooth(); } catch(e){} }, 80);
      }
    });

    // close button inside drawer
    var closeBtn = drawer.querySelector('.mobile-drawer-close');
    if (closeBtn && !closeBtn.__attached) {
      closeBtn.addEventListener('click', function (ev) { ev.preventDefault(); closeDrawerSmooth(); });
      closeBtn.__attached = true;
    }
  }

  function updateEnhancement() {
    if (isMobile()) {
      ensureOverlay();
      ensureDrawer();
      initHandlers();
    } else {
      destroyDrawer();
      document.body.classList.remove('drawer-open');
      document.body.classList.remove('drawer-closing');
    }
  }

  onReady(function(){
    updateEnhancement();
    var rt = null;
    window.addEventListener('resize', function(){ clearTimeout(rt); rt = setTimeout(updateEnhancement,150); }, {passive:true});
    var nav = document.getElementById('site-navigation') || document.querySelector('.main-navigation');
    if (nav) {
      var mo = new MutationObserver(function(){ updateEnhancement(); });
      mo.observe(nav, { childList:true, subtree:true });
    }
  });

})();