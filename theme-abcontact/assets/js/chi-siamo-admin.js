(function () {
  'use strict';
  if ( typeof jQuery === 'undefined' ) return;
  var $ = jQuery;

  function createFrame( multiple ) {
    return wp.media({
      title: abcontactChiSiamo.l10n.title || 'Seleziona immagine',
      library: { type: '' },
      button: { text: abcontactChiSiamo.l10n.button || 'Usa immagine' },
      multiple: !! multiple
    });
  }

  $(function(){
    if ( typeof wp === 'undefined' || !wp.media ) return;

    // Generic selector handlers
    // A / C images
    $('#cs_select_a_image').on('click', function (e) {
      e.preventDefault();
      var frame = createFrame(false);
      frame.on('select', function () {
        var att = frame.state().get('selection').first().toJSON();
        $('#cs_section_a_image_id').val(att.id);
        $('#cs_preview_a').html('<img src="'+ (att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url) +'" style="max-width:220px;height:auto;border-radius:8px;display:block;margin-bottom:6px;">');
      });
      frame.open();
    });

    $('#cs_select_c_image').on('click', function (e) {
      e.preventDefault();
      var frame = createFrame(false);
      frame.on('select', function () {
        var att = frame.state().get('selection').first().toJSON();
        $('#cs_section_c_image_id').val(att.id);
        $('#cs_preview_c').html('<img src="'+ (att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url) +'" style="max-width:220px;height:auto;border-radius:8px;display:block;margin-bottom:6px;">');
      });
      frame.open();
    });

    $('.cs-select-icon').on('click', function (e) {
      e.preventDefault();
      var idx = $(this).data('target');
      var frame = createFrame(false);
      frame.on('select', function () {
        var att = frame.state().get('selection').first().toJSON();
        $('#cs_value_' + idx + '_icon_id').val(att.id);
        $('#cs_preview_icon_' + idx).html('<img src="'+ (att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url) +'" style="width:72px;height:72px;object-fit:contain;border-radius:999px;">');
      });
      frame.open();
    });

    // Remove handlers
    $('#cs_remove_a_image').on('click', function(e){ e.preventDefault(); $('#cs_section_a_image_id').val(''); $('#cs_preview_a').empty(); });
    $('#cs_remove_c_image').on('click', function(e){ e.preventDefault(); $('#cs_section_c_image_id').val(''); $('#cs_preview_c').empty(); });
    $('.cs-remove-icon').on('click', function(e){ e.preventDefault(); var idx = $(this).data('target'); $('#cs_value_' + idx + '_icon_id').val(''); $('#cs_preview_icon_' + idx).empty(); });

  });
})();