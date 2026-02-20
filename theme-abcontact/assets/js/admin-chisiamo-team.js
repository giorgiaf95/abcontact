(function($){
  'use strict';

  // admin-chisiamo-team.js

  // Open media frame for a specific input/preview
  function openMediaFor($input, $preview) {
    if ( typeof wp === 'undefined' || ! wp.media ) {
      alert( 'Media library non disponibile. Assicurati che wp_enqueue_media() sia chiamato.' );
      return;
    }

    var frame = wp.media({
      title: 'Seleziona foto membro',
      button: { text: 'Seleziona' },
      multiple: false
    });

    frame.on('select', function(){
      var attachment = frame.state().get('selection').first().toJSON();
      if ( ! attachment ) return;
      $input.val( attachment.id ).trigger('change');
      var thumb = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;
      $preview.html('<img src="'+ thumb +'" class="ab-team-preview-img" style="max-width:140px;border-radius:999px;display:block;margin-top:8px;" />');
    });

    frame.open();
  }

  function replaceIndexInName(name, idx) {
    return name.replace(/\[(?:__index__|\d+)\]/, '[' + idx + ']');
  }

  // Re-index names/indices after add/remove/sort
  function reindex($container) {
    $container.find('.ab-team-item').each(function(i){
      var $it = $(this);
      $it.attr('data-index', i);

      $it.find('[name]').each(function(){
        var $el = $(this);
        var name = $el.attr('name');
        if ( ! name ) return;
        var newName = replaceIndexInName(name, i);
        $el.attr('name', newName);
      });

      var title = $it.find('input[name*="[name]"]').val() || '';
      $it.find('.ab-team-handle .label').text( title ? title : (i+1) );
    });
  }

  function bindItem($item, $container) {
    if (!$item || !$item.length) return;

    if ($item.data('tcBound')) return;
    $item.data('tcBound', 1);

    $item.on('click', '.ab-team-remove', function(e){
      e.preventDefault();
      if ( confirm( (window.abChiSiamoTeam && window.abChiSiamoTeam.removeConfirm) ? window.abChiSiamoTeam.removeConfirm : 'Rimuovere questo membro?' ) ) {
        $item.remove();
        reindex($container);
        $container.trigger('ab:itemsChanged');
      }
    });

    $item.on('click', '.ab-team-image-remove', function(e){
      e.preventDefault();
      var $wrap = $(this).closest('.ab-team-item');
      $wrap.find('.ab-team-image-id').val('');
      $wrap.find('.ab-team-preview').empty();
    });

    $item.find('input[name*="[name]"]').on('input', function(){
      var val = $(this).val();
      $item.find('.ab-team-handle .label').text( val ? val : 'Nuovo membro' );
    });
  }

  // repeater container
  function initRepeater(containerSelector) {
    var $container = $(containerSelector);
    if ( !$container.length ) return;

    if ($container.data('repeaterInit')) return;
    $container.data('repeaterInit', 1);

    var tplNode = $('#ab-team-template');
    var tpl = tplNode.length ? tplNode.html() : '';
    if (!tpl) {
      return;
    }

    $container.find('.ab-team-item').each(function(){
      bindItem($(this), $container);
    });
    reindex($container);

    // Add new item
    $(document).on('click', '#ab-team-add', function(e){
      e.preventDefault();
      var idx = $container.find('.ab-team-item').length;
      var html = tpl.replace(/\[__index__\]/g, '['+idx+']').replace(/__index__/g, idx);
      var $node = $(html);
      $container.append($node);
      bindItem($node, $container);
      reindex($container);
      $container.trigger('ab:itemsChanged');
      if ( $.fn.sortable ) {
        try { $container.sortable('refresh'); } catch (err) { /* ignore */ }
      }
    });

    // Delegated image select
    $(document).on('click', '.ab-team-image-select', function(e){
      e.preventDefault();
      var $btn = $(this);
      var $wrapper = $btn.closest('.ab-team-item');
      var $input = $wrapper.find('.ab-team-image-id');
      var $preview = $wrapper.find('.ab-team-preview');
      if ( $input.length ) openMediaFor($input, $preview);
    });

    // Delegated remove-link
    $(document).on('click', '.ab-service-remove-link, .ab-team-remove', function(e){
    });

    if ( $.fn.sortable ) {
      $container.sortable({
        handle: '.ab-team-handle',
        items: '.ab-team-item',
        axis: 'y',
        update: function(){
          reindex($container);
          $container.trigger('ab:itemsChanged');
        }
      });
    }
  }

  $(document).ready(function(){
    initRepeater('[data-repeater]');
  });

  // Optional: allow re-init on demand (useful if metabox HTML is replaced via AJAX)
  window.abcontactTeamAdmin = {
    init: function(sel){ initRepeater(sel || '[data-repeater]'); },
    reindexAll: function(sel){ $(sel || '[data-repeater]').each(function(){ reindex($(this)); }); }
  };

})(jQuery);