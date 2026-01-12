// JS per metabox servizi: apre il media frame e popola il campo hidden + anteprima
jQuery(function($){
  // open media frame for phase icons
  $('body').on('click', '.service-select-icon', function(e){
    e.preventDefault();
    var target = $(this).data('target'); // numero i
    var inputId = 'service_phase_' + target + '_icon_id';
    var previewId = 'service_phase_' + target + '_icon_preview';

    var frame = wp.media({
      title: 'Seleziona icona',
      button: { text: 'Usa immagine' },
      multiple: false
    });

    frame.on('select', function(){
      var attachment = frame.state().get('selection').first().toJSON();
      if ( attachment && attachment.id ) {
        $('#' + inputId).val( attachment.id );
        $('#' + previewId).html('<img src="' + (attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url) + '" style="max-width:72px;height:auto;display:block;border-radius:8px;" />');
      }
    });

    frame.open();
  });

  // remove phase icon
  $('body').on('click', '.service-remove-icon', function(e){
    e.preventDefault();
    var target = $(this).data('target');
    var inputId = 'service_phase_' + target + '_icon_id';
    var previewId = 'service_phase_' + target + '_icon_preview';
    $('#' + inputId).val('');
    $('#' + previewId).html('');
  });

  // full image (main image) select
  $('body').on('click', '#ab-select-full-image', function(e){
    e.preventDefault();
    var frame = wp.media({
      title: 'Seleziona immagine full',
      button: { text: 'Usa immagine' },
      multiple: false
    });

    frame.on('select', function(){
      var attachment = frame.state().get('selection').first().toJSON();
      if ( attachment && attachment.id ) {
        $('#service_full_image_id').val( attachment.id );
        $('#service_full_image_preview').html('<img src="' + (attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url) + '" style="max-width:240px;height:auto;display:block;border-radius:8px;" />');
      }
    });

    frame.open();
  });

  // remove full image
  $('body').on('click', '#ab-remove-full-image', function(e){
    e.preventDefault();
    $('#service_full_image_id').val('');
    $('#service_full_image_preview').html('');
  });
});