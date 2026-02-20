/**
 * Admin JS for Sedi metabox (repeater + bullets + reindex before submit)
 * Path: wp-content/themes/your-theme/assets/js/metaboxes-sedi.js
 *
 * Responsibilities:
 *  - gestire aggiungi/rimuovi blocchi e voci (bullets)
 *  - reindexare e impostare gli attributi name prima del submit
 *  - compatibile con editor classico e block editor (fall-back DOM)
 */
(function () {
  'use strict';

  function qa(sel, ctx){ return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }
  function q(sel, ctx){ return (ctx || document).querySelector(sel); }

  function reindex() {
    var blocks = qa('#theme-ab-sedi-blocks .theme-ab-sedi-block');
    blocks.forEach(function(block, i){
      block.setAttribute('data-index', i);
      var strong = block.querySelector('strong');
      if ( strong ) strong.textContent = 'Blocco ' + (i+1);
      var title = block.querySelector('.theme-ab-block-title');
      if ( title ) title.setAttribute('name', 'sedi_blocks['+i+'][title]');
      var body = block.querySelector('.theme-ab-block-body');
      if ( body ) body.setAttribute('name', 'sedi_blocks['+i+'][body]');
      var bullets = block.querySelectorAll('.theme-ab-bullets-list > div');
      bullets.forEach(function(b, bi){
        var input = b.querySelector('.theme-ab-block-bullet');
        if ( input ) input.setAttribute('name', 'sedi_blocks['+i+'][bullets][]');
      });
    });
  }

  document.addEventListener('click', function(e){
    var t = e.target;

    if ( t.matches('.theme-ab-remove-block') ) {
      e.preventDefault();
      var block = t.closest('.theme-ab-sedi-block');
      if ( block ) {
        block.parentNode.removeChild(block);
        reindex();
      }
      return;
    }

    if ( t.matches('.theme-ab-remove-bullet') ) {
      e.preventDefault();
      var item = t.closest('div');
      if ( item && item.parentNode ) {
        item.parentNode.removeChild(item);
        reindex();
      }
      return;
    }

    if ( t.matches('.theme-ab-add-bullet') ) {
      e.preventDefault();
      var block = t.closest('.theme-ab-sedi-block');
      if ( ! block ) return;
      var list = block.querySelector('.theme-ab-bullets-list');
      var wrapper = document.createElement('div');
      wrapper.style.display = 'flex';
      wrapper.style.gap = '8px';
      wrapper.style.marginBottom = '6px';
      wrapper.innerHTML = '<input class="theme-ab-block-bullet" type="text" value="" style="flex:1"><button type="button" class="button theme-ab-remove-bullet" style="background:#f6f6f8;border:1px solid #ddd;padding:4px 8px;">&times;</button>';
      list.appendChild(wrapper);
      reindex();
      return;
    }

    if ( t && t.id === 'theme-ab-add-block' ) {
      e.preventDefault();
      var container = document.getElementById('theme-ab-sedi-blocks');
      if ( ! container ) return;
      var idx = container.querySelectorAll('.theme-ab-sedi-block').length;
      var el = document.createElement('div');
      el.className = 'theme-ab-sedi-block';
      el.setAttribute('data-index', idx);
      el.style = 'border:1px solid #e6e9ee;padding:10px;margin-bottom:10px;border-radius:6px;background:#fff;';
      el.innerHTML = '' +
        '<p style="display:flex;justify-content:space-between;align-items:center;margin:0 0 8px;">' +
          '<strong>Blocco ' + (idx+1) + '</strong>' +
          '<button type="button" class="button theme-ab-remove-block" style="background:#f6f6f8;border:1px solid #ddd;padding:4px 8px;">Rimuovi</button>' +
        '</p>' +
        '<p style="margin:8px 0;"><label>Titolo</label><br><input class="theme-ab-block-title" name="sedi_blocks['+idx+'][title]" type="text" value="" style="width:100%"></p>' +
        '<p style="margin:8px 0;"><label>Testo (corpo)</label><br><textarea class="theme-ab-block-body" name="sedi_blocks['+idx+'][body]" rows="3" style="width:100%;"></textarea></p>' +
        '<div class="theme-ab-block-bullets" style="margin:8px 0;">' +
          '<label>Elenco puntato</label>' +
          '<div class="theme-ab-bullets-list" style="margin-top:6px;">' +
            '<div style="display:flex;gap:8px;margin-bottom:6px;"><input class="theme-ab-block-bullet" name="sedi_blocks['+idx+'][bullets][]" type="text" value="" style="flex:1"><button type="button" class="button theme-ab-remove-bullet" style="background:#f6f6f8;border:1px solid #ddd;padding:4px 8px;">&times;</button></div>' +
          '</div>' +
          '<p style="margin:6px 0 0;"><button type="button" class="button button-primary theme-ab-add-bullet">Aggiungi voce</button></p>' +
        '</div>';
      container.appendChild(el);
      reindex();
      return;
    }
  });

  // ensure names are present before submit (covers block editor / save)
  document.addEventListener('submit', function(){
    reindex();
  }, true);

  // initial reindex on load (in case server printed blocks without name attributes)
  document.addEventListener('DOMContentLoaded', function(){
    reindex();
  });
})();