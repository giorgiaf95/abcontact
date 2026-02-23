<?php
// inc/metaboxes-services.php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Metabox "Contenuti Servizio"
 *
 * Modifiche:
 * - aggiunge "Sottotitolo Hero" (service_hero_subtitle)
 * - sostituisce "Come funziona (4 fasi)" con repeater (variabile) salvato in _service_how_steps
 * - migrazione automatica: se repeater vuoto e ci sono le 4 fasi legacy, le copia nel repeater al save
 * - recensioni NON toccate
 */

/* Register metabox */
function abcontact_register_service_metabox() {
    add_meta_box(
        'ab_service_fields',
        __( 'Contenuti Servizio', 'abcontact' ),
        'abcontact_render_service_metabox',
        'page',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'abcontact_register_service_metabox' );

/* Render metabox */
function abcontact_render_service_metabox( $post ) {
    $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
    $tpl = $tpl ? basename( $tpl ) : '';
    $allowed_templates = array( 'page-service-template.php', 'page-chi-siamo.php' );
    $is_allowed = in_array( $tpl, $allowed_templates, true ) || ( isset( $post->post_name ) && $post->post_name === 'chi-siamo' );

    if ( ! $is_allowed ) {
        echo '<p>' . esc_html__( 'Questo metabox è disponibile solo per le pagine "Servizi" o "Chi Siamo".', 'abcontact' ) . '</p>';
        return;
    }

    wp_nonce_field( 'ab_service_save', 'ab_service_nonce' );

    // load meta values
    $meta_keys = array(
        'service_hero_subtitle',
        'service_reviews_title','service_reviews_sub','service_reviews_shortcode',
        'service_how_title','service_how_subtitle'
    );
    $meta = array();
    foreach ( $meta_keys as $k ) {
        $meta[ $k ] = get_post_meta( $post->ID, $k, true );
    }

    // Load repeater steps
    $steps_raw = get_post_meta( $post->ID, '_service_how_steps', true );
    $steps = is_array( $steps_raw ) ? $steps_raw : array();

    wp_enqueue_media();
    ?>
    <div style="max-width:920px;">

      <h4><?php esc_html_e( 'Hero (sottotitolo)', 'abcontact' ); ?></h4>
      <p>
        <label for="service_hero_subtitle"><?php esc_html_e( 'Sottotitolo (mostrato nell’hero sotto il titolo)', 'abcontact' ); ?></label><br>
        <textarea id="service_hero_subtitle" name="service_hero_subtitle" rows="2" style="width:100%;"><?php echo esc_textarea( (string) $meta['service_hero_subtitle'] ); ?></textarea>
      </p>

      <hr>

      <h4><?php esc_html_e( 'Sezione "Come funziona" (titolo & sottotitolo)', 'abcontact' ); ?></h4>
      <p>
        <label for="service_how_title"><?php esc_html_e( 'Titolo sezione (es. "Come funziona")', 'abcontact' ); ?></label><br>
        <input type="text" id="service_how_title" name="service_how_title" value="<?php echo esc_attr( (string) $meta['service_how_title'] ); ?>" style="width:100%;">
      </p>
      <p>
        <label for="service_how_subtitle"><?php esc_html_e( 'Sottotitolo (breve descrizione sotto il titolo)', 'abcontact' ); ?></label><br>
        <textarea id="service_how_subtitle" name="service_how_subtitle" rows="2" style="width:100%;"><?php echo esc_textarea( (string) $meta['service_how_subtitle'] ); ?></textarea>
      </p>

      <hr>

      <h4><?php esc_html_e( 'Come funziona (card ripetibili)', 'abcontact' ); ?></h4>
      <p class="description"><?php esc_html_e( 'Aggiungi/rimuovi/riordina le card. Testo semplice (titolo + corpo). Icona selezionabile da Media Library.', 'abcontact' ); ?></p>

      <style>
        .svc-how-item{ border:1px solid #e6e9ee; padding:10px; margin-bottom:10px; border-radius:8px; background:#fff; }
        .svc-how-handle{ cursor:move; font-weight:700; color:#0b5fff; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center; }
        .svc-how-row{ display:flex; gap:10px; align-items:flex-start; margin-bottom:10px; }
        .svc-how-row .col{ flex:1; }
        .svc-how-field{ width:100%; box-sizing:border-box; padding:6px 8px; }
        .svc-how-actions{ display:flex; gap:8px; align-items:center; }
        .svc-how-icon-preview img{ width:54px; height:54px; object-fit:contain; display:block; }
        .svc-how-icon-preview{ margin-top:8px; }
      </style>

      <div id="service-how-repeater" data-repeater="service-how">
        <?php if ( ! empty( $steps ) ) : ?>
          <?php foreach ( $steps as $i => $s ) :
            if ( ! is_array( $s ) ) continue;
            $title = isset( $s['title'] ) ? (string) $s['title'] : '';
            $text = isset( $s['text'] ) ? (string) $s['text'] : '';
            $icon_id = isset( $s['icon_id'] ) ? absint( $s['icon_id'] ) : 0;
            ?>
            <div class="svc-how-item" data-index="<?php echo esc_attr( $i ); ?>">
              <div class="svc-how-handle">
                <span class="svc-how-label"><?php echo esc_html( $title ? $title : sprintf( __( 'Card %d', 'abcontact' ), $i + 1 ) ); ?></span>
                <div class="svc-how-actions">
                  <button type="button" class="button svc-how-remove"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
                </div>
              </div>

              <div class="svc-how-row">
                <div class="col">
                  <label><?php esc_html_e( 'Titolo', 'abcontact' ); ?></label>
                  <input type="text" class="svc-how-field" name="service_how_steps[<?php echo esc_attr( $i ); ?>][title]" value="<?php echo esc_attr( $title ); ?>">
                </div>
              </div>

              <div class="svc-how-row">
                <div class="col">
                  <label><?php esc_html_e( 'Corpo (testo)', 'abcontact' ); ?></label>
                  <textarea class="svc-how-field" rows="3" name="service_how_steps[<?php echo esc_attr( $i ); ?>][text]"><?php echo esc_textarea( $text ); ?></textarea>
                </div>
              </div>

              <div class="svc-how-row">
                <div class="col">
                  <label><?php esc_html_e( 'Icona', 'abcontact' ); ?></label><br>
                  <input type="hidden" class="svc-how-icon-id" name="service_how_steps[<?php echo esc_attr( $i ); ?>][icon_id]" value="<?php echo esc_attr( $icon_id ); ?>">
                  <button type="button" class="button svc-how-icon-pick"><?php esc_html_e( 'Seleziona icona', 'abcontact' ); ?></button>
                  <button type="button" class="button svc-how-icon-remove"><?php esc_html_e( 'Rimuovi icona', 'abcontact' ); ?></button>
                  <div class="svc-how-icon-preview">
                    <?php
                    if ( $icon_id ) {
                        echo wp_get_attachment_image( $icon_id, array(72,72) );
                    }
                    ?>
                  </div>
                </div>
              </div>

            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <template id="service-how-template">
          <div class="svc-how-item" data-index="__index__">
            <div class="svc-how-handle">
              <span class="svc-how-label"><?php esc_html_e( 'Nuova card', 'abcontact' ); ?></span>
              <div class="svc-how-actions">
                <button type="button" class="button svc-how-remove"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
              </div>
            </div>

            <div class="svc-how-row">
              <div class="col">
                <label><?php esc_html_e( 'Titolo', 'abcontact' ); ?></label>
                <input type="text" class="svc-how-field" name="service_how_steps[__index__][title]" value="">
              </div>
            </div>

            <div class="svc-how-row">
              <div class="col">
                <label><?php esc_html_e( 'Corpo (testo)', 'abcontact' ); ?></label>
                <textarea class="svc-how-field" rows="3" name="service_how_steps[__index__][text]"></textarea>
              </div>
            </div>

            <div class="svc-how-row">
              <div class="col">
                <label><?php esc_html_e( 'Icona', 'abcontact' ); ?></label><br>
                <input type="hidden" class="svc-how-icon-id" name="service_how_steps[__index__][icon_id]" value="">
                <button type="button" class="button svc-how-icon-pick"><?php esc_html_e( 'Seleziona icona', 'abcontact' ); ?></button>
                <button type="button" class="button svc-how-icon-remove"><?php esc_html_e( 'Rimuovi icona', 'abcontact' ); ?></button>
                <div class="svc-how-icon-preview"></div>
              </div>
            </div>
          </div>
        </template>

        <p><button type="button" class="button button-primary" id="service-how-add"><?php esc_html_e( 'Aggiungi card', 'abcontact' ); ?></button></p>
      </div>

      <hr>

      <h4><?php esc_html_e( 'Recensioni (titolo / sottotitolo / shortcode)', 'abcontact' ); ?></h4>
      <p>
        <label for="service_reviews_title"><?php esc_html_e( 'Titolo recensioni', 'abcontact' ); ?></label><br>
        <input type="text" id="service_reviews_title" name="service_reviews_title" value="<?php echo esc_attr( (string) $meta['service_reviews_title'] ); ?>" style="width:100%">
      </p>
      <p>
        <label for="service_reviews_sub"><?php esc_html_e( 'Sottotitolo recensioni', 'abcontact' ); ?></label><br>
        <input type="text" id="service_reviews_sub" name="service_reviews_sub" value="<?php echo esc_attr( (string) $meta['service_reviews_sub'] ); ?>" style="width:100%">
      </p>
      <p>
        <label for="service_reviews_shortcode"><?php esc_html_e( 'Shortcode recensioni', 'abcontact' ); ?></label><br>
        <input type="text" id="service_reviews_shortcode" name="service_reviews_shortcode" value="<?php echo esc_attr( (string) $meta['service_reviews_shortcode'] ); ?>" style="width:100%" placeholder="[your_reviews id=&quot;123&quot;]">
      </p>

    </div>

    <script>
    (function($){
      var frame;

      function reindex($root){
        $root.find('.svc-how-item').each(function(i){
          var $it = $(this);
          $it.attr('data-index', i);
          $it.find('[name]').each(function(){
            var name = $(this).attr('name') || '';
            name = name.replace(/\[\d+\]/, '['+i+']');
            name = name.replace(/\[__index__\]/, '['+i+']');
            $(this).attr('name', name);
          });

          var title = $it.find('input[name*="[title]"]').val();
          $it.find('.svc-how-label').text(title ? title : ('Card ' + (i+1)));
        });
      }

      function openMediaFor($item){
        if (frame) frame.close();
        frame = wp.media({
          title: 'Seleziona icona',
          button: { text: 'Seleziona' },
          library: { type: 'image' },
          multiple: false
        });
        frame.on('select', function(){
          var att = frame.state().get('selection').first().toJSON();
          if(!att) return;

          var id = att.id;
          var url = (att.sizes && att.sizes.thumbnail && att.sizes.thumbnail.url) ? att.sizes.thumbnail.url : (att.url || '');

          $item.find('.svc-how-icon-id').val(id);
          $item.find('.svc-how-icon-preview').html(url ? '<img src="'+ url +'" alt="" />' : '');
        });
        frame.open();
      }

      function bindItem($it, $root){
        $it.find('.svc-how-remove').on('click', function(e){
          e.preventDefault();
          $it.remove();
          reindex($root);
        });

        $it.find('input[name*="[title]"]').on('input', function(){
          var v = $(this).val();
          $it.find('.svc-how-label').text(v ? v : 'Nuova card');
        });

        $it.find('.svc-how-icon-pick').on('click', function(e){
          e.preventDefault();
          openMediaFor($it);
        });

        $it.find('.svc-how-icon-remove').on('click', function(e){
          e.preventDefault();
          $it.find('.svc-how-icon-id').val('');
          $it.find('.svc-how-icon-preview').html('');
        });
      }

      $(document).ready(function(){
        var $root = $('#service-how-repeater');
        if(!$root.length) return;

        $root.find('.svc-how-item').each(function(){ bindItem($(this), $root); });

        var tpl = $('#service-how-template').html();
        $('#service-how-add').on('click', function(e){
          e.preventDefault();
          var idx = $root.find('.svc-how-item').length;
          var html = tpl.replace(/__index__/g, idx);
          var $node = $(html);
          $root.find('> p').last().before($node);
          bindItem($node, $root);
          reindex($root);

          if($.fn.sortable){
            $root.sortable('refresh');
          }
        });

        if($.fn.sortable){
          $root.sortable({
            handle: '.svc-how-handle',
            items: '.svc-how-item',
            axis: 'y',
            update: function(){ reindex($root); }
          });
        }
      });
    })(jQuery);
    </script>
    <?php
}

/* Save metabox */
function abcontact_save_service_metabox( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['ab_service_nonce'] ) || ! wp_verify_nonce( $_POST['ab_service_nonce'], 'ab_service_save' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Hero subtitle
    if ( isset( $_POST['service_hero_subtitle'] ) ) {
        $raw = wp_unslash( $_POST['service_hero_subtitle'] );
        $raw = str_replace( array( "\r\n", "\r" ), "\n", $raw );
        update_post_meta( $post_id, 'service_hero_subtitle', sanitize_textarea_field( $raw ) );
    } else {
        delete_post_meta( $post_id, 'service_hero_subtitle' );
    }

    // Keep existing keys (how title/subtitle + reviews)
    $keys = array(
        'service_reviews_title',
        'service_reviews_sub',
        'service_reviews_shortcode',
        'service_how_title',
        'service_how_subtitle',
    );

    foreach ( $keys as $k ) {
        if ( isset( $_POST[ $k ] ) ) {
            $raw = wp_unslash( $_POST[ $k ] );
            if ( in_array( $k, array( 'service_reviews_sub', 'service_reviews_shortcode', 'service_how_subtitle' ), true ) ) {
                update_post_meta( $post_id, $k, wp_kses_post( $raw ) );
            } else {
                update_post_meta( $post_id, $k, sanitize_text_field( trim( $raw ) ) );
            }
        } else {
            delete_post_meta( $post_id, $k );
        }
    }

    // NEW: save repeater steps
    $steps_in = isset( $_POST['service_how_steps'] ) && is_array( $_POST['service_how_steps'] )
        ? wp_unslash( $_POST['service_how_steps'] )
        : array();

    $clean = array();
    foreach ( $steps_in as $item ) {
        if ( ! is_array( $item ) ) continue;

        $title = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';
        $text  = isset( $item['text'] ) ? sanitize_textarea_field( $item['text'] ) : '';
        $icon  = isset( $item['icon_id'] ) ? absint( $item['icon_id'] ) : 0;

        if ( $title === '' && $text === '' && $icon === 0 ) continue;

        $clean[] = array(
            'title'   => $title,
            'text'    => $text,
            'icon_id' => $icon,
        );
    }

    // Migration: if repeater empty, but legacy phases exist, auto-copy
    if ( empty( $clean ) ) {
        $legacy = array();
        for ( $i = 1; $i <= 4; $i++ ) {
            $t = get_post_meta( $post_id, "service_phase_{$i}_title", true );
            $x = get_post_meta( $post_id, "service_phase_{$i}_text", true );
            $ic = absint( get_post_meta( $post_id, "service_phase_{$i}_icon_id", true ) );

            if ( empty( $t ) && empty( $x ) && empty( $ic ) ) continue;

            $legacy[] = array(
                'title'   => sanitize_text_field( (string) $t ),
                'text'    => sanitize_textarea_field( (string) $x ),
                'icon_id' => $ic,
            );
        }

        if ( ! empty( $legacy ) ) {
            update_post_meta( $post_id, '_service_how_steps', $legacy );
            return;
        }

        // If truly empty: remove repeater meta
        delete_post_meta( $post_id, '_service_how_steps' );
    } else {
        update_post_meta( $post_id, '_service_how_steps', $clean );
    }
}
add_action( 'save_post', 'abcontact_save_service_metabox', 20 );

/* Enqueue admin scripts/styles for the metabox (keep existing) */
function abcontact_enqueue_service_admin_assets( $hook ) {
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) return;

    $post_id = 0;
    if ( isset( $GLOBALS['post'] ) && is_object( $GLOBALS['post'] ) && isset( $GLOBALS['post']->ID ) ) {
        $post_id = (int) $GLOBALS['post']->ID;
    }
    if ( empty( $post_id ) && isset( $_GET['post'] ) ) $post_id = (int) $_GET['post'];
    if ( empty( $post_id ) ) return;

    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'page' ) return;

    $tpl = get_post_meta( $post_id, '_wp_page_template', true );
    $tpl = $tpl ? basename( $tpl ) : '';
    $allowed_templates = array( 'page-service-template.php', 'page-chi-siamo.php', 'front-page.php' );
    if ( ! in_array( $tpl, $allowed_templates, true ) && $post->post_name !== 'chi-siamo' ) return;

    wp_enqueue_media();

    // Optional admin CSS
    $admin_css = get_stylesheet_directory() . '/assets/css/admin-metaboxes.css';
    if ( file_exists( $admin_css ) ) {
        wp_enqueue_style( 'ab-admin-metabox', get_stylesheet_directory_uri() . '/assets/css/admin-metaboxes.css', array(), filemtime( $admin_css ) );
    }
}
add_action( 'admin_enqueue_scripts', 'abcontact_enqueue_service_admin_assets', 10 );