<?php
// inc/metaboxes-services.php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Metabox "Contenuti Servizio" (editor classico per Intro + gruppo aggiuntivo)
 *
 * - service_intro_body is a wp_editor (gruppo 1)
 * - at least one additional block exists by default (gruppo 2) and uses wp_editor
 * - you can add more blocks (gruppo 3 ...) with the "Aggiungi blocco di testo" button
 * - full image, phases and reviews functionality unchanged
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

    // load meta values (including service_how_title / service_how_subtitle)
    $meta_keys = array(
        'service_intro_title','service_intro_body','service_full_image_id',
        'service_reviews_title','service_reviews_sub','service_reviews_shortcode',
        'service_how_title','service_how_subtitle'
    );
    $meta = array();
    foreach ( $meta_keys as $k ) {
        $meta[ $k ] = get_post_meta( $post->ID, $k, true );
    }

    // load additional text groups (repeater) - robust decoding
    $additional_raw = get_post_meta( $post->ID, '_service_additional_texts', true );
    $additional = array();

    if ( is_array( $additional_raw ) && ! empty( $additional_raw ) ) {
        // WP already returned the unserialized array
        $additional = $additional_raw;
    } elseif ( $additional_raw ) {
        // try json_decode (legacy) then unslash / maybe_unserialize as fallback
        $decoded = json_decode( $additional_raw, true );
        if ( $decoded === null ) {
            $decoded = json_decode( wp_unslash( $additional_raw ), true );
        }
        if ( is_array( $decoded ) ) {
            $additional = $decoded;
        } else {
            $maybe = maybe_unserialize( $additional_raw );
            if ( is_array( $maybe ) ) {
                $additional = $maybe;
            }
        }
    }

    // Ensure at least 1 additional block exists (this becomes "gruppo 2")
    if ( count( $additional ) < 1 ) {
        $additional[] = array( 'title' => '', 'body' => '' );
    }

    // phases (keep as before)
    $phases = array();
    for ( $i = 1; $i <= 4; $i++ ) {
        $phases[ $i ] = array(
            'title'   => get_post_meta( $post->ID, "service_phase_{$i}_title", true ),
            'text'    => get_post_meta( $post->ID, "service_phase_{$i}_text", true ),
            'icon_id' => intval( get_post_meta( $post->ID, "service_phase_{$i}_icon_id", true ) ),
        );
    }

    // Enqueue editor assets for dynamic initialization (admin_enqueue hooks also handle this, but safe here)
    wp_enqueue_editor();
    ?>
    <div style="max-width:920px;">
      <h4><?php esc_html_e( 'Intro (titolo e testo)', 'abcontact' ); ?></h4>
      <p>
        <label for="service_intro_title"><?php esc_html_e( 'Titolo intro', 'abcontact' ); ?></label><br>
        <input type="text" id="service_intro_title" name="service_intro_title" value="<?php echo esc_attr( $meta['service_intro_title'] ); ?>" style="width:100%;">
      </p>
      <p>
        <label for="service_intro_body"><?php esc_html_e( 'Testo intro (editor)', 'abcontact' ); ?></label><br>
        <?php
        // Gruppo 1: wp_editor for intro body
        $intro_editor_settings = array(
            'textarea_name' => 'service_intro_body',
            'textarea_rows' => 8,
            'tinymce'       => array(
                'wpautop'  => true,
                'toolbar1' => 'formatselect | bold italic | bullist numlist | link unlink | removeformat',
            ),
            'quicktags'     => true,
            'editor_class'  => 'service-intro-body',
            'teeny'         => false,
        );
        wp_editor( $meta['service_intro_body'], 'service_intro_body_editor', $intro_editor_settings );
        ?>
      </p>

      <hr>

      <h4><?php esc_html_e( 'Sezione "Come funziona" (titolo & sottotitolo)', 'abcontact' ); ?></h4>
      <p>
        <label for="service_how_title"><?php esc_html_e( 'Titolo sezione (es. "Come funziona")', 'abcontact' ); ?></label><br>
        <input type="text" id="service_how_title" name="service_how_title" value="<?php echo esc_attr( $meta['service_how_title'] ); ?>" style="width:100%;">
      </p>
      <p>
        <label for="service_how_subtitle"><?php esc_html_e( 'Sottotitolo (breve descrizione sotto il titolo)', 'abcontact' ); ?></label><br>
        <textarea id="service_how_subtitle" name="service_how_subtitle" rows="2" style="width:100%;"><?php echo esc_textarea( $meta['service_how_subtitle'] ); ?></textarea>
        <small class="description"><?php esc_html_e( 'Puoi usare più righe; verranno rispettati i paragrafi in frontend.', 'abcontact' ); ?></small>
      </p>

      <hr>

      <h4><?php esc_html_e( 'Immagine full (main image)', 'abcontact' ); ?></h4>
      <p>
        <input type="hidden" id="service_full_image_id" name="service_full_image_id" value="<?php echo esc_attr( $meta['service_full_image_id'] ); ?>">
        <button type="button" class="button" id="ab-select-full-image"><?php esc_html_e( 'Seleziona immagine', 'abcontact' ); ?></button>
        <button type="button" class="button" id="ab-remove-full-image"><?php esc_html_e( 'Rimuovi immagine', 'abcontact' ); ?></button>
      </p>
      <div id="service_full_image_preview" style="margin-top:8px;">
        <?php
        if ( ! empty( $meta['service_full_image_id'] ) ) {
            if ( defined( 'SERVICE_GROUP_IMG_NAME' ) ) {
                echo wp_get_attachment_image( (int) $meta['service_full_image_id'], SERVICE_GROUP_IMG_NAME, false, array( 'class' => 'service-full-image' ) );
            } else {
                echo wp_get_attachment_image( (int) $meta['service_full_image_id'], 'medium', false, array( 'class' => 'service-full-image' ) );
            }
        }
        ?>
      </div>

      <hr>

      <h4><?php esc_html_e( 'Ulteriori gruppi di testo (verranno mostrati sotto l\\\'immagine)', 'abcontact' ); ?></h4>

      <div id="service-additional-repeater" data-repeater>
        <?php
        // Render existing additional blocks (each with wp_editor) - these are "gruppo 2", "gruppo 3", ...
        foreach ( $additional as $idx => $item ) :
            $title = isset( $item['title'] ) ? $item['title'] : '';
            $body  = isset( $item['body'] ) ? $item['body'] : '';
            $editor_id = 'service_additional_' . $idx . '_body';
            ?>
            <div class="service-add-item" data-index="<?php echo esc_attr( $idx ); ?>" style="border:1px solid #eee;padding:10px;margin-bottom:8px;border-radius:6px;background:#fff;">
              <p style="display:flex;justify-content:space-between;align-items:center;margin:0 0 8px;">
                <strong><?php echo esc_html( $title ? $title : sprintf( __( 'Blocco %d', 'abcontact' ), $idx + 1 ) ); ?></strong>
                <button type="button" class="button service-add-remove"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
              </p>

              <p>
                <label><?php esc_html_e( 'Titolo', 'abcontact' ); ?></label><br>
                <input type="text" name="service_additional[<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" style="width:100%;">
              </p>

              <p>
                <label><?php esc_html_e( 'Testo (editor)', 'abcontact' ); ?></label><br>
                <?php
                // wp_editor for each additional block
                $editor_settings = array(
                    'textarea_name' => "service_additional[{$idx}][body]",
                    'textarea_rows' => 6,
                    'tinymce'       => array(
                        'wpautop'  => true,
                        'toolbar1' => 'formatselect | bold italic | bullist numlist | link unlink | removeformat',
                    ),
                    'quicktags'     => true,
                    'editor_class'  => 'service-add-body',
                    'teeny'         => false,
                );
                wp_editor( $body, $editor_id, $editor_settings );
                ?>
              </p>
            </div>
        <?php endforeach; ?>

        <!-- template for new blocks: a textarea that will be converted to wp_editor by JS -->
        <template id="service-add-template">
          <div class="service-add-item" data-index="__index__" style="border:1px solid #eee;padding:10px;margin-bottom:8px;border-radius:6px;background:#fff;">
            <p style="display:flex;justify-content:space-between;align-items:center;margin:0 0 8px;">
              <strong><?php esc_html_e( 'Nuovo blocco', 'abcontact' ); ?></strong>
              <button type="button" class="button service-add-remove"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
            </p>

            <p>
              <label><?php esc_html_e( 'Titolo', 'abcontact' ); ?></label><br>
              <input type="text" name="service_additional[__index__][title]" value="" style="width:100%;">
            </p>

            <p>
              <label><?php esc_html_e( 'Testo (editor)', 'abcontact' ); ?></label><br>
              <textarea id="service_additional___index___body" name="service_additional[__index__][body]" rows="6" style="width:100%;"></textarea>
            </p>
          </div>
        </template>

        <p><button type="button" class="button button-primary" id="service-add-new"><?php esc_html_e( 'Aggiungi blocco di testo', 'abcontact' ); ?></button></p>
      </div>

      <hr>

      <h4><?php esc_html_e( 'Come funziona (4 fasi)', 'abcontact' ); ?></h4>
      <?php for ( $i = 1; $i <= 4; $i++ ) : ?>
        <fieldset style="margin:8px 0;padding:8px;border:1px solid #eee;border-radius:6px">
          <legend><?php echo sprintf( esc_html__( 'Fase %d', 'abcontact' ), $i ); ?></legend>

          <p><label><?php esc_html_e( 'Titolo', 'abcontact' ); ?></label><br>
          <input type="text" id="service_phase_<?php echo $i; ?>_title" name="service_phase_<?php echo $i; ?>_title" value="<?php echo esc_attr( $phases[$i]['title'] ); ?>" style="width:100%"></p>

          <p><label><?php esc_html_e( 'Testo', 'abcontact' ); ?></label><br>
          <textarea id="service_phase_<?php echo $i; ?>_text" name="service_phase_<?php echo $i; ?>_text" rows="3" style="width:100%"><?php echo esc_textarea( $phases[$i]['text'] ); ?></textarea></p>

          <p>
            <input type="hidden" id="service_phase_<?php echo $i; ?>_icon_id" name="service_phase_<?php echo $i; ?>_icon_id" value="<?php echo esc_attr( $phases[$i]['icon_id'] ); ?>">
            <button class="button service-select-icon" data-target="<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Seleziona icona', 'abcontact' ); ?></button>
            <button class="button service-remove-icon" data-target="<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Rimuovi icona', 'abcontact' ); ?></button>
            <div id="service_phase_<?php echo $i; ?>_icon_preview" style="margin-top:8px;">
              <?php if ( $phases[$i]['icon_id'] ) { echo wp_get_attachment_image( $phases[$i]['icon_id'], array(72,72) ); } ?>
            </div>
          </p>
        </fieldset>
      <?php endfor; ?>

      <hr>

      <h4><?php esc_html_e( 'Recensioni (titolo / sottotitolo / shortcode)', 'abcontact' ); ?></h4>
      <p>
        <label for="service_reviews_title"><?php esc_html_e( 'Titolo recensioni', 'abcontact' ); ?></label><br>
        <input type="text" id="service_reviews_title" name="service_reviews_title" value="<?php echo esc_attr( $meta['service_reviews_title'] ); ?>" style="width:100%">
      </p>
      <p>
        <label for="service_reviews_sub"><?php esc_html_e( 'Sottotitolo recensioni', 'abcontact' ); ?></label><br>
        <input type="text" id="service_reviews_sub" name="service_reviews_sub" value="<?php echo esc_attr( $meta['service_reviews_sub'] ); ?>" style="width:100%">
      </p>
      <p>
        <label for="service_reviews_shortcode"><?php esc_html_e( 'Shortcode recensioni', 'abcontact' ); ?></label><br>
        <input type="text" id="service_reviews_shortcode" name="service_reviews_shortcode" value="<?php echo esc_attr( $meta['service_reviews_shortcode'] ); ?>" style="width:100%" placeholder="[your_reviews id=&quot;123&quot;]">
        <small class="description"><?php esc_html_e( 'Se vuoto la sezione recensioni non verrà mostrata in frontend.', 'abcontact' ); ?></small>
      </p>

      <hr>

      <h4><?php esc_html_e( 'Anteprima CTA principale', 'abcontact' ); ?></h4>
      <p class="description">
        <?php
        echo esc_html__( 'La CTA principale è gestita centralmente. Se vuoi impostare o modificare la CTA per questa pagina usa il metabox "Front CTA" o aggiorna i meta "cta_*" della pagina.', 'abcontact' );
        ?>
      </p>

    </div>

    <script>
    (function($){
      $(document).ready(function(){
        var $repeater = $('#service-additional-repeater');
        var tpl = $('#service-add-template').html();

        // utility: create a consistent editor id for an index
        function editorIdFor(idx){
          return 'service_additional_' + idx + '_body';
        }

        function reindex() {
          $repeater.find('.service-add-item').each(function(i){
            var $it = $(this);
            $it.attr('data-index', i);
            $it.find('[name]').each(function(){
              var name = $(this).attr('name');
              name = name.replace(/\[\d+\]/, '['+i+']');
              name = name.replace(/\[__index__\]/, '['+i+']');
              $(this).attr('name', name);
            });
            // ensure textarea ids match pattern for dynamic init
            $it.find('textarea').each(function(){
              var $ta = $(this);
              var id = $ta.attr('id') || '';
              if ( id.indexOf('__index__') !== -1 || id.indexOf('___index___') !== -1 ) {
                $ta.attr('id', id.replace(/__index__|___index___/g, i));
              } else {
                // if no id or mismatch, set canonical id
                if ( $ta.attr('name') && $ta.attr('name').indexOf('service_additional['+i+'][body]') !== -1 ) {
                  $ta.attr('id', editorIdFor(i));
                }
              }
            });
          });
        }

        // remove block: also remove any editor instance
        $repeater.on('click', '.service-add-remove', function(e){
          e.preventDefault();
          var $item = $(this).closest('.service-add-item');
          $item.find('textarea').each(function(){
            var tid = $(this).attr('id');
            if ( typeof wp !== 'undefined' && wp.editor && wp.editor.remove ) {
              try { wp.editor.remove(tid); } catch(e){ /* ignore */ }
            }
            if ( typeof tinyMCE !== 'undefined' && tinyMCE.get(tid) ) {
              try { tinyMCE.get(tid).remove(); } catch(e) { /* ignore */ }
            }
          });
          $item.remove();
          reindex();
        });

        // add new block: insert template, set ids/names and initialize editor
        $('#service-add-new').on('click', function(e){
          e.preventDefault();
          var idx = $repeater.find('.service-add-item').length;
          var html = tpl.replace(/__index__/g, idx).replace(/___index___/g, idx);
          $repeater.find('> p').first().before(html);
          reindex();

          var textareaId = editorIdFor(idx);
          var textarea = document.getElementById(textareaId) || document.querySelector('#service-additional-repeater textarea[name="service_additional['+idx+'][body]"]');
          if ( textarea ) {
            // initialize editor (wp.editor if available)
            if ( typeof wp !== 'undefined' && wp.editor && wp.editor.initialize ) {
              wp.editor.initialize(textareaId, {
                tinymce: {
                  wpautop: true,
                  toolbar1: 'formatselect | bold italic | bullist numlist | link unlink | removeformat'
                },
                quicktags: true,
                textarea_name: 'service_additional['+idx+'][body]'
              });
            } else if ( typeof tinyMCE !== 'undefined' ) {
              tinyMCE.init({
                selector: '#' + textareaId,
                menubar: false,
                toolbar: 'formatselect | bold italic | bullist numlist | link unlink | removeformat'
              });
            }
          }
        });

        // On load: if any additional textarea exists without editor (edge-case), ensure it has id and quicktags
        (function ensureExisting(){
          $repeater.find('.service-add-item').each(function(i){
            $(this).find('textarea').each(function(){
              var $ta = $(this);
              if ( !$ta.attr('id') ) {
                $ta.attr('id', editorIdFor(i));
              }
            });
          });
        })();

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

    // Keys to handle (including new how title/subtitle)
    $keys = array(
        'service_intro_title',
        'service_intro_body',
        'service_full_image_id',
        'service_reviews_title',
        'service_reviews_sub',
        'service_reviews_shortcode',
        'service_how_title',
        'service_how_subtitle',
    );

    foreach ( $keys as $k ) {
        if ( isset( $_POST[ $k ] ) ) {
            $raw = wp_unslash( $_POST[ $k ] );
            if ( $k === 'service_full_image_id' ) {
                update_post_meta( $post_id, $k, absint( $raw ) );
            } elseif ( in_array( $k, array( 'service_intro_body', 'service_reviews_sub', 'service_reviews_shortcode', 'service_how_subtitle' ), true ) ) {
                // allow limited safe HTML and preserve paragraphs/newlines
                update_post_meta( $post_id, $k, wp_kses_post( $raw ) );
            } else {
                update_post_meta( $post_id, $k, sanitize_text_field( trim( $raw ) ) );
            }
        } else {
            delete_post_meta( $post_id, $k );
        }
    }

    // Save repeater additional texts (service_additional) - save as array (WP serializes)
    if ( isset( $_POST['service_additional'] ) && is_array( $_POST['service_additional'] ) ) {
        $raw_arr = wp_unslash( $_POST['service_additional'] );
        $clean = array();
        foreach ( $raw_arr as $item ) {
            if ( ! is_array( $item ) ) continue;

            $title = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';

            // Normalize newlines to \n and sanitize body allowing safe HTML
            $body_raw = isset( $item['body'] ) ? $item['body'] : '';
            $body_raw = str_replace( array( "\r\n", "\r" ), "\n", $body_raw );
            // Allow safe HTML (links, headings, paragraphs).
            $body = wp_kses_post( $body_raw );

            // Skip empty blocks
            if ( $title === '' && $body === '' ) {
                continue;
            }

            $clean[] = array(
                'title' => $title,
                'body'  => $body,
            );
        }

        if ( ! empty( $clean ) ) {
            // Save array directly; WP will serialize it safely (preserves newlines/accents)
            update_post_meta( $post_id, '_service_additional_texts', $clean );
        } else {
            delete_post_meta( $post_id, '_service_additional_texts' );
        }
    } else {
        // do not delete existing if none sent intentionally
    }

    // Phases: title (sanitized), text (allow safe HTML), icon_id (absint)
    for ( $i = 1; $i <= 4; $i++ ) {
        $title_key = "service_phase_{$i}_title";
        $text_key  = "service_phase_{$i}_text";
        $icon_key  = "service_phase_{$i}_icon_id";

        if ( isset( $_POST[ $title_key ] ) ) {
            update_post_meta( $post_id, $title_key, sanitize_text_field( wp_unslash( $_POST[ $title_key ] ) ) );
        } else {
            delete_post_meta( $post_id, $title_key );
        }

        if ( isset( $_POST[ $text_key ] ) ) {
            // allow safe HTML and preserve paragraphs/newlines
            update_post_meta( $post_id, $text_key, wp_kses_post( wp_unslash( $_POST[ $text_key ] ) ) );
        } else {
            delete_post_meta( $post_id, $text_key );
        }

        if ( isset( $_POST[ $icon_key ] ) ) {
            update_post_meta( $post_id, $icon_key, absint( $_POST[ $icon_key ] ) );
        } else {
            delete_post_meta( $post_id, $icon_key );
        }
    }
}
add_action( 'save_post', 'abcontact_save_service_metabox', 20 );

/* Enqueue admin scripts/styles for the metabox (media + optional admin js) */
function abcontact_enqueue_service_admin_assets( $hook ) {
    // Only run on post edit/new pages
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
        return;
    }

    // Resolve post ID robustly
    $post_id = 0;
    if ( isset( $GLOBALS['post'] ) && is_object( $GLOBALS['post'] ) && isset( $GLOBALS['post']->ID ) ) {
        $post_id = (int) $GLOBALS['post']->ID;
    }
    if ( empty( $post_id ) && isset( $_GET['post'] ) ) {
        $post_id = (int) $_GET['post'];
    }
    if ( empty( $post_id ) && isset( $_GET['post_id'] ) ) {
        $post_id = (int) $_GET['post_id'];
    }
    if ( empty( $post_id ) ) {
        return;
    }

    $post = get_post( $post_id );
    if ( ! $post ) {
        return;
    }

    // Only for pages
    if ( $post->post_type !== 'page' ) {
        return;
    }

    $tpl = get_post_meta( $post_id, '_wp_page_template', true );
    $tpl = $tpl ? basename( $tpl ) : '';

    // Allowed templates / legacy slug
    $allowed_templates = array( 'page-service-template.php', 'page-chi-siamo.php', 'front-page.php' );
    if ( ! in_array( $tpl, $allowed_templates, true ) && $post->post_name !== 'chi-siamo' ) {
        return;
    }

    // Ensure core libs are registered/enqueued
    wp_enqueue_editor();
    wp_enqueue_media();

    // Candidate admin JS (media picker etc.)
    $candidates = array(
        '/assets/js/service-metabox.js',
        '/assets/js/admin/service-metabox.js',
        '/assets/js/admin/service-metabox.min.js',
        '/assets/js/service-metabox.min.js',
    );

    $found = false;
    $src = '';
    $ver = null;
    foreach ( $candidates as $rel ) {
        $child_path  = get_stylesheet_directory() . $rel;
        $parent_path = get_template_directory() . $rel;

        if ( file_exists( $child_path ) ) {
            $src   = get_stylesheet_directory_uri() . $rel;
            $ver   = filemtime( $child_path );
            $found = true;
            break;
        }
        if ( file_exists( $parent_path ) ) {
            $src   = get_template_directory_uri() . $rel;
            $ver   = filemtime( $parent_path );
            $found = true;
            break;
        }
    }

    if ( $found ) {
        wp_register_script( 'ab-service-metabox', $src, array( 'jquery', 'wp-editor', 'media-editor' ), $ver, true );
        wp_enqueue_script( 'ab-service-metabox' );
        wp_localize_script( 'ab-service-metabox', 'abServiceMetabox', array(
            'postId' => $post_id,
            'nonce'  => wp_create_nonce( 'ab_service_metabox' ),
        ) );
    }

    // Optional admin CSS for metabox UI
    $admin_css = get_stylesheet_directory() . '/assets/css/admin-metaboxes.css';
    if ( file_exists( $admin_css ) ) {
        wp_enqueue_style( 'ab-admin-metabox', get_stylesheet_directory_uri() . '/assets/css/admin-metaboxes.css', array(), filemtime( $admin_css ) );
    }
}
add_action( 'admin_enqueue_scripts', 'abcontact_enqueue_service_admin_assets', 10 );