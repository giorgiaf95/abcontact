(function () {
  'use strict';

  // Utilities
  function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
  function qsa(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

  function initCtaModal() {
    var openBtn = document.getElementById('open-cta-modal');
    var modal = document.getElementById('abcontact-cta-modal');
    if (!openBtn || !modal) return;

    var panel = qs('.abcontact-modal__panel', modal);
    var overlay = qs('.abcontact-modal__overlay', modal);
    var closeBtns = qsa('[data-abcontact-modal-close]', modal);

    var lastFocused = null;

    function openModal() {
      lastFocused = document.activeElement;
      modal.setAttribute('aria-hidden', 'false');
      document.documentElement.classList.add('abcontact-modal-open');
      if (panel) {
        panel.focus();
      }
      if (window.dataLayer && Array.isArray(window.dataLayer)) {
        window.dataLayer.push({ event: 'abcontact_cta_open', label: 'Richiedi un preventivo' });
      }
      window.dispatchEvent(new CustomEvent('abcontact:cta_open', { detail: { label: 'Richiedi un preventivo' } }));
      trapFocus(modal);
    }

    function closeModal() {
      modal.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('abcontact-modal-open');
      if (lastFocused && lastFocused.focus) {
        lastFocused.focus();
      }
      window.dispatchEvent(new CustomEvent('abcontact:cta_close'));
    }

    openBtn.addEventListener('click', function (e) {
      e.preventDefault();
      openModal();
    });

    if (overlay) overlay.addEventListener('click', closeModal);
    closeBtns.forEach(function (btn) { btn.addEventListener('click', closeModal); });

    document.addEventListener('keydown', function (e) {
      if (!modal || modal.getAttribute('aria-hidden') === 'true') return;
      if (e.key === 'Escape' || e.keyCode === 27) {
        closeModal();
      }
    });

    function watchFormSubmit() {
      var forms = modal.querySelectorAll('form');
      forms.forEach(function (f) {
        if (f._abcontactTracked) return;
        f.addEventListener('submit', function () {
          if (window.dataLayer && Array.isArray(window.dataLayer)) {
            window.dataLayer.push({ event: 'abcontact_cta_form_submit', label: 'Richiedi un preventivo' });
          }
          window.dispatchEvent(new CustomEvent('abcontact:cta_form_submit'));
        });
        f._abcontactTracked = true;
      });
    }

    var mo = new MutationObserver(function () {
      watchFormSubmit();
    });
    mo.observe(modal, { childList: true, subtree: true });

    watchFormSubmit();
  }

  function trapFocus(root) {
    var focusable = 'a[href], area[href], input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])';
    var nodes = Array.prototype.slice.call(root.querySelectorAll(focusable));
    if (!nodes.length) return;
    var first = nodes[0], last = nodes[nodes.length - 1];

    function handle(e) {
      if (e.key !== 'Tab') return;
      if (e.shiftKey) {
        if (document.activeElement === first) {
          e.preventDefault();
          last.focus();
        }
      } else {
        if (document.activeElement === last) {
          e.preventDefault();
          first.focus();
        }
      }
    }

    root.addEventListener('keydown', handle);

    function cleanup() {
      root.removeEventListener('keydown', handle);
      window.removeEventListener('abcontact:cta_close', cleanup);
    }
    window.addEventListener('abcontact:cta_close', cleanup);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCtaModal);
  } else {
    initCtaModal();
  }
})();