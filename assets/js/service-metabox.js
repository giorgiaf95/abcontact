(function($){
  $(document).ready(function(){

    // Rende sicuro l'ID per TinyMCE/wp.editor
    function makeEditorId(idx){
      return 'service_additional_' + idx + '_body';
    }

    // Initialize editor on a textarea id (best-effort: wp.editor or tinyMCE fallback)
    function initEditorFor(textareaId, nameAttr){
      if ( typeof wp !== 'undefined' && wp.editor && wp.editor.initialize ){
        wp.editor.initialize(textareaId, {
          tinymce: {
            wpautop: true,
            toolbar1: 'bold italic | bullist numlist | link unlink | removeformat'
          },
          quicktags: true,
          textarea_name: nameAttr
        });
      } else if ( typeof tinyMCE !== 'undefined' ){
        tinyMCE.init({
          selector: '#' + textareaId,
          menubar: false,
          toolbar: 'bold italic | bullist numlist | link unlink | removeformat'
        });
      } else {
        // no editor available; leave textarea as-is
      }
    }

    // Remove editor instance if present
    function removeEditor(textareaId){
      if ( typeof wp !== 'undefined' && wp.editor && wp.editor.remove ){
        try { wp.editor.remove(textareaId); } catch(e){ /* ignore */ }
      }
      if ( typeof tinyMCE !== 'undefined' && tinyMCE.get(textareaId) ){
        try { tinyMCE.get(textareaId).remove(); } catch(e){ /* ignore */ }
      }
    }

    var $repeater = $('#service-additional-repeater');
    var tplHtml = $('#service-add-template').html();

    function reindex(){
      $repeater.find('.service-add-item').each(function(i){
        var $item = $(this);
        $item.attr('data-index', i);
        $item.find('[name]').each(function(){
          var name = $(this).attr('name');
          name = name.replace(/\[\d+\]/, '['+i+']');
          name = name.replace(/\[__index__\]/, '['+i+']');
          $(this).attr('name', name);
        });
        // update ids where needed
        $item.find('textarea').each(function(){
          var $ta = $(this);
          var id = $ta.attr('id');
          if ( id && (id.indexOf('__index__') !== -1 || id.indexOf('___index___') !== -1) ){
            $ta.attr('id', id.replace('__index__', i).replace('___index___', i));
          } else {
            // ensure consistent id pattern
            var expected = makeEditorId(i);
            if ( $ta.attr('name') && $ta.attr('name').indexOf('service_additional['+i+'][body]') !== -1 ){
              $ta.attr('id', expected);
            }
          }
        });
      });
    }

    // Remove
    $repeater.on('click', '.service-add-remove', function(e){
      e.preventDefault();
      var $item = $(this).closest('.service-add-item');
      $item.find('textarea').each(function(){
        removeEditor($(this).attr('id'));
      });
      $item.remove();
      reindex();
    });

    // Add new
    $('#service-add-new').on('click', function(e){
      e.preventDefault();
      var idx = $repeater.find('.service-add-item').length;
      var html = tplHtml.replace(/__index__/g, idx).replace(/___index___/g, idx);
      $repeater.find('> p').first().before(html);
      reindex();

      // init editor for the new textarea
      var textareaId = makeEditorId(idx);
      var textarea = document.getElementById(textareaId);
      if ( textarea ){
        initEditorFor(textareaId, 'service_additional['+idx+'][body]');
      }
    });

    // On page load: existing server-side wp_editor() already inits editors for existing blocks.
    // But to be safe: for any textarea lacking TinyMCE we can initialize quicktags if needed.
  });
})(jQuery);

(function($){
  // Fallback robusto per il pulsante "Seleziona immagine full"
  $(document).ready(function(){
    // se il pulsante non apre nulla (o il frame non è stato creato), questo garantisce il comportamento
    $('#ab-select-full-image').off('click.ab_full_image_fallback').on('click.ab_full_image_fallback', function(e){
      e.preventDefault();
      if ( typeof wp === 'undefined' || typeof wp.media === 'undefined' ) {
        console.warn('wp.media non disponibile');
        return;
      }

      // riusa eventuale frame esistente
      if ( ! wp.media.frames || ! wp.media.frames.ab_full_image_frame ) {
        wp.media.frames.ab_full_image_frame = wp.media({
          title: 'Seleziona immagine full',
          button: { text: 'Usa immagine' },
          library: { type: 'image' },
          multiple: false
        });

        wp.media.frames.ab_full_image_frame.on('select', function(){
          var attachment = wp.media.frames.ab_full_image_frame.state().get('selection').first().toJSON();
          if ( ! attachment ) return;
          var id = attachment.id || '';
          var url = '';

          // scegli l'immagine preferita dalle sizes, altrimenti usa attachment.url
          if ( attachment.sizes ) {
            url = (attachment.sizes.medium && attachment.sizes.medium.url) ||
                  (attachment.sizes.large && attachment.sizes.large.url) ||
                  (attachment.sizes.full && attachment.sizes.full.url) || attachment.url || '';
          } else {
            url = attachment.url || '';
          }

          if ( id ) {
            $('#service_full_image_id').val( id.toString() );
          }
          if ( url ) {
            $('#service_full_image_preview').html('<img src="' + url + '" alt="" style="max-width:240px;height:auto;display:block;border-radius:8px;">');
          } else {
            $('#service_full_image_preview').html('');
          }
        });
      }

      // apri sempre il frame (riutilizza se già creato)
      try {
        wp.media.frames.ab_full_image_frame.open();
      } catch (err) {
        // se qualcosa va storto, ricreiamo e riapriamo
        delete wp.media.frames.ab_full_image_frame;
        $('#ab-select-full-image').trigger('click.ab_full_image_fallback');
      }
    });
  });
})(jQuery);