<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'abcontact_is_landing_post' ) ) {
    function abcontact_is_landing_post( $post = null ) {
        if ( ! $post ) {
            if ( isset( $GLOBALS['post'] ) ) {
                $post = $GLOBALS['post'];
            } elseif ( isset( $_GET['post'] ) ) {
                $post = get_post( (int) $_GET['post'] );
            }
        }
        if ( ! $post || $post->post_type !== 'page' ) {
            return false;
        }

        $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
        $tpl = $tpl ? basename( $tpl ) : '';
        return ( $tpl === 'page-landing.php' || $tpl === 'page-templates/page-landing.php' );
    }
}

add_action( 'add_meta_boxes', function() {
    $post = null;
    if ( isset( $_GET['post'] ) ) {
        $post = get_post( (int) $_GET['post'] );
    } elseif ( isset( $GLOBALS['post'] ) ) {
        $post = $GLOBALS['post'];
    }

    if ( ! abcontact_is_landing_post( $post ) ) {
        return;
    }

    add_meta_box(
        'ab_landing_metabox',
        __( 'Landing — Impostazioni', 'theme-abcontact' ),
        'abcontact_render_landing_metabox',
        'page',
        'normal',
        'high'
    );
}, 20 );

function abcontact_render_landing_metabox( $post ) {
    if ( ! abcontact_is_landing_post( $post ) ) {
        echo '<p>' . esc_html__( 'Questo metabox è disponibile solo per il template Landing.', 'theme-abcontact' ) . '</p>';
        return;
    }

    wp_nonce_field( 'abcontact_landing_save', 'abcontact_landing_nonce' );

    $logo_id  = absint( get_post_meta( $post->ID, 'landing_logo_id', true ) );
    $img_id   = absint( get_post_meta( $post->ID, 'landing_image_id', true ) );
    $form_sc  = get_post_meta( $post->ID, 'landing_form_shortcode', true );
    $contact  = get_post_meta( $post->ID, 'landing_contact_text', true );

    // Colors (admin-controlled)
    $bg_color          = get_post_meta( $post->ID, 'landing_bg_color', true );
    $header_bg_color   = get_post_meta( $post->ID, 'landing_header_bg_color', true );
    $footer_bg_color   = get_post_meta( $post->ID, 'landing_footer_bg_color', true );
    $footer_text_color = get_post_meta( $post->ID, 'landing_footer_text_color', true );

    if ( ! $bg_color ) $bg_color = '#3650b7';
    if ( ! $header_bg_color ) $header_bg_color = '#2740a5';
    if ( ! $footer_bg_color ) $footer_bg_color = '#0f1b33';
    if ( ! $footer_text_color ) $footer_text_color = '#ffffff';

    $blocks = get_post_meta( $post->ID, '_landing_blocks', true );
    if ( ! is_array( $blocks ) ) $blocks = array();

    $social = array();
    for ( $i = 1; $i <= 3; $i++ ) {
        $social[$i] = array(
            'label' => get_post_meta( $post->ID, "landing_social_{$i}_label", true ),
            'url'   => get_post_meta( $post->ID, "landing_social_{$i}_url", true ),
            'icon'  => absint( get_post_meta( $post->ID, "landing_social_{$i}_icon_id", true ) ),
        );
    }

    $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';
    $img_url  = $img_id ? wp_get_attachment_image_url( $img_id, 'large' ) : '';
    ?>
    <style>
      .ab-landing-row{display:flex; gap:14px; align-items:flex-start; flex-wrap:wrap;}
      .ab-landing-col{flex:1 1 280px;}
      .ab-landing-field{width:100%; box-sizing:border-box; padding:8px 10px;}
      .ab-landing-item{border:1px solid #e6e9ee; padding:12px; border-radius:10px; background:#fff; margin:10px 0;}
      .ab-landing-handle{display:flex; justify-content:space-between; align-items:center; cursor:move; font-weight:700; color:#0b5fff;}
      .ab-landing-actions{display:flex; gap:10px; align-items:center;}
      .ab-landing-btn{border:1px solid #ccd5e1; background:#fff; padding:6px 10px; border-radius:8px; cursor:pointer;}
      .ab-landing-help{color:#6b7280; font-size:12px;}
      .ab-media-field{display:flex; gap:10px; align-items:center; flex-wrap:wrap;}
      .ab-media-preview{width:64px; height:64px; border-radius:10px; border:1px solid rgba(16,24,40,0.08); background:#f8fafc; display:flex; align-items:center; justify-content:center; overflow:hidden;}
      .ab-media-preview img{width:100%; height:100%; object-fit:cover; display:block;}
      .ab-media-hidden{display:none !important;}
      .ab-landing-editor{margin-top:10px;}
      .ab-landing-editor .wp-editor-container{border-radius:10px; overflow:hidden;}
      .ab-color-row{display:flex; gap:12px; align-items:center; flex-wrap:wrap;}
      .ab-color-row label{min-width:240px;}
    </style>

    <h3><?php esc_html_e( 'Colori Landing', 'theme-abcontact' ); ?></h3>

    <div class="ab-color-row">
      <label for="landing_bg_color"><strong><?php esc_html_e( 'Sfondo principale', 'theme-abcontact' ); ?></strong></label>
      <input type="color" id="landing_bg_color" name="landing_bg_color" value="<?php echo esc_attr( $bg_color ); ?>">
      <input class="ab-landing-field" style="max-width:200px;" type="text" value="<?php echo esc_attr( $bg_color ); ?>" readonly>
    </div>

    <div class="ab-color-row" style="margin-top:10px;">
      <label for="landing_header_bg_color"><strong><?php esc_html_e( 'Sfondo header (logo + immagine)', 'theme-abcontact' ); ?></strong></label>
      <input type="color" id="landing_header_bg_color" name="landing_header_bg_color" value="<?php echo esc_attr( $header_bg_color ); ?>">
      <input class="ab-landing-field" style="max-width:200px;" type="text" value="<?php echo esc_attr( $header_bg_color ); ?>" readonly>
    </div>

    <div class="ab-color-row" style="margin-top:10px;">
      <label for="landing_footer_bg_color"><strong><?php esc_html_e( 'Sfondo footer', 'theme-abcontact' ); ?></strong></label>
      <input type="color" id="landing_footer_bg_color" name="landing_footer_bg_color" value="<?php echo esc_attr( $footer_bg_color ); ?>">
      <input class="ab-landing-field" style="max-width:200px;" type="text" value="<?php echo esc_attr( $footer_bg_color ); ?>" readonly>
    </div>

    <div class="ab-color-row" style="margin-top:10px;">
      <label for="landing_footer_text_color"><strong><?php esc_html_e( 'Colore testo footer', 'theme-abcontact' ); ?></strong></label>
      <input type="color" id="landing_footer_text_color" name="landing_footer_text_color" value="<?php echo esc_attr( $footer_text_color ); ?>">
      <input class="ab-landing-field" style="max-width:200px;" type="text" value="<?php echo esc_attr( $footer_text_color ); ?>" readonly>
    </div>

    <hr>

    <div class="ab-landing-row">
      <div class="ab-landing-col">
        <p><strong><?php esc_html_e( 'Logo (Media Library)', 'theme-abcontact' ); ?></strong></p>
        <div class="ab-media-field" data-media-field>
          <div class="ab-media-preview">
            <?php if ( $logo_url ) : ?><img src="<?php echo esc_url( $logo_url ); ?>" alt=""><?php else : ?>
              <span class="ab-landing-help"><?php esc_html_e( 'Nessun logo', 'theme-abcontact' ); ?></span>
            <?php endif; ?>
          </div>
          <input type="hidden" name="landing_logo_id" value="<?php echo esc_attr( $logo_id ); ?>" data-media-id>
          <button type="button" class="ab-landing-btn" data-media-select><?php esc_html_e( 'Seleziona', 'theme-abcontact' ); ?></button>
          <button type="button" class="ab-landing-btn <?php echo $logo_id ? '' : 'ab-media-hidden'; ?>" data-media-remove><?php esc_html_e( 'Rimuovi', 'theme-abcontact' ); ?></button>
        </div>
      </div>

      <div class="ab-landing-col">
        <p><strong><?php esc_html_e( 'Immagine centrale (Media Library)', 'theme-abcontact' ); ?></strong></p>
        <div class="ab-media-field" data-media-field>
          <div class="ab-media-preview">
            <?php if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" alt=""><?php else : ?>
              <span class="ab-landing-help"><?php esc_html_e( 'Nessuna immagine', 'theme-abcontact' ); ?></span>
            <?php endif; ?>
          </div>
          <input type="hidden" name="landing_image_id" value="<?php echo esc_attr( $img_id ); ?>" data-media-id>
          <button type="button" class="ab-landing-btn" data-media-select><?php esc_html_e( 'Seleziona', 'theme-abcontact' ); ?></button>
          <button type="button" class="ab-landing-btn <?php echo $img_id ? '' : 'ab-media-hidden'; ?>" data-media-remove><?php esc_html_e( 'Rimuovi', 'theme-abcontact' ); ?></button>
        </div>
      </div>
    </div>

    <hr>

    <p><strong><?php esc_html_e( 'Shortcode form (plugin)', 'theme-abcontact' ); ?></strong></p>
    <input class="ab-landing-field" type="text" name="landing_form_shortcode" value="<?php echo esc_attr( $form_sc ); ?>">

    <p style="margin-top:14px;"><strong><?php esc_html_e( 'Contatti (testo sotto ai social)', 'theme-abcontact' ); ?></strong></p>
    <textarea class="ab-landing-field" rows="4" name="landing_contact_text"><?php echo esc_textarea( $contact ); ?></textarea>

    <hr>

    <h3><?php esc_html_e( 'Social links (3)', 'theme-abcontact' ); ?></h3>
    <?php for ( $i = 1; $i <= 3; $i++ ) :
        $icon_url = $social[$i]['icon'] ? wp_get_attachment_image_url( $social[$i]['icon'], 'thumbnail' ) : '';
        ?>
      <div class="ab-landing-item">
        <div class="ab-landing-handle"><?php printf( esc_html__( 'Social %d', 'theme-abcontact' ), $i ); ?></div>

        <p><label><strong><?php esc_html_e( 'Label', 'theme-abcontact' ); ?></strong></label>
        <input class="ab-landing-field" type="text" name="landing_social_<?php echo (int) $i; ?>_label" value="<?php echo esc_attr( $social[$i]['label'] ); ?>"></p>

        <p><label><strong><?php esc_html_e( 'URL (con https://)', 'theme-abcontact' ); ?></strong></label>
        <input class="ab-landing-field" type="url" name="landing_social_<?php echo (int) $i; ?>_url" value="<?php echo esc_attr( $social[$i]['url'] ); ?>"></p>

        <p><strong><?php esc_html_e( 'Icona (Media Library)', 'theme-abcontact' ); ?></strong></p>
        <div class="ab-media-field" data-media-field>
          <div class="ab-media-preview">
            <?php if ( $icon_url ) : ?><img src="<?php echo esc_url( $icon_url ); ?>" alt=""><?php else : ?>
              <span class="ab-landing-help"><?php esc_html_e( 'Nessuna icona', 'theme-abcontact' ); ?></span>
            <?php endif; ?>
          </div>

          <input type="hidden" name="landing_social_<?php echo (int) $i; ?>_icon_id" value="<?php echo esc_attr( $social[$i]['icon'] ); ?>" data-media-id>
          <button type="button" class="ab-landing-btn" data-media-select><?php esc_html_e( 'Seleziona', 'theme-abcontact' ); ?></button>
          <button type="button" class="ab-landing-btn <?php echo $social[$i]['icon'] ? '' : 'ab-media-hidden'; ?>" data-media-remove><?php esc_html_e( 'Rimuovi', 'theme-abcontact' ); ?></button>
        </div>
      </div>
    <?php endfor; ?>

    <hr>

    <h3><?php esc_html_e( 'Blocchi testo (ripetibili) — editor + bottone opzionale', 'theme-abcontact' ); ?></h3>

    <div id="landing-blocks-repeater">
      <?php foreach ( $blocks as $idx => $b ) :
        $body  = $b['body'] ?? '';
        $bl    = $b['button_label'] ?? '';
        $bu    = $b['button_url'] ?? '';
        $bv    = $b['button_variant'] ?? 'primary';
        $editor_id = 'landing_block_' . (int) $idx . '_body';
      ?>
        <div class="ab-landing-item landing-block-item" data-index="<?php echo (int) $idx; ?>">
          <div class="ab-landing-handle">
            <span><?php esc_html_e( 'Blocco', 'theme-abcontact' ); ?> #<?php echo (int) ( $idx + 1 ); ?></span>
            <div class="ab-landing-actions">
              <button type="button" class="ab-landing-btn" data-remove><?php esc_html_e( 'Rimuovi', 'theme-abcontact' ); ?></button>
            </div>
          </div>

          <div class="ab-landing-editor">
            <?php
            wp_editor(
                $body,
                $editor_id,
                array(
                    'textarea_name' => "landing_blocks[{$idx}][body]",
                    'media_buttons' => true,
                    'teeny'         => false,
                    'textarea_rows' => 8,
                    'tinymce'       => true,
                    'quicktags'     => true,
                )
            );
            ?>
          </div>

          <div class="ab-landing-row">
            <div class="ab-landing-col">
              <p><label><strong><?php esc_html_e( 'Testo bottone (opzionale)', 'theme-abcontact' ); ?></strong></label>
              <input class="ab-landing-field" type="text" name="landing_blocks[<?php echo (int) $idx; ?>][button_label]" value="<?php echo esc_attr( $bl ); ?>"></p>
            </div>
            <div class="ab-landing-col">
              <p><label><strong><?php esc_html_e( 'Link bottone (opzionale)', 'theme-abcontact' ); ?></strong></label>
              <input class="ab-landing-field" type="url" name="landing_blocks[<?php echo (int) $idx; ?>][button_url]" value="<?php echo esc_attr( $bu ); ?>"></p>
            </div>
            <div class="ab-landing-col">
              <p><label><strong><?php esc_html_e( 'Stile bottone', 'theme-abcontact' ); ?></strong></label>
              <select class="ab-landing-field" name="landing_blocks[<?php echo (int) $idx; ?>][button_variant]">
                <option value="primary" <?php selected( $bv, 'primary' ); ?>><?php esc_html_e( 'Primary', 'theme-abcontact' ); ?></option>
                <option value="ghost" <?php selected( $bv, 'ghost' ); ?>><?php esc_html_e( 'Ghost', 'theme-abcontact' ); ?></option>
              </select></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <p><button type="button" class="ab-landing-btn" id="landing-add-block"><?php esc_html_e( 'Aggiungi blocco', 'theme-abcontact' ); ?></button></p>

    <script>
    (function($){
      function initMediaFields($scope){
        $scope.find('[data-media-field]').each(function(){
          var $field = $(this);
          var $id = $field.find('[data-media-id]');
          var $preview = $field.find('.ab-media-preview');
          var $remove = $field.find('[data-media-remove]');

          $field.find('[data-media-select]').off('click').on('click', function(e){
            e.preventDefault();
            var frame = wp.media({
              title: '<?php echo esc_js( __( 'Seleziona immagine', 'theme-abcontact' ) ); ?>',
              button: { text: '<?php echo esc_js( __( 'Usa questa immagine', 'theme-abcontact' ) ); ?>' },
              multiple: false
            });

            frame.on('select', function(){
              var attachment = frame.state().get('selection').first().toJSON();
              $id.val(attachment.id);
              var url = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;
              $preview.html('<img src="'+url+'" alt="">');
              $remove.removeClass('ab-media-hidden');
            });

            frame.open();
          });

          $remove.off('click').on('click', function(e){
            e.preventDefault();
            $id.val('');
            $preview.html('<span class="ab-landing-help"><?php echo esc_js( __( 'Nessuna immagine', 'theme-abcontact' ) ); ?></span>');
            $remove.addClass('ab-media-hidden');
          });
        });
      }

      function addBlock(){
        var idx = $('#landing-blocks-repeater .landing-block-item').length;
        var uid = 'landing_block_' + idx + '_body';

        var html = ''
          + '<div class="ab-landing-item landing-block-item" data-index="'+idx+'">'
          +   '<div class="ab-landing-handle">'
          +     '<span><?php echo esc_js( __( 'Blocco', 'theme-abcontact' ) ); ?> #' + (idx+1) + '</span>'
          +     '<div class="ab-landing-actions"><button type="button" class="ab-landing-btn" data-remove><?php echo esc_js( __( 'Rimuovi', 'theme-abcontact' ) ); ?></button></div>'
          +   '</div>'
          +   '<div class="ab-landing-editor"><textarea id="'+uid+'" name="landing_blocks['+idx+'][body]"></textarea></div>'
          +   '<div class="ab-landing-row">'
          +     '<div class="ab-landing-col"><p><label><strong><?php echo esc_js( __( 'Testo bottone (opzionale)', 'theme-abcontact' ) ); ?></strong></label>'
          +     '<input class="ab-landing-field" type="text" name="landing_blocks['+idx+'][button_label]" value=""></p></div>'
          +     '<div class="ab-landing-col"><p><label><strong><?php echo esc_js( __( 'Link bottone (opzionale)', 'theme-abcontact' ) ); ?></strong></label>'
          +     '<input class="ab-landing-field" type="url" name="landing_blocks['+idx+'][button_url]" value=""></p></div>'
          +     '<div class="ab-landing-col"><p><label><strong><?php echo esc_js( __( 'Stile bottone', 'theme-abcontact' ) ); ?></strong></label>'
          +     '<select class="ab-landing-field" name="landing_blocks['+idx+'][button_variant]">'
          +       '<option value="primary"><?php echo esc_js( __( 'Primary', 'theme-abcontact' ) ); ?></option>'
          +       '<option value="ghost"><?php echo esc_js( __( 'Ghost', 'theme-abcontact' ) ); ?></option>'
          +     '</select></p></div>'
          +   '</div>'
          + '</div>';

        $('#landing-blocks-repeater').append(html);

        if ( typeof wp !== 'undefined' && wp.editor && wp.editor.initialize ) {
          wp.editor.initialize(uid, { tinymce: true, quicktags: true, mediaButtons: true });
        } else if ( typeof tinymce !== 'undefined' ) {
          tinymce.init({ selector: '#'+uid });
        }
        if ( typeof quicktags !== 'undefined' ) {
          quicktags({ id: uid });
        }
      }

      $(document).ready(function(){
        if ( typeof wp !== 'undefined' && wp.media ) {
          initMediaFields($(document));
        }

        $('#landing-blocks-repeater').on('click', '[data-remove]', function(e){
          e.preventDefault();
          var $item = $(this).closest('.landing-block-item');
          var $ta = $item.find('textarea[id]');
          if ($ta.length) {
            var id = $ta.attr('id');
            if ( typeof wp !== 'undefined' && wp.editor && wp.editor.remove ) {
              wp.editor.remove(id);
            }
          }
          $item.remove();
        });

        $('#landing-add-block').on('click', function(e){
          e.preventDefault();
          addBlock();
        });
      });
    })(jQuery);
    </script>
    <?php
}

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
        return;
    }

    $post_id = 0;
    if ( isset( $_GET['post'] ) ) {
        $post_id = (int) $_GET['post'];
    } elseif ( isset( $GLOBALS['post']->ID ) ) {
        $post_id = (int) $GLOBALS['post']->ID;
    }

    if ( ! $post_id ) {
        return;
    }

    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'page' ) {
        return;
    }

    if ( ! abcontact_is_landing_post( $post ) ) {
        return;
    }

    wp_enqueue_media();
}, 20 );

add_action( 'save_post_page', function( $post_id, $post ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['abcontact_landing_nonce'] ) || ! wp_verify_nonce( $_POST['abcontact_landing_nonce'], 'abcontact_landing_save' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $tpl = get_post_meta( $post_id, '_wp_page_template', true );
    $tpl = $tpl ? basename( $tpl ) : '';
    if ( $tpl !== 'page-landing.php' && $tpl !== 'page-templates/page-landing.php' ) return;

    // Save colors
    foreach ( array(
        'landing_bg_color' => 'landing_bg_color',
        'landing_header_bg_color' => 'landing_header_bg_color',
        'landing_footer_bg_color' => 'landing_footer_bg_color',
        'landing_footer_text_color' => 'landing_footer_text_color',
    ) as $post_key => $meta_key ) {
        if ( isset( $_POST[ $post_key ] ) ) {
            $c = sanitize_hex_color( wp_unslash( $_POST[ $post_key ] ) );
            if ( $c ) update_post_meta( $post_id, $meta_key, $c );
            else delete_post_meta( $post_id, $meta_key );
        }
    }

    foreach ( array( 'landing_logo_id', 'landing_image_id' ) as $k ) {
        if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $k, absint( $_POST[ $k ] ) );
        else delete_post_meta( $post_id, $k );
    }

    if ( isset( $_POST['landing_form_shortcode'] ) ) update_post_meta( $post_id, 'landing_form_shortcode', sanitize_text_field( wp_unslash( $_POST['landing_form_shortcode'] ) ) );
    else delete_post_meta( $post_id, 'landing_form_shortcode' );

    if ( isset( $_POST['landing_contact_text'] ) ) update_post_meta( $post_id, 'landing_contact_text', wp_kses_post( wp_unslash( $_POST['landing_contact_text'] ) ) );
    else delete_post_meta( $post_id, 'landing_contact_text' );

    for ( $i = 1; $i <= 3; $i++ ) {
        $label_k = "landing_social_{$i}_label";
        $url_k   = "landing_social_{$i}_url";
        $icon_k  = "landing_social_{$i}_icon_id";

        if ( isset( $_POST[ $label_k ] ) ) update_post_meta( $post_id, $label_k, sanitize_text_field( wp_unslash( $_POST[ $label_k ] ) ) );
        else delete_post_meta( $post_id, $label_k );

        if ( isset( $_POST[ $url_k ] ) ) update_post_meta( $post_id, $url_k, esc_url_raw( wp_unslash( $_POST[ $url_k ] ) ) );
        else delete_post_meta( $post_id, $url_k );

        if ( isset( $_POST[ $icon_k ] ) ) update_post_meta( $post_id, $icon_k, absint( $_POST[ $icon_k ] ) );
        else delete_post_meta( $post_id, $icon_k );
    }

    if ( isset( $_POST['landing_blocks'] ) && is_array( $_POST['landing_blocks'] ) ) {
        $raw = wp_unslash( $_POST['landing_blocks'] );
        $clean = array();

        foreach ( $raw as $item ) {
            if ( ! is_array( $item ) ) continue;

            $body  = isset( $item['body'] ) ? wp_kses_post( $item['body'] ) : '';
            $bl    = isset( $item['button_label'] ) ? sanitize_text_field( $item['button_label'] ) : '';
            $bu    = isset( $item['button_url'] ) ? esc_url_raw( $item['button_url'] ) : '';
            $bv    = ( isset( $item['button_variant'] ) && $item['button_variant'] === 'ghost' ) ? 'ghost' : 'primary';

            if ( $body === '' && $bl === '' && $bu === '' ) continue;

            $clean[] = array(
                'body'  => $body,
                'button_label' => $bl,
                'button_url'   => $bu,
                'button_variant' => $bv,
            );
        }

        update_post_meta( $post_id, '_landing_blocks', $clean );
    } else {
        delete_post_meta( $post_id, '_landing_blocks' );
    }
}, 20, 2 );