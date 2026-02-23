<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const ABCONTACT_HOME_SERVICES_OPTION = 'abcontact_home_services_settings';

function abcontact_home_services_defaults_v4() {
    return array(
        // Section (prototype: eyebrow + title + subtitle)
        'eyebrow'  => 'I NOSTRI SERVIZI',
        'title'    => 'Soluzioni per ogni esigenza',
        'subtitle' => "Dall'analisi delle tue bollette all'installazione di impianti fotovoltaici, ti accompagniamo verso l'efficienza energetica.",

        // Back-compat (old keys)
        'section_title'    => '',
        'section_subtitle' => '',

        'groups' => array(
            'privati' => array(
                'title'    => 'Per Privati',
                'subtitle' => 'Rendi la tua casa più efficiente',
                'icon_id'  => 0,
                'items'    => array(),
            ),
            'aziende' => array(
                'title'    => 'Per Aziende',
                'subtitle' => 'Consulenza su misura per il tuo business',
                'icon_id'  => 0,
                'items'    => array(),
            ),
        ),
    );
}

function abcontact_home_services_get_settings_v4() {
    $saved = get_option( ABCONTACT_HOME_SERVICES_OPTION, array() );
    if ( ! is_array( $saved ) ) $saved = array();

    $def = abcontact_home_services_defaults_v4();
    $merged = array_merge( $def, $saved );

    // Back-compat: if new keys empty, fill from old ones
    if ( empty( $merged['eyebrow'] ) && ! empty( $merged['section_title'] ) ) {
        $merged['eyebrow'] = $merged['section_title'];
    }
    if ( empty( $merged['title'] ) && ! empty( $merged['section_subtitle'] ) ) {
        $merged['title'] = $merged['section_subtitle'];
    }

    if ( empty( $merged['groups'] ) || ! is_array( $merged['groups'] ) ) {
        $merged['groups'] = $def['groups'];
    }

    foreach ( array( 'privati', 'aziende' ) as $k ) {
        if ( empty( $merged['groups'][ $k ] ) || ! is_array( $merged['groups'][ $k ] ) ) {
            $merged['groups'][ $k ] = $def['groups'][ $k ];
            continue;
        }
        $merged['groups'][ $k ] = array_merge( $def['groups'][ $k ], $merged['groups'][ $k ] );
        if ( empty( $merged['groups'][ $k ]['items'] ) || ! is_array( $merged['groups'][ $k ]['items'] ) ) {
            $merged['groups'][ $k ]['items'] = array();
        }
    }

    return $merged;
}

function abcontact_home_services_sanitize_v4( $input ) {
    $out = abcontact_home_services_defaults_v4();
    if ( ! is_array( $input ) ) return $out;

    $out['eyebrow']  = isset( $input['eyebrow'] ) ? sanitize_text_field( $input['eyebrow'] ) : $out['eyebrow'];
    $out['title']    = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : $out['title'];
    $out['subtitle'] = isset( $input['subtitle'] ) ? sanitize_text_field( $input['subtitle'] ) : $out['subtitle'];

    // keep old keys empty (we don't need them anymore but they won't hurt)
    $out['section_title']    = '';
    $out['section_subtitle'] = '';

    $groups_in = isset( $input['groups'] ) && is_array( $input['groups'] ) ? $input['groups'] : array();

    foreach ( array( 'privati', 'aziende' ) as $gk ) {
        $g = isset( $groups_in[ $gk ] ) && is_array( $groups_in[ $gk ] ) ? $groups_in[ $gk ] : array();

        $out['groups'][ $gk ]['title']    = isset( $g['title'] ) ? sanitize_text_field( $g['title'] ) : $out['groups'][ $gk ]['title'];
        $out['groups'][ $gk ]['subtitle'] = isset( $g['subtitle'] ) ? sanitize_text_field( $g['subtitle'] ) : $out['groups'][ $gk ]['subtitle'];
        $out['groups'][ $gk ]['icon_id']  = isset( $g['icon_id'] ) ? absint( $g['icon_id'] ) : 0;

        $out['groups'][ $gk ]['items'] = array();
        $items = isset( $g['items'] ) && is_array( $g['items'] ) ? $g['items'] : array();

        foreach ( $items as $row ) {
            if ( ! is_array( $row ) ) continue;

            $title    = isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '';
            $subtitle = isset( $row['subtitle'] ) ? sanitize_text_field( $row['subtitle'] ) : '';
            $url      = isset( $row['url'] ) ? esc_url_raw( $row['url'] ) : '';
            $icon_id  = isset( $row['icon_id'] ) ? absint( $row['icon_id'] ) : 0;

            // URL obbligatorio
            if ( $url === '' ) continue;
            if ( $title === '' && $subtitle === '' && ! $icon_id ) continue;

            $out['groups'][ $gk ]['items'][] = array(
                'title'    => $title,
                'subtitle' => $subtitle,
                'url'      => $url,
                'icon_id'  => $icon_id,
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
        'abcontact_home_services_render_admin_page_v4',
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
            'sanitize_callback' => 'abcontact_home_services_sanitize_v4',
            'default'           => abcontact_home_services_defaults_v4(),
        )
    );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( $hook !== 'toplevel_page_abcontact-home-services' ) return;

    wp_enqueue_media();
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-widget' );
    wp_enqueue_script( 'jquery-ui-mouse' );
    wp_enqueue_script( 'jquery-ui-sortable' );
} );

function abcontact_home_services_render_admin_page_v4() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $opt = abcontact_home_services_get_settings_v4();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Home – Sezione Servizi (tema)', 'theme-abcontact' ); ?></h1>

        <form method="post" action="options.php">
            <?php settings_fields( 'abcontact_home_services_group' ); ?>

            <h2><?php esc_html_e( 'Testi sezione (come prototipo)', 'theme-abcontact' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Nome sezione (eyebrow)', 'theme-abcontact' ); ?></label></th>
                    <td>
                        <input type="text" class="regular-text"
                               name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[eyebrow]"
                               value="<?php echo esc_attr( $opt['eyebrow'] ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Titolo', 'theme-abcontact' ); ?></label></th>
                    <td>
                        <input type="text" class="large-text"
                               name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[title]"
                               value="<?php echo esc_attr( $opt['title'] ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Sottotitolo / descrizione', 'theme-abcontact' ); ?></label></th>
                    <td>
                        <textarea class="large-text" rows="3"
                                  name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[subtitle]"><?php echo esc_textarea( $opt['subtitle'] ); ?></textarea>
                    </td>
                </tr>
            </table>

            <hr>

            <style>
              .ab-two-cols{display:grid; grid-template-columns:1fr 1fr; gap:18px;}
              @media (max-width: 1100px){ .ab-two-cols{grid-template-columns:1fr;} }
              .ab-group{border:1px solid #dcdcde; border-radius:12px; background:#fff; padding:14px;}
              .ab-group h2{margin:0 0 10px;}
              .ab-group-meta{display:grid; grid-template-columns:120px 1fr; gap:12px; align-items:start;}
              .ab-group-fields{display:grid; grid-template-columns:1fr; gap:12px;}
              .ab-square-preview{width:72px; height:72px; border-radius:14px; border:1px solid rgba(16,24,40,0.10); background:#f6f7f7; display:flex; align-items:center; justify-content:center; overflow:hidden;}
              .ab-square-preview img{width:100%; height:100%; object-fit:contain; display:block;}
              .ab-muted{color:#646970; font-size:12px; margin-top:6px;}
              .ab-inline-actions{display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:8px;}
              .ab-repeater{display:flex; flex-direction:column; gap:12px; margin-top:14px;}
              .ab-item{border:1px solid #dcdcde; border-radius:12px; background:#fff; padding:12px;}
              .ab-item-head{display:flex; justify-content:space-between; align-items:center; gap:12px;}
              .ab-item-title{margin:0; font-size:14px; display:flex; align-items:center; gap:10px;}
              .ab-drag-handle{display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:10px; border:1px solid rgba(16,24,40,0.12); background:#f6f7f7; cursor:grab; user-select:none; font-size:16px; line-height:1;}
              .ab-drag-handle:active{cursor:grabbing;}
              .ab-item-grid{display:grid; grid-template-columns:140px 1fr 1fr; gap:12px; align-items:start; margin-top:12px;}
              .ab-sort-placeholder{border:2px dashed #8c8f94; border-radius:12px; background:#f6f7f7; height: 64px;}
            </style>

            <div class="ab-two-cols">
              <?php foreach ( array('privati' => 'Privati', 'aziende' => 'Aziende') as $gk => $label ) :
                $g = $opt['groups'][$gk];
                $items = is_array($g['items']) ? $g['items'] : array();
                $g_icon_id = (int) ($g['icon_id'] ?? 0);
                $g_icon_url = $g_icon_id ? wp_get_attachment_image_url($g_icon_id, 'thumbnail') : '';
              ?>
                <section class="ab-group" data-group="<?php echo esc_attr($gk); ?>">
                  <h2><?php echo esc_html( $label ); ?></h2>

                  <div class="ab-group-meta">
                    <div>
                      <div class="ab-square-preview" data-group-icon-preview>
                        <?php if ($g_icon_url): ?><img src="<?php echo esc_url($g_icon_url); ?>" alt=""><?php endif; ?>
                      </div>
                      <input type="hidden" data-group-icon-id
                        name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[groups][<?php echo esc_attr($gk); ?>][icon_id]"
                        value="<?php echo esc_attr( $g_icon_id ); ?>">
                      <div class="ab-muted"><?php esc_html_e('Icona gruppo', 'theme-abcontact'); ?></div>
                      <div class="ab-inline-actions">
                        <button type="button" class="button" data-pick-group-icon><?php esc_html_e('Scegli', 'theme-abcontact'); ?></button>
                        <button type="button" class="button" data-remove-group-icon><?php esc_html_e('Rimuovi', 'theme-abcontact'); ?></button>
                      </div>
                    </div>

                    <div class="ab-group-fields">
                      <div>
                        <label><strong><?php esc_html_e('Titolo gruppo', 'theme-abcontact'); ?></strong></label>
                        <input type="text" class="regular-text"
                          name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[groups][<?php echo esc_attr($gk); ?>][title]"
                          value="<?php echo esc_attr( $g['title'] ?? '' ); ?>">
                      </div>

                      <div>
                        <label><strong><?php esc_html_e('Sottotitolo gruppo', 'theme-abcontact'); ?></strong></label>
                        <input type="text" class="large-text"
                          name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[groups][<?php echo esc_attr($gk); ?>][subtitle]"
                          value="<?php echo esc_attr( $g['subtitle'] ?? '' ); ?>">
                      </div>
                    </div>
                  </div>

                  <div class="ab-repeater" data-items-repeater>
                    <?php foreach ( $items as $idx => $row ) :
                      $icon_id = (int) ($row['icon_id'] ?? 0);
                      $icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'thumbnail') : '';
                    ?>
                      <div class="ab-item" data-item data-index="<?php echo (int)$idx; ?>">
                        <div class="ab-item-head">
                          <p class="ab-item-title">
                            <span class="ab-drag-handle" title="<?php echo esc_attr__('Trascina per riordinare','theme-abcontact'); ?>">☰</span>
                            <strong><?php esc_html_e('Voce', 'theme-abcontact'); ?></strong>
                            <span data-item-number>#<?php echo (int)($idx+1); ?></span>
                          </p>
                          <button type="button" class="button button-link-delete" data-remove-item><?php esc_html_e('Elimina', 'theme-abcontact'); ?></button>
                        </div>

                        <div class="ab-item-grid">
                          <div>
                            <div class="ab-square-preview" data-item-icon-preview>
                              <?php if ($icon_url): ?><img src="<?php echo esc_url($icon_url); ?>" alt=""><?php endif; ?>
                            </div>
                            <input type="hidden" data-item-icon-id
                              name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[groups][<?php echo esc_attr($gk); ?>][items][<?php echo (int)$idx; ?>][icon_id]"
                              value="<?php echo esc_attr($icon_id); ?>">
                            <div class="ab-muted"><?php esc_html_e('Icona voce', 'theme-abcontact'); ?></div>
                            <div class="ab-inline-actions">
                              <button type="button" class="button" data-pick-item-icon><?php esc_html_e('Scegli', 'theme-abcontact'); ?></button>
                              <button type="button" class="button" data-remove-item-icon><?php esc_html_e('Rimuovi', 'theme-abcontact'); ?></button>
                            </div>
                          </div>

                          <div>
                            <label><strong><?php esc_html_e('Titolo', 'theme-abcontact'); ?></strong></label>
                            <input type="text" class="regular-text"
                              name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[groups][<?php echo esc_attr($gk); ?>][items][<?php echo (int)$idx; ?>][title]"
                              value="<?php echo esc_attr($row['title'] ?? ''); ?>">

                            <label style="display:block; margin-top:10px;"><strong><?php esc_html_e('Link (obbligatorio)', 'theme-abcontact'); ?></strong></label>
                            <input type="url" class="large-text" required
                              name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[groups][<?php echo esc_attr($gk); ?>][items][<?php echo (int)$idx; ?>][url]"
                              value="<?php echo esc_attr($row['url'] ?? ''); ?>"
                              placeholder="https://...">
                          </div>

                          <div>
                            <label><strong><?php esc_html_e('Sottotitolo', 'theme-abcontact'); ?></strong></label>
                            <input type="text" class="large-text"
                              name="<?php echo esc_attr( ABCONTACT_HOME_SERVICES_OPTION ); ?>[groups][<?php echo esc_attr($gk); ?>][items][<?php echo (int)$idx; ?>][subtitle]"
                              value="<?php echo esc_attr($row['subtitle'] ?? ''); ?>">
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <p style="margin:12px 0 0;">
                    <button type="button" class="button button-secondary" data-add-item><?php esc_html_e('Aggiungi voce', 'theme-abcontact'); ?></button>
                  </p>
                </section>
              <?php endforeach; ?>
            </div>

            <?php submit_button( __( 'Salva', 'theme-abcontact' ) ); ?>
        </form>
    </div>

    <script>
    (function($){
      var OPT = '<?php echo esc_js( ABCONTACT_HOME_SERVICES_OPTION ); ?>';

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

      function escRe(s){ return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

      function itemTemplate(groupKey, idx){
        return '' +
          '<div class="ab-item" data-item data-index="'+idx+'">' +
            '<div class="ab-item-head">' +
              '<p class="ab-item-title">' +
                '<span class="ab-drag-handle" title="<?php echo esc_js( __( 'Trascina per riordinare', 'theme-abcontact' ) ); ?>">☰</span>' +
                '<strong><?php echo esc_js( __( 'Voce', 'theme-abcontact' ) ); ?></strong> <span data-item-number>#' + (idx+1) + '</span>' +
              '</p>' +
              '<button type="button" class="button button-link-delete" data-remove-item><?php echo esc_js( __( 'Elimina', 'theme-abcontact' ) ); ?></button>' +
            '</div>' +

            '<div class="ab-item-grid">' +
              '<div>' +
                '<div class="ab-square-preview" data-item-icon-preview></div>' +
                '<input type="hidden" data-item-icon-id name="'+OPT+'[groups]['+groupKey+'][items]['+idx+'][icon_id]" value="0">' +
                '<div class="ab-muted"><?php echo esc_js( __( 'Icona voce', 'theme-abcontact' ) ); ?></div>' +
                '<div class="ab-inline-actions">' +
                  '<button type="button" class="button" data-pick-item-icon><?php echo esc_js( __( 'Scegli', 'theme-abcontact' ) ); ?></button>' +
                  '<button type="button" class="button" data-remove-item-icon><?php echo esc_js( __( 'Rimuovi', 'theme-abcontact' ) ); ?></button>' +
                '</div>' +
              '</div>' +

              '<div>' +
                '<label><strong><?php echo esc_js( __( 'Titolo', 'theme-abcontact' ) ); ?></strong></label>' +
                '<input type="text" class="regular-text" name="'+OPT+'[groups]['+groupKey+'][items]['+idx+'][title]" value="">' +
                '<label style="display:block; margin-top:10px;"><strong><?php echo esc_js( __( 'Link (obbligatorio)', 'theme-abcontact' ) ); ?></strong></label>' +
                '<input type="url" class="large-text" required name="'+OPT+'[groups]['+groupKey+'][items]['+idx+'][url]" value="" placeholder="https://...">' +
              '</div>' +

              '<div>' +
                '<label><strong><?php echo esc_js( __( 'Sottotitolo', 'theme-abcontact' ) ); ?></strong></label>' +
                '<input type="text" class="large-text" name="'+OPT+'[groups]['+groupKey+'][items]['+idx+'][subtitle]" value="">' +
              '</div>' +
            '</div>' +
          '</div>';
      }

      function reindexGroup($group){
        var groupKey = $group.data('group');
        var $rep = $group.find('[data-items-repeater]').first();

        $rep.find('[data-item]').each(function(newIdx){
          var $item = $(this);
          $item.attr('data-index', newIdx);
          $item.find('[data-item-number]').text('#' + (newIdx + 1));

          $item.find('[name]').each(function(){
            var $el = $(this);
            var name = $el.attr('name');
            if (!name) return;

            var re = new RegExp('\\['+escRe(OPT)+'\\]\\[groups\\]\\['+escRe(groupKey)+'\\]\\[items\\]\\[\\d+\\]');
            name = name.replace(re, '['+OPT+'][groups]['+groupKey+'][items]['+newIdx+']');
            $el.attr('name', name);
          });
        });
      }

      $('.ab-group').each(function(){
        var $group = $(this);
        var $rep = $group.find('[data-items-repeater]').first();
        if ($.fn.sortable) {
          $rep.sortable({
            handle: '.ab-drag-handle',
            placeholder: 'ab-sort-placeholder',
            items: '[data-item]',
            tolerance: 'pointer',
            update: function(){ reindexGroup($group); }
          });
        }
      });

      $(document).on('click', '[data-pick-group-icon]', function(e){
        e.preventDefault();
        var $group = $(this).closest('[data-group]');
        pickMedia(function(att){
          var url = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
          $group.find('[data-group-icon-id]').val(att.id);
          $group.find('[data-group-icon-preview]').html('<img src="'+url+'" alt="">');
        });
      });

      $(document).on('click', '[data-remove-group-icon]', function(e){
        e.preventDefault();
        var $group = $(this).closest('[data-group]');
        $group.find('[data-group-icon-id]').val(0);
        $group.find('[data-group-icon-preview]').empty();
      });

      $(document).on('click', '[data-add-item]', function(e){
        e.preventDefault();
        var $group = $(this).closest('[data-group]');
        var groupKey = $group.data('group');
        var $rep = $group.find('[data-items-repeater]').first();
        var idx = $rep.find('[data-item]').length;
        $rep.append(itemTemplate(groupKey, idx));
        reindexGroup($group);
      });

      $(document).on('click', '[data-remove-item]', function(e){
        e.preventDefault();
        var $group = $(this).closest('[data-group]');
        $(this).closest('[data-item]').remove();
        reindexGroup($group);
      });

      $(document).on('click', '[data-pick-item-icon]', function(e){
        e.preventDefault();
        var $item = $(this).closest('[data-item]');
        pickMedia(function(att){
          var url = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
          $item.find('[data-item-icon-id]').val(att.id);
          $item.find('[data-item-icon-preview]').html('<img src="'+url+'" alt="">');
        });
      });

      $(document).on('click', '[data-remove-item-icon]', function(e){
        e.preventDefault();
        var $item = $(this).closest('[data-item]');
        $item.find('[data-item-icon-id]').val(0);
        $item.find('[data-item-icon-preview]').empty();
      });

    })(jQuery);
    </script>
    <?php
}