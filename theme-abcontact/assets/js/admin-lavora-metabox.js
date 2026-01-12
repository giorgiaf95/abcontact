(function($){
  var frame;

  function openMedia(frameTitle, targetInput, previewWrap) {
    if (frame) frame.close();
    frame = wp.media({
      title: frameTitle,
      button: { text: 'Seleziona' },
      multiple: false
    });
    frame.on('select', function(){
      var attachment = frame.state().get('selection').first().toJSON();
      $(targetInput).val(attachment.id).trigger('change');
      var thumb = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
      $(previewWrap).html('<img src="' + thumb + '" class="ab-lc-img-preview" />');
    });
    frame.open();
  }

  /* =========== Repeater for positions =========== */

  function openMediaFor(button, targetInput, previewWrap) {
    if (frame) frame.close();
    frame = wp.media({
      title: 'Seleziona immagine',
      button: { text: 'Seleziona' },
      multiple: false
    });
    frame.on('select', function(){
      var attachment = frame.state().get('selection').first().toJSON();
      $(targetInput).val(attachment.id).trigger('change');
      var thumb = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
      $(previewWrap).html('<img src="' + thumb + '" class="ab-lc-img-preview" />');
    });
    frame.open();
  }

  function reindex($container) {
    $container.find('.lc-position-item').each(function(i){
      var $it = $(this);
      $it.attr('data-index', i);
      $it.find('[name]').each(function(){
        var name = $(this).attr('name');
        name = name.replace(/\[\d+\]/, '['+i+']');
        name = name.replace(/\[__index__\]/, '['+i+']');
        $(this).attr('name', name);
      });
      // update handle label
      var title = $it.find('input[name*="[title]"]').val();
      var label = title ? title : (i+1);
      $it.find('.handle .label').text(label);
    });
  }

  function bindItem($item, $container) {
    $item.find('.lc-position-remove').on('click', function(e){
      e.preventDefault();
      if ( confirm( (window.abLavoraPositions && window.abLavoraPositions.removeConfirm) ? window.abLavoraPositions.removeConfirm : 'Rimuovere questa posizione?' ) ) {
        $item.remove();
        reindex($container);
      }
    });

    $item.find('.lc-pos-image-button').on('click', function(e){
      e.preventDefault();
      var $btn = $(this);
      var $wrapper = $btn.closest('.lc-position-item');
      var $input = $wrapper.find('.lc-pos-image-id');
      var $preview = $wrapper.find('.lc-pos-image-preview');
      openMediaFor($btn, $input, $preview);
    });

    $item.find('.lc-pos-image-remove').on('click', function(e){
      e.preventDefault();
      var $btn = $(this);
      var $wrapper = $btn.closest('.lc-position-item');
      $wrapper.find('.lc-pos-image-id').val('');
      $wrapper.find('.lc-pos-image-preview').html('');
    });

    // when title changes, update handle label
    $item.find('input[name*="[title]"]').on('input', function(){
      var val = $(this).val();
      $item.find('.handle .label').text( val ? val : 'Nuova posizione' );
    });
  }

  function initRepeater(containerSelector) {
    var $container = $(containerSelector);
    if ( !$container.length ) return;

    var tpl = $('#lc-position-template').html();

    // bind existing
    $container.find('.lc-position-item').each(function(){
      bindItem($(this), $container);
    });

    // add
    $('#lc-add-position').on('click', function(e){
      e.preventDefault();
      var idx = $container.find('.lc-position-item').length;
      var html = tpl.replace(/__index__/g, idx);
      var $node = $(html);
      $container.append($node);
      bindItem($node, $container);
      reindex($container);
      // if sortable available, refresh
      if ( $container.find('.lc-position-item').length && $.fn.sortable ) {
        $container.sortable('refresh');
      }
    });

    // init sortable if available
    if ( $.fn.sortable ) {
      $container.sortable({
        handle: '.handle',
        items: '.lc-position-item',
        axis: 'y',
        update: function(){
          reindex($container);
        }
      });
    }
  }

  $(document).ready(function(){
    initRepeater('[data-repeater]');
  });

})(jQuery);