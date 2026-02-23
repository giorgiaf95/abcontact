(function($){
  'use strict';

  function reindex($container) {
    $container.find('.lc-position-item').each(function(i){
      var $it = $(this);
      $it.attr('data-index', i);

      $it.find('[name]').each(function(){
        var name = $(this).attr('name') || '';
        name = name.replace(/\[\d+\]/, '['+i+']');
        name = name.replace(/\[__index__\]/, '['+i+']');
        $(this).attr('name', name);
      });

      var title = $it.find('input[name*="[title]"]').val();
      $it.find('.handle .label').text(title ? title : ('Posizione ' + (i+1)));
    });
  }

  function ensureId($item){
    var $id = $item.find('input[name*="[id]"]');
    if(!$id.length) return;

    if(!$id.val()){
      // simple random id
      var rand = Math.random().toString(36).slice(2, 10);
      $id.val('job_' + Date.now() + '_' + rand);
    }
  }

  function bindItem($item, $container) {
    ensureId($item);

    $item.find('.lc-position-remove').on('click', function(e){
      e.preventDefault();
      var msg = (window.abLavoraPositions && window.abLavoraPositions.removeConfirm) ? window.abLavoraPositions.removeConfirm : 'Rimuovere questa posizione?';
      if ( confirm(msg) ) {
        $item.remove();
        reindex($container);
      }
    });

    $item.find('input[name*="[title]"]').on('input', function(){
      var val = $(this).val();
      $item.find('.handle .label').text(val ? val : 'Nuova posizione');
    });
  }

  function initRepeater(containerSelector) {
    var $container = $(containerSelector);
    if (!$container.length) return;

    var tpl = $('#lc-position-template-v2').html();
    if(!tpl) return;

    $container.find('.lc-position-item').each(function(){
      bindItem($(this), $container);
    });

    $('#lc-add-position-v2').on('click', function(e){
      e.preventDefault();
      var idx = $container.find('.lc-position-item').length;
      var html = tpl.replace(/__index__/g, idx);
      var $node = $(html);
      $container.append($node);
      bindItem($node, $container);
      reindex($container);
      if ($container.find('.lc-position-item').length && $.fn.sortable) {
        $container.sortable('refresh');
      }
    });

    if ($.fn.sortable) {
      $container.sortable({
        handle: '.handle',
        items: '.lc-position-item',
        axis: 'y',
        update: function(){ reindex($container); }
      });
    }
  }

  $(document).ready(function(){
    initRepeater('[data-repeater="lc-v2"]');
  });

})(jQuery);