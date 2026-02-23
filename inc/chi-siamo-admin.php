<?php
/**
 * inc/chi-siamo-admin.php
 *
 * Metabox "Chi Siamo" (grouped) — versione semplificata:
 * - Hero subtitle
 * - Titolo + sottotitolo sezione Card
 * - Repeater card (icone da Media Library, titolo + testo semplice)
 * - Statistiche (immutate)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* Helper: check if given $post is Chi Siamo page/template */
if ( ! function_exists( 'abcontact_is_chisiamo_post' ) ) {
    function abcontact_is_chisiamo_post( $post = null ) {
        if ( ! $post ) {
            if ( isset( $GLOBALS['post'] ) ) {
                $post = $GLOBALS['post'];
            } else {
                return false;
            }
        }
        if ( ! $post || ! isset( $post->post_type ) || $post->post_type !== 'page' ) {
            return false;
        }
        $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
        $tpl = $tpl ? basename( $tpl ) : '';
        if ( $tpl === 'page-chi-siamo.php' ) return true;
        if ( isset( $post->post_name ) && $post->post_name === 'chi-siamo' ) return true;
        return false;
    }
}

/* Register metabox */
if ( ! function_exists( 'abcontact_register_chisiamo_grouped_metabox' ) ) {
    function abcontact_register_chisiamo_grouped_metabox() {
        $post = null;
        if ( isset( $_GET['post'] ) ) {
            $post = get_post( (int) $_GET['post'] );
        } elseif ( isset( $GLOBALS['post'] ) ) {
            $post = $GLOBALS['post'];
        }

        if ( ! abcontact_is_chisiamo_post( $post ) ) {
            return;
        }

        add_meta_box(
            'ab_chisiamo_grouped',
            __( 'Pagina Chi Siamo — Contenuti', 'abcontact' ),
            'abcontact_render_chisiamo_grouped_metabox',
            'page',
            'normal',
            'high'
        );
    }
    add_action( 'add_meta_boxes', 'abcontact_register_chisiamo_grouped_metabox' );
}

/* Render metabox */
if ( ! function_exists( 'abcontact_render_chisiamo_grouped_metabox' ) ) {
    function abcontact_render_chisiamo_grouped_metabox( $post ) {
        if ( ! abcontact_is_chisiamo_post( $post ) ) {
            echo '<p>' . esc_html__( 'Questi campi sono disponibili solo per la pagina "Chi Siamo".', 'abcontact' ) . '</p>';
            return;
        }

        wp_nonce_field( 'ab_chisiamo_save', 'ab_chisiamo_nonce' );
        wp_enqueue_media();

        $hero_subtitle   = get_post_meta( $post->ID, 'cs_hero_subtitle', true );
        $cards_title     = get_post_meta( $post->ID, 'cs_cards_title', true );
        $cards_subtitle  = get_post_meta( $post->ID, 'cs_cards_subtitle', true );

        $cards_raw = get_post_meta( $post->ID, '_cs_how_steps', true );
        $cards = is_array( $cards_raw ) ? $cards_raw : array();

        // stats
        $cs_stats_heading = get_post_meta( $post->ID, 'cs_stats_heading', true );
        $stats = array();
        for ( $i = 1; $i <= 4; $i++ ) {
            $stats[$i] = array(
                'value' => get_post_meta( $post->ID, "cs_stat_{$i}_value", true ),
                'label' => get_post_meta( $post->ID, "cs_stat_{$i}_label", true ),
            );
        }
        ?>
        <div style="max-width:920px; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;">

          <h3 style="margin-top:0;"><?php esc_html_e( 'Hero', 'abcontact' ); ?></h3>
          <p>
            <label for="cs_hero_subtitle"><?php esc_html_e( 'Sottotitolo hero', 'abcontact' ); ?></label><br>
            <textarea id="cs_hero_subtitle" name="cs_hero_subtitle" rows="2" style="width:100%"><?php echo esc_textarea( (string) $hero_subtitle ); ?></textarea>
          </p>

          <hr style="margin:18px 0;border:none;border-top:1px solid #eee;">

          <h3 style="margin:0 0 8px;"><?php esc_html_e( 'Sezione Card', 'abcontact' ); ?></h3>
          <p>
            <label for="cs_cards_title"><?php esc_html_e( 'Titolo sezione card', 'abcontact' ); ?></label><br>
            <input type="text" id="cs_cards_title" name="cs_cards_title" value="<?php echo esc_attr( (string) $cards_title ); ?>" style="width:100%">
          </p>
          <p>
            <label for="cs_cards_subtitle"><?php esc_html_e( 'Sottotitolo sezione card (opzionale)', 'abcontact' ); ?></label><br>
            <textarea id="cs_cards_subtitle" name="cs_cards_subtitle" rows="2" style="width:100%"><?php echo esc_textarea( (string) $cards_subtitle ); ?></textarea>
          </p>

          <hr style="margin:18px 0;border:none;border-top:1px solid #eee;">

          <h3 style="margin:0 0 8px;"><?php esc_html_e( 'Card (repeater)', 'abcontact' ); ?></h3>
          <p class="description" style="color:#666;margin:0 0 10px;">
            <?php esc_html_e( 'Card ripetibili con icona + titolo + testo (testo semplice).', 'abcontact' ); ?>
          </p>

          <style>
            .cs-card-item{ border:1px solid #e6e9ee; padding:10px; margin-bottom:10px; border-radius:8px; background:#fff; }
            .cs-card-handle{ cursor:move; font-weight:700; color:#0b5fff; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center; }
            .cs-card-row{ display:flex; gap:10px; align-items:flex-start; margin-bottom:10px; }
            .cs-card-row .col{ flex:1; }
            .cs-card-field{ width:100%; box-sizing:border-box; padding:6px 8px; }
            .cs-card-actions{ display:flex; gap:8px; align-items:center; }
            .cs-card-icon-preview img{ width:54px; height:54px; object-fit:contain; display:block; }
            .cs-card-icon-preview{ margin-top:8px; }
          </style>

          <div id="cs-cards-repeater">
            <?php foreach ( $cards as $i => $c ) :
              if ( ! is_array( $c ) ) continue;
              $t = isset( $c['title'] ) ? (string) $c['title'] : '';
              $x = isset( $c['text'] ) ? (string) $c['text'] : '';
              $icon_id = isset( $c['icon_id'] ) ? absint( $c['icon_id'] ) : 0;
              ?>
              <div class="cs-card-item" data-index="<?php echo esc_attr( $i ); ?>">
                <div class="cs-card-handle">
                  <span class="cs-card-label"><?php echo esc_html( $t ?: sprintf( __( 'Card %d', 'abcontact' ), $i + 1 ) ); ?></span>
                  <div class="cs-card-actions">
                    <button type="button" class="button cs-card-remove"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
                  </div>
                </div>

                <div class="cs-card-row">
                  <div class="col">
                    <label><?php esc_html_e( 'Titolo', 'abcontact' ); ?></label><br>
                    <input type="text" class="cs-card-field" name="cs_cards[<?php echo esc_attr( $i ); ?>][title]" value="<?php echo esc_attr( $t ); ?>">
                  </div>
                </div>

                <div class="cs-card-row">
                  <div class="col">
                    <label><?php esc_html_e( 'Testo', 'abcontact' ); ?></label><br>
                    <textarea class="cs-card-field" rows="3" name="cs_cards[<?php echo esc_attr( $i ); ?>][text]"><?php echo esc_textarea( $x ); ?></textarea>
                  </div>
                </div>

                <div class="cs-card-row">
                  <div class="col">
                    <label><?php esc_html_e( 'Icona', 'abcontact' ); ?></label><br>
                    <input type="hidden" class="cs-card-icon-id" name="cs_cards[<?php echo esc_attr( $i ); ?>][icon_id]" value="<?php echo esc_attr( $icon_id ); ?>">
                    <button type="button" class="button cs-card-icon-pick"><?php esc_html_e( 'Seleziona icona', 'abcontact' ); ?></button>
                    <button type="button" class="button cs-card-icon-remove"><?php esc_html_e( 'Rimuovi icona', 'abcontact' ); ?></button>
                    <div class="cs-card-icon-preview">
                      <?php if ( $icon_id ) echo wp_get_attachment_image( $icon_id, array(72,72) ); ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>

            <template id="cs-card-template">
              <div class="cs-card-item" data-index="__index__">
                <div class="cs-card-handle">
                  <span class="cs-card-label"><?php esc_html_e( 'Nuova card', 'abcontact' ); ?></span>
                  <div class="cs-card-actions">
                    <button type="button" class="button cs-card-remove"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
                  </div>
                </div>

                <div class="cs-card-row">
                  <div class="col">
                    <label><?php esc_html_e( 'Titolo', 'abcontact' ); ?></label><br>
                    <input type="text" class="cs-card-field" name="cs_cards[__index__][title]" value="">
                  </div>
                </div>

                <div class="cs-card-row">
                  <div class="col">
                    <label><?php esc_html_e( 'Testo', 'abcontact' ); ?></label><br>
                    <textarea class="cs-card-field" rows="3" name="cs_cards[__index__][text]"></textarea>
                  </div>
                </div>

                <div class="cs-card-row">
                  <div class="col">
                    <label><?php esc_html_e( 'Icona', 'abcontact' ); ?></label><br>
                    <input type="hidden" class="cs-card-icon-id" name="cs_cards[__index__][icon_id]" value="">
                    <button type="button" class="button cs-card-icon-pick"><?php esc_html_e( 'Seleziona icona', 'abcontact' ); ?></button>
                    <button type="button" class="button cs-card-icon-remove"><?php esc_html_e( 'Rimuovi icona', 'abcontact' ); ?></button>
                    <div class="cs-card-icon-preview"></div>
                  </div>
                </div>
              </div>
            </template>

            <p><button type="button" class="button button-primary" id="cs-card-add"><?php esc_html_e( 'Aggiungi card', 'abcontact' ); ?></button></p>
          </div>

          <hr style="margin:18px 0;border:none;border-top:1px solid #eee;">

          <h3 style="margin:0 0 8px;"><?php esc_html_e( 'Statistiche', 'abcontact' ); ?></h3>

          <p>
            <label for="cs_stats_heading"><?php esc_html_e( 'Titolo statistica (opzionale)', 'abcontact' ); ?></label><br>
            <input type="text" id="cs_stats_heading" name="cs_stats_heading" value="<?php echo esc_attr( (string) $cs_stats_heading ); ?>" style="width:100%">
          </p>

          <?php for ( $i = 1; $i <= 4; $i++ ) : ?>
            <p style="display:flex;gap:8px;margin-bottom:8px;">
              <input type="text" name="cs_stat_<?php echo $i; ?>_value" value="<?php echo esc_attr( (string) $stats[$i]['value'] ); ?>" placeholder="<?php echo esc_attr( '10+', 'abcontact' ); ?>" style="width:30%">
              <input type="text" name="cs_stat_<?php echo $i; ?>_label" value="<?php echo esc_attr( (string) $stats[$i]['label'] ); ?>" placeholder="<?php echo esc_attr( 'Etichetta', 'abcontact' ); ?>" style="width:70%">
            </p>
          <?php endfor; ?>

          <script>
          (function($){
            var frame;

            function reindex(){
              $('#cs-cards-repeater .cs-card-item').each(function(i){
                var $it = $(this);
                $it.attr('data-index', i);
                $it.find('[name]').each(function(){
                  var name = $(this).attr('name') || '';
                  name = name.replace(/\[\d+\]/, '['+i+']');
                  name = name.replace(/\[__index__\]/, '['+i+']');
                  $(this).attr('name', name);
                });
                var title = $it.find('input[name*="[title]"]').val();
                $it.find('.cs-card-label').text(title ? title : ('Card ' + (i+1)));
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

                $item.find('.cs-card-icon-id').val(id);
                $item.find('.cs-card-icon-preview').html(url ? '<img src="'+ url +'" alt="" />' : '');
              });
              frame.open();
            }

            function bindItem($it){
              $it.find('.cs-card-remove').on('click', function(e){
                e.preventDefault();
                $it.remove();
                reindex();
              });

              $it.find('input[name*="[title]"]').on('input', function(){
                var v = $(this).val();
                $it.find('.cs-card-label').text(v ? v : 'Nuova card');
              });

              $it.find('.cs-card-icon-pick').on('click', function(e){
                e.preventDefault();
                openMediaFor($it);
              });

              $it.find('.cs-card-icon-remove').on('click', function(e){
                e.preventDefault();
                $it.find('.cs-card-icon-id').val('');
                $it.find('.cs-card-icon-preview').html('');
              });
            }

            $(document).ready(function(){
              var $wrap = $('#cs-cards-repeater');
              if(!$wrap.length) return;

              $wrap.find('.cs-card-item').each(function(){ bindItem($(this)); });

              var tpl = $('#cs-card-template').html();
              $('#cs-card-add').on('click', function(e){
                e.preventDefault();
                var idx = $wrap.find('.cs-card-item').length;
                var html = tpl.replace(/__index__/g, idx);
                var $node = $(html);
                $wrap.find('> p').last().before($node);
                bindItem($node);
                reindex();

                if($.fn.sortable){ $wrap.sortable('refresh'); }
              });

              if($.fn.sortable){
                $wrap.sortable({
                  handle: '.cs-card-handle',
                  items: '.cs-card-item',
                  axis: 'y',
                  update: function(){ reindex(); }
                });
              }
            });
          })(jQuery);
          </script>

        </div>
        <?php
    }
}

/* Save handler */
if ( ! function_exists( 'ab_chisiamo_save_fields' ) ) {
    function ab_chisiamo_save_fields( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! isset( $_POST['ab_chisiamo_nonce'] ) || ! wp_verify_nonce( $_POST['ab_chisiamo_nonce'], 'ab_chisiamo_save' ) ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // Hero subtitle
        if ( isset( $_POST['cs_hero_subtitle'] ) ) {
            $raw = wp_unslash( $_POST['cs_hero_subtitle'] );
            $raw = str_replace( array( "\r\n", "\r" ), "\n", $raw );
            update_post_meta( $post_id, 'cs_hero_subtitle', sanitize_textarea_field( $raw ) );
        } else {
            delete_post_meta( $post_id, 'cs_hero_subtitle' );
        }

        // Cards section title/subtitle
        if ( isset( $_POST['cs_cards_title'] ) ) {
            update_post_meta( $post_id, 'cs_cards_title', sanitize_text_field( wp_unslash( $_POST['cs_cards_title'] ) ) );
        } else {
            delete_post_meta( $post_id, 'cs_cards_title' );
        }

        if ( isset( $_POST['cs_cards_subtitle'] ) ) {
            $raw = wp_unslash( $_POST['cs_cards_subtitle'] );
            $raw = str_replace( array( "\r\n", "\r" ), "\n", $raw );
            update_post_meta( $post_id, 'cs_cards_subtitle', sanitize_textarea_field( $raw ) );
        } else {
            delete_post_meta( $post_id, 'cs_cards_subtitle' );
        }

        // Cards repeater
        $cards_in = isset( $_POST['cs_cards'] ) && is_array( $_POST['cs_cards'] ) ? wp_unslash( $_POST['cs_cards'] ) : array();
        $clean = array();

        foreach ( $cards_in as $item ) {
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

        if ( ! empty( $clean ) ) {
            update_post_meta( $post_id, '_cs_how_steps', $clean );
        } else {
            delete_post_meta( $post_id, '_cs_how_steps' );
        }

        // Stats (unchanged)
        $fields = array(
            'cs_stats_heading',
            'cs_stat_1_value','cs_stat_1_label',
            'cs_stat_2_value','cs_stat_2_label',
            'cs_stat_3_value','cs_stat_3_label',
            'cs_stat_4_value','cs_stat_4_label'
        );

        foreach ( $fields as $f ) {
            if ( isset( $_POST[ $f ] ) ) {
                $val = wp_unslash( $_POST[ $f ] );
                update_post_meta( $post_id, $f, sanitize_text_field( $val ) );
            }
        }
    }
    add_action( 'save_post', 'ab_chisiamo_save_fields', 20 );
}

/* Admin enqueue */
if ( ! function_exists( 'ab_chisiamo_admin_enqueue' ) ) {
    function ab_chisiamo_admin_enqueue( $hook ) {
        if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) return;

        $post_id = 0;
        if ( isset( $_GET['post'] ) ) $post_id = (int) $_GET['post'];
        elseif ( isset( $_POST['post_ID'] ) ) $post_id = (int) $_POST['post_ID'];
        elseif ( isset( $GLOBALS['post']->ID ) ) $post_id = (int) $GLOBALS['post']->ID;
        if ( ! $post_id ) return;

        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'page' ) return;
        if ( ! abcontact_is_chisiamo_post( $post ) ) return;

        wp_enqueue_media();

        $admin_css = get_stylesheet_directory() . '/assets/css/admin-metaboxes.css';
        if ( file_exists( $admin_css ) ) {
            wp_enqueue_style( 'ab-admin-metabox', get_stylesheet_directory_uri() . '/assets/css/admin-metaboxes.css', array(), filemtime( $admin_css ) );
        }
    }
    add_action( 'admin_enqueue_scripts', 'ab_chisiamo_admin_enqueue', 10 );
}