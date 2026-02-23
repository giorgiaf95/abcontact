<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const ABCONTACT_HOME_SERVICES_OPTION = 'abcontact_home_services_settings';

function abcontact_home_services_defaults() {
    return array(
        'section_title'    => 'I Nostri Servizi',
        'section_subtitle' => "Soluzioni complete per l'efficienza energetica di privati e aziende",
        'items'            => array(),
    );
}

function abcontact_home_services_get_settings() {
    $saved = get_option( ABCONTACT_HOME_SERVICES_OPTION, array() );
    if ( ! is_array( $saved ) ) {
        $saved = array();
    }
    $merged = array_merge( abcontact_home_services_defaults(), $saved );

    if ( empty( $merged['items'] ) || ! is_array( $merged['items'] ) ) {
        $merged['items'] = array();
    }
    return $merged;
}

function abcontact_home_services_sanitize( $input ) {
    $out = abcontact_home_services_defaults();

    if ( ! is_array( $input ) ) {
        return $out;
    }

    $out['section_title']    = isset( $input['section_title'] ) ? sanitize_text_field( $input['section_title'] ) : $out['section_title'];
    $out['section_subtitle'] = isset( $input['section_subtitle'] ) ? sanitize_text_field( $input['section_subtitle'] ) : $out['section_subtitle'];

    $out['items'] = array();

    if ( ! empty( $input['items'] ) && is_array( $input['items'] ) ) {
        foreach ( $input['items'] as $row ) {
            if ( ! is_array( $row ) ) continue;

            $title    = isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '';
            $subtitle = isset( $row['subtitle'] ) ? sanitize_text_field( $row['subtitle'] ) : '';
            $icon_id  = isset( $row['icon_id'] ) ? absint( $row['icon_id'] ) : 0;

            $pills_out = array();
            if ( ! empty( $row['pills'] ) && is_array( $row['pills'] ) ) {
                foreach ( $row['pills'] as $p ) {
                    if ( ! is_array( $p ) ) continue;

                    $pl = isset( $p['label'] ) ? sanitize_text_field( $p['label'] ) : '';
                    $pu = isset( $p['url'] ) ? esc_url_raw( $p['url'] ) : '';

                    if ( $pl === '' && $pu === '' ) continue;
                    if ( $pu === '' ) continue;

                    $pills_out[] = array(
                        'label' => $pl !== '' ? $pl : __( 'Link', 'theme-abcontact' ),
                        'url'   => $pu,
                    );
                }
            }

            if ( $title === '' && $subtitle === '' && ! $icon_id && empty( $pills_out ) ) {
                continue;
            }

            $out['items'][] = array(
                'title'    => $title,
                'subtitle' => $subtitle,
                'icon_id'  => $icon_id,
                'pills'    => $pills_out,
            );
        }
    }

    return $out;
}

add_action( 'admin_menu', function () {
    add_menu_page(
        __( 'Home – Servizi', 'theme-abcontact' ),
        __( 'Home – Servizi', 'theme-abcontact' ),
        'manage_options',
        'abcontact-home-services',
        'abcontact_home_services_render_admin_page',
        'dashicons-screenoptions',
        58
    );
} );

add_action( 'admin_init', function () {
    register_setting(
        'abcontact_home_services_group',
        ABCONTACT_HOME_SERVICES_OPTION,
        array(
            'type'              => 'array',
            'sanitize_callback' => 'abcontact_home_services_sanitize',
            'default'           => abcontact_home_services_defaults(),
        )
    );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( $hook !== 'toplevel_page_abcontact-home-services' ) {
        return;
    }

    wp_enqueue_media();

    // Ensure jQuery UI Sortable is fully available
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-widget' );
    wp_enqueue_script( 'jquery-ui-mouse' );
    wp_enqueue_script( 'jquery-ui-sortable' );
} );

function abcontact_home_services_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $opt   = abcontact_home_services_get_settings();
    $items = $opt['items'];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Home – Sezione Servizi (tema)', 'theme-abcontact' ); ?></h1>
        <p class="description"><?php esc_html_e( 'Configura le voci servizi in home. Ogni voce ha icona, titolo, sottotitolo e bottoni (pill) ripetibili. Puoi riordinare con drag & drop.', 'theme-abcontact' ); ?></p>

        <form method="post" action="options.php">
            <?php settings_fields( 'abcontact_home_services_group' ); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="ab-section-title"><?php esc_html_e( 'Titolo sezione', 'theme-abcontact' ); ?></label></th>
                    <td>
                        <input id="ab-section-title" type="text" class="regular-text"
                               name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[section_title]"
                               value="<?php echo esc_attr( $opt['section_title'] ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ab-section-subtitle"><?php esc_html_e( 'Sottotitolo sezione', 'theme-abcontact' ); ?></label></th>
                    <td>
                        <input id="ab-section-subtitle" type="text" class="large-text"
                               name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[section_subtitle]"
                               value="<?php echo esc_attr( $opt['section_subtitle'] ); ?>">
                    </td>
                </tr>
            </table>

            <hr>

            <h2><?php esc_html_e( 'Servizi (repeater)', 'theme-abcontact' ); ?></h2>

            <style>
                .ab-repeater { display:flex; flex-direction:column; gap:14px; margin-top: 14px; }
                .ab-service { border:1px solid #dcdcde; background:#fff; border-radius:12px; padding:14px; }
                .ab-service__head { display:flex; justify-content:space-between; align-items:center; gap:12px; }
                .ab-service__title { margin:0; font-size:14px; display:flex; align-items:center; gap:10px; }

                .ab-drag-handle{
                    display:inline-flex;
                    align-items:center;
                    justify-content:center;
                    width:34px;
                    height:34px;
                    border-radius:10px;
                    border:1px solid rgba(16,24,40,0.12);
                    background:#f6f7f7;
                    cursor:grab;
                    user-select:none;
                    font-size:16px;
                    line-height:1;
                }
                .ab-drag-handle:active{ cursor:grabbing; }

                .ab-service__grid { display:grid; grid-template-columns: 140px 1fr 1fr; gap:12px; align-items:start; margin-top: 12px; }
                .ab-icon-preview { width:72px; height:72px; border-radius:14px; border:1px solid rgba(16,24,40,0.10); background:#f6f7f7; display:flex; align-items:center; justify-content:center; overflow:hidden; }
                .ab-icon-preview img { width:100%; height:100%; object-fit:contain; display:block; }
                .ab-muted { color:#646970; font-size:12px; margin-top:6px; }
                .ab-pills { margin-top: 14px; padding-top: 12px; border-top: 1px dashed #dcdcde; }
                .ab-pill-row { display:grid; grid-template-columns: 1fr 2fr 90px; gap:10px; align-items:start; margin-top: 10px; }
                .ab-inline-actions { display:flex; gap:10px; align-items:center; }
                .ab-sort-placeholder{
                    border:2px dashed #8c8f94;
                    border-radius:12px;
                    background: #f6f7f7;
                    height: 64px;
                }
            </style>

            <div class="ab-repeater" id="ab-services-repeater">
                <?php foreach ( $items as $idx => $row ) :
                    $icon_id = ! empty( $row['icon_id'] ) ? (int) $row['icon_id'] : 0;
                    $icon_url = $icon_id ? wp_get_attachment_image_url( $icon_id, 'thumbnail' ) : '';
                    $pills = ( ! empty( $row['pills'] ) && is_array( $row['pills'] ) ) ? $row['pills'] : array();
                ?>
                <div class="ab-service" data-service data-index="<?php echo (int) $idx; ?>">
                    <div class="ab-service__head">
                        <p class="ab-service__title">
                            <span class="ab-drag-handle" title="<?php echo esc_attr__( 'Trascina per riordinare', 'theme-abcontact' ); ?>" aria-label="<?php echo esc_attr__( 'Trascina per riordinare', 'theme-abcontact' ); ?>">☰</span>
                            <strong><?php echo esc_html__( 'Servizio', 'theme-abcontact' ); ?></strong>
                            <span data-service-number>#<?php echo (int) ( $idx + 1 ); ?></span>
                        </p>
                        <button type="button" class="button button-link-delete" data-remove-service><?php esc_html_e( 'Elimina servizio', 'theme-abcontact' ); ?></button>
                    </div>

                    <div class="ab-service__grid">
                        <div>
                            <div class="ab-icon-preview" data-icon-preview>
                                <?php if ( $icon_url ) : ?>
                                    <img src="<?php echo esc_url( $icon_url ); ?>" alt="">
                                <?php endif; ?>
                            </div>
                            <input type="hidden" data-icon-id
                                   name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[items][<?php echo (int) $idx; ?>][icon_id]"
                                   value="<?php echo esc_attr( $icon_id ); ?>">
                            <div class="ab-muted"><?php esc_html_e( 'Icona (Media Library)', 'theme-abcontact' ); ?></div>
                            <div class="ab-inline-actions" style="margin-top:8px;">
                                <button type="button" class="button" data-pick-icon><?php esc_html_e( 'Scegli', 'theme-abcontact' ); ?></button>
                                <button type="button" class="button" data-remove-icon><?php esc_html_e( 'Rimuovi', 'theme-abcontact' ); ?></button>
                            </div>
                        </div>

                        <div>
                            <label><strong><?php esc_html_e( 'Titolo', 'theme-abcontact' ); ?></strong></label>
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[items][<?php echo (int) $idx; ?>][title]"
                                   value="<?php echo esc_attr( $row['title'] ?? '' ); ?>">
                        </div>

                        <div>
                            <label><strong><?php esc_html_e( 'Sottotitolo', 'theme-abcontact' ); ?></strong></label>
                            <input type="text" class="large-text"
                                   name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[items][<?php echo (int) $idx; ?>][subtitle]"
                                   value="<?php echo esc_attr( $row['subtitle'] ?? '' ); ?>">
                        </div>
                    </div>

                    <div class="ab-pills" data-pills>
                        <p style="margin:0;"><strong><?php esc_html_e( 'Bottoni (pill)', 'theme-abcontact' ); ?></strong></p>

                        <div data-pills-list>
                            <?php foreach ( $pills as $pidx => $p ) : ?>
                                <div class="ab-pill-row" data-pill-row>
                                    <div>
                                        <label class="ab-muted"><?php esc_html_e( 'Label', 'theme-abcontact' ); ?></label>
                                        <input type="text" class="regular-text"
                                            name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[items][<?php echo (int) $idx; ?>][pills][<?php echo (int) $pidx; ?>][label]"
                                            value="<?php echo esc_attr( $p['label'] ?? '' ); ?>">
                                    </div>
                                    <div>
                                        <label class="ab-muted"><?php esc_html_e( 'URL', 'theme-abcontact' ); ?></label>
                                        <input type="url" class="large-text"
                                            name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[items][<?php echo (int) $idx; ?>][pills][<?php echo (int) $pidx; ?>][url]"
                                            value="<?php echo esc_attr( $p['url'] ?? '' ); ?>"
                                            placeholder="https://...">
                                    </div>
                                    <div style="padding-top:18px;">
                                        <button type="button" class="button button-link-delete" data-remove-pill><?php esc_html_e( 'Elimina', 'theme-abcontact' ); ?></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <p style="margin:12px 0 0;">
                            <button type="button" class="button" data-add-pill><?php esc_html_e( 'Aggiungi bottone', 'theme-abcontact' ); ?></button>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <p style="margin-top:14px;">
                <button type="button" class="button button-secondary" id="ab-add-service"><?php esc_html_e( 'Aggiungi servizio', 'theme-abcontact' ); ?></button>
            </p>

            <?php submit_button( __( 'Salva', 'theme-abcontact' ) ); ?>
        </form>
    </div>

    <script>
    (function($){
        var OPT = '<?php echo esc_js( ABCONTACT_HOME_SERVICES_OPTION ); ?>';
        var $repeater = $('#ab-services-repeater');
        var $addServiceBtn = $('#ab-add-service');

        if (!$repeater.length || !$addServiceBtn.length) return;

        function pickMedia(onSelect){
            if (!window.wp || !wp.media) return;
            var frame = wp.media({
                title: '<?php echo esc_js( __( 'Seleziona immagine', 'theme-abcontact' ) ); ?>',
                button: { text: '<?php echo esc_js( __( 'Usa questa immagine', 'theme-abcontact' ) ); ?>' },
                multiple: false
            });
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                onSelect(att);
            });
            frame.open();
        }

        function serviceTemplate(sIdx){
            return '' +
            '<div class="ab-service" data-service data-index="'+sIdx+'">' +
              '<div class="ab-service__head">' +
                '<p class="ab-service__title">' +
                  '<span class="ab-drag-handle" title="<?php echo esc_js( __( 'Trascina per riordinare', 'theme-abcontact' ) ); ?>" aria-label="<?php echo esc_js( __( 'Trascina per riordinare', 'theme-abcontact' ) ); ?>">☰</span>' +
                  '<strong><?php echo esc_js( __( 'Servizio', 'theme-abcontact' ) ); ?></strong> ' +
                  '<span data-service-number>#' + (sIdx+1) + '</span>' +
                '</p>' +
                '<button type="button" class="button button-link-delete" data-remove-service><?php echo esc_js( __( 'Elimina servizio', 'theme-abcontact' ) ); ?></button>' +
              '</div>' +

              '<div class="ab-service__grid">' +
                '<div>' +
                  '<div class="ab-icon-preview" data-icon-preview></div>' +
                  '<input type="hidden" data-icon-id name="'+OPT+'[items]['+sIdx+'][icon_id]" value="0">' +
                  '<div class="ab-muted"><?php echo esc_js( __( 'Icona (Media Library)', 'theme-abcontact' ) ); ?></div>' +
                  '<div class="ab-inline-actions" style="margin-top:8px;">' +
                    '<button type="button" class="button" data-pick-icon><?php echo esc_js( __( 'Scegli', 'theme-abcontact' ) ); ?></button>' +
                    '<button type="button" class="button" data-remove-icon><?php echo esc_js( __( 'Rimuovi', 'theme-abcontact' ) ); ?></button>' +
                  '</div>' +
                '</div>' +
                '<div>' +
                  '<label><strong><?php echo esc_js( __( 'Titolo', 'theme-abcontact' ) ); ?></strong></label>' +
                  '<input type="text" class="regular-text" name="'+OPT+'[items]['+sIdx+'][title]" value="">' +
                '</div>' +
                '<div>' +
                  '<label><strong><?php echo esc_js( __( 'Sottotitolo', 'theme-abcontact' ) ); ?></strong></label>' +
                  '<input type="text" class="large-text" name="'+OPT+'[items]['+sIdx+'][subtitle]" value="">' +
                '</div>' +
              '</div>' +

              '<div class="ab-pills" data-pills>' +
                '<p style="margin:0;"><strong><?php echo esc_js( __( 'Bottoni (pill)', 'theme-abcontact' ) ); ?></strong></p>' +
                '<div data-pills-list></div>' +
                '<p style="margin:12px 0 0;">' +
                  '<button type="button" class="button" data-add-pill><?php echo esc_js( __( 'Aggiungi bottone', 'theme-abcontact' ) ); ?></button>' +
                '</p>' +
              '</div>' +
            '</div>';
        }

        function pillTemplate(sIdx, pIdx){
            return '' +
            '<div class="ab-pill-row" data-pill-row>' +
              '<div>' +
                '<label class="ab-muted"><?php echo esc_js( __( 'Label', 'theme-abcontact' ) ); ?></label>' +
                '<input type="text" class="regular-text" name="'+OPT+'[items]['+sIdx+'][pills]['+pIdx+'][label]" value="">' +
              '</div>' +
              '<div>' +
                '<label class="ab-muted"><?php echo esc_js( __( 'URL', 'theme-abcontact' ) ); ?></label>' +
                '<input type="url" class="large-text" name="'+OPT+'[items]['+sIdx+'][pills]['+pIdx+'][url]" value="" placeholder="https://...">' +
              '</div>' +
              '<div style="padding-top:18px;">' +
                '<button type="button" class="button button-link-delete" data-remove-pill><?php echo esc_js( __( 'Elimina', 'theme-abcontact' ) ); ?></button>' +
              '</div>' +
            '</div>';
        }

        function reindexServices(){
            $repeater.find('[data-service]').each(function(newIdx){
                var $service = $(this);
                $service.attr('data-index', newIdx);
                $service.find('[data-service-number]').text('#' + (newIdx + 1));

                // Update ALL name attributes: items[old] -> items[newIdx]
                $service.find('[name]').each(function(){
                    var $el = $(this);
                    var name = $el.attr('name');
                    if (!name) return;

                    // Replace only the first occurrence of [items][<num>]
                    name = name.replace(new RegExp('\\[' + OPT.replace(/[.*+?^${}()|[\\]\\\\]/g, '\\$&') + '\\]\\[items\\]\\[\\d+\\]'), '[' + OPT + '][items][' + newIdx + ']');
                    $el.attr('name', name);
                });
            });
        }

if ($.fn.sortable) {
  $repeater.sortable({
    handle: '.ab-drag-handle',
    placeholder: 'ab-sort-placeholder',
    items: '[data-service]',
    tolerance: 'pointer',
    update: function(){
      reindexServices();
    }
  });
} else {
  if (window.console && console.warn) {
    console.warn('abcontact: jQuery UI Sortable not available on this page.');
  }
}

        // Click handlers (delegated)
        $repeater.on('click', '[data-remove-service]', function(e){
            e.preventDefault();
            $(this).closest('[data-service]').remove();
            reindexServices();
        });

        $repeater.on('click', '[data-pick-icon]', function(e){
            e.preventDefault();
            var $service = $(this).closest('[data-service]');
            pickMedia(function(att){
                $service.find('[data-icon-id]').val(att.id);
                var url = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
                $service.find('[data-icon-preview]').html('<img src="'+url+'" alt="">');
            });
        });

        $repeater.on('click', '[data-remove-icon]', function(e){
            e.preventDefault();
            var $service = $(this).closest('[data-service]');
            $service.find('[data-icon-id]').val(0);
            $service.find('[data-icon-preview]').empty();
        });

        $repeater.on('click', '[data-add-pill]', function(e){
            e.preventDefault();
            var $service = $(this).closest('[data-service]');
            var sIdx = parseInt($service.attr('data-index') || '0', 10);
            var $list = $service.find('[data-pills-list]').first();
            var pIdx = $list.find('[data-pill-row]').length;
            $list.append(pillTemplate(sIdx, pIdx));
        });

        $repeater.on('click', '[data-remove-pill]', function(e){
            e.preventDefault();
            $(this).closest('[data-pill-row]').remove();
        });

        $addServiceBtn.on('click', function(e){
            e.preventDefault();
            var idx = $repeater.find('[data-service]').length;
            $repeater.append(serviceTemplate(idx));
            reindexServices();
        });

    })(jQuery);
    </script>
    <?php
}