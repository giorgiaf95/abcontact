<?php
/**
 * Theme metabox: Impostazioni Pagina Sedi
 *
 * Path: wp-content/themes/your-theme/inc/metaboxes-sedi.php
 *
 * - Il metabox è visibile e funzionante solo per pagine che usano il template
 *   page-sedi.php (o page-templates/page-sedi.php) o per la pagina slug 'sedi'.
 * - Enqueue JS/CSS admin solo quando si modifica quella pagina.
 * - Salvataggio robusto: non sovrascrive meta se i campi non sono stati inviati.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ---------- Helper: è la pagina Sedi? ---------- */
if ( ! function_exists( 'theme_ab_is_sedi_page' ) ) {
    function theme_ab_is_sedi_page( $post = null ) {
        if ( ! $post ) {
            if ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) {
                $post = $GLOBALS['post'];
            } elseif ( isset( $_GET['post'] ) ) {
                $post = get_post( (int) $_GET['post'] );
            } else {
                return false;
            }
        }

        if ( ! $post || 'page' !== get_post_type( $post ) ) {
            return false;
        }

        $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
        $tpl = $tpl ? basename( $tpl ) : '';

        $allowed_templates = array( 'page-sedi.php', 'page-templates/page-sedi.php' );
        if ( in_array( $tpl, $allowed_templates, true ) ) {
            return true;
        }

        if ( isset( $post->post_name ) && $post->post_name === 'sedi' ) {
            return true;
        }

        return false;
    }
}

/* ---------- Rimuovi metabox noti confliggenti (solo per pagine Sedi) ---------- */
add_action( 'add_meta_boxes', function() {
    $post = null;
    if ( isset( $_GET['post'] ) ) {
        $post = get_post( (int) $_GET['post'] );
    } elseif ( isset( $GLOBALS['post'] ) ) {
        $post = $GLOBALS['post'];
    }

    if ( ! $post || ! theme_ab_is_sedi_page( $post ) ) {
        return;
    }

    // Rimuove metaboxs noti che potrebbero essere registrati da plugin o da versioni vecchie:
    $conflicts = array( 'abcontact_sedi_page_meta', 'sedi_page_meta', 'sedi_meta', 'abcontact_sedi_page_meta', 'ab_service_fields' );
    foreach ( $conflicts as $id ) {
        remove_meta_box( $id, 'page', 'normal' );
        remove_meta_box( $id, 'page', 'advanced' );
        remove_meta_box( $id, 'page', 'side' );
    }
}, 11 );

/* ---------- Registra il metabox del tema (solo per Sedi) ---------- */
add_action( 'add_meta_boxes', function() {
    // prova a recuperare il post (classic editor)
    $post = null;
    if ( isset( $_GET['post'] ) ) {
        $post = get_post( (int) $_GET['post'] );
    } elseif ( isset( $GLOBALS['post'] ) ) {
        $post = $GLOBALS['post'];
    }

    if ( ! $post || ! theme_ab_is_sedi_page( $post ) ) {
        return;
    }

    add_meta_box(
        'theme_ab_sedi_meta',
        __( 'Impostazioni Pagina Sedi', 'theme-abcontact' ),
        'theme_ab_render_sedi_metabox',
        'page',
        'normal',
        'high'
    );
}, 20 );

/* ---------- Render metabox ---------- */
if ( ! function_exists( 'theme_ab_render_sedi_metabox' ) ) {
    function theme_ab_render_sedi_metabox( $post ) {
        if ( ! theme_ab_is_sedi_page( $post ) ) {
            echo '<p>' . esc_html__( 'Questo metabox è disponibile solo per il template "Sedi".', 'theme-abcontact' ) . '</p>';
            return;
        }

        wp_nonce_field( 'theme_ab_save_sedi_meta', 'theme_ab_sedi_nonce' );

        $intro = get_post_meta( $post->ID, 'sedi_intro_text', true );
        $map_shortcode = get_post_meta( $post->ID, 'sedi_map_shortcode', true );

        $blocks = get_post_meta( $post->ID, 'sedi_blocks', true );
        if ( is_string( $blocks ) && $blocks !== '' ) {
            $blocks = maybe_unserialize( $blocks );
        }
        if ( ! is_array( $blocks ) ) $blocks = array();

        $cta_title = get_post_meta( $post->ID, 'sedi_cta_title', true );
        $cta_text  = get_post_meta( $post->ID, 'sedi_cta_text', true );
        $cta_btn_label = get_post_meta( $post->ID, 'sedi_cta_button_label', true );
        $cta_btn_link  = get_post_meta( $post->ID, 'sedi_cta_button_link', true );
        $cta_phone = get_post_meta( $post->ID, 'sedi_cta_support_phone', true );
        $cta_email = get_post_meta( $post->ID, 'sedi_cta_support_email', true );
        ?>

        <div class="theme-ab-sedi-metabox" style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;">
          <div style="flex:1;min-width:320px;max-width:820px;">
            <p>
              <label for="sedi_intro_text"><strong><?php esc_html_e( 'Testo introduttivo (hero / lead)', 'theme-abcontact' ); ?></strong></label><br>
              <textarea id="sedi_intro_text" name="sedi_intro_text" rows="3" style="width:100%;"><?php echo esc_textarea( $intro ); ?></textarea>
            </p>

            <p>
              <label for="sedi_map_shortcode"><strong><?php esc_html_e( 'Shortcode mappa', 'theme-abcontact' ); ?></strong></label><br>
              <input type="text" id="sedi_map_shortcode" name="sedi_map_shortcode" value="<?php echo esc_attr( $map_shortcode ); ?>" style="width:100%;" placeholder="[nome_plugin_mappa id=123]">
            </p>

            <hr>

            <h4><?php esc_html_e( 'Gruppi (titolo + testo + elenco puntato)', 'theme-abcontact' ); ?></h4>

            <div id="theme-ab-sedi-blocks" style="margin-bottom:8px;">
              <?php
              if ( empty( $blocks ) ) {
                  // default: un blocco vuoto
                  $blocks = array( array( 'title' => '', 'body' => '', 'bullets' => array( '' ) ) );
              }

              foreach ( $blocks as $i => $blk ) :
                  $title = isset( $blk['title'] ) ? $blk['title'] : '';
                  $body  = isset( $blk['body'] ) ? $blk['body'] : '';
                  $bullets = isset( $blk['bullets'] ) && is_array( $blk['bullets'] ) ? $blk['bullets'] : array( '' );
                  ?>
                  <div class="theme-ab-sedi-block" data-index="<?php echo esc_attr( $i ); ?>" style="border:1px solid #e6e9ee;padding:10px;margin-bottom:10px;border-radius:6px;background:#fff;">
                    <p style="display:flex;justify-content:space-between;align-items:center;margin:0 0 8px;">
                      <strong><?php echo esc_html__( 'Blocco', 'theme-abcontact' ) . ' ' . ( $i + 1 ); ?></strong>
                      <button type="button" class="button theme-ab-remove-block" style="background:#f6f6f8;border:1px solid #ddd;padding:4px 8px;"><?php esc_html_e( 'Rimuovi', 'theme-abcontact' ); ?></button>
                    </p>

                    <p style="margin:8px 0;">
                      <label><?php esc_html_e( 'Titolo', 'theme-abcontact' ); ?></label><br>
                      <input class="theme-ab-block-title" name="sedi_blocks[<?php echo esc_attr( $i ); ?>][title]" type="text" value="<?php echo esc_attr( $title ); ?>" style="width:100%">
                    </p>

                    <p style="margin:8px 0;">
                      <label><?php esc_html_e( 'Testo (corpo)', 'theme-abcontact' ); ?></label><br>
                      <textarea class="theme-ab-block-body" name="sedi_blocks[<?php echo esc_attr( $i ); ?>][body]" rows="3" style="width:100%;"><?php echo esc_textarea( $body ); ?></textarea>
                    </p>

                    <div class="theme-ab-block-bullets" style="margin:8px 0;">
                      <label><?php esc_html_e( 'Elenco puntato', 'theme-abcontact' ); ?></label>
                      <div class="theme-ab-bullets-list" style="margin-top:6px;">
                        <?php
                        if ( empty( $bullets ) ) $bullets = array( '' );
                        foreach ( $bullets as $bi => $b ) : ?>
                          <div style="display:flex;gap:8px;margin-bottom:6px;">
                            <input class="theme-ab-block-bullet" name="sedi_blocks[<?php echo esc_attr( $i ); ?>][bullets][]" type="text" value="<?php echo esc_attr( $b ); ?>" style="flex:1">
                            <button type="button" class="button theme-ab-remove-bullet" style="background:#f6f6f8;border:1px solid #ddd;padding:4px 8px;">&times;</button>
                          </div>
                        <?php endforeach; ?>
                      </div>
                      <p style="margin:6px 0 0;">
                        <button type="button" class="button button-primary theme-ab-add-bullet"><?php esc_html_e( 'Aggiungi voce', 'theme-abcontact' ); ?></button>
                      </p>
                    </div>
                  </div>
              <?php endforeach; ?>
            </div>

            <p><button type="button" id="theme-ab-add-block" class="button button-primary"><?php esc_html_e( 'Aggiungi Blocco', 'theme-abcontact' ); ?></button></p>

          </div>

          <div style="width:360px;box-sizing:border-box;">
            <h4><?php esc_html_e( 'CTA (box blu)', 'theme-abcontact' ); ?></h4>

            <p>
              <label for="sedi_cta_title"><?php esc_html_e( 'Titolo CTA', 'theme-abcontact' ); ?></label><br>
              <input type="text" id="sedi_cta_title" name="sedi_cta_title" value="<?php echo esc_attr( $cta_title ); ?>" style="width:100%;">
            </p>

            <p>
              <label for="sedi_cta_text"><?php esc_html_e( 'Testo CTA', 'theme-abcontact' ); ?></label><br>
              <textarea id="sedi_cta_text" name="sedi_cta_text" rows="4" style="width:100%;"><?php echo esc_textarea( $cta_text ); ?></textarea>
            </p>

            <p>
              <label for="sedi_cta_button_label"><?php esc_html_e( 'Testo bottone', 'theme-abcontact' ); ?></label><br>
              <input type="text" id="sedi_cta_button_label" name="sedi_cta_button_label" value="<?php echo esc_attr( $cta_btn_label ); ?>" style="width:100%;">
            </p>

            <p>
              <label for="sedi_cta_button_link"><?php esc_html_e( 'Link bottone', 'theme-abcontact' ); ?></label><br>
              <input type="text" id="sedi_cta_button_link" name="sedi_cta_button_link" value="<?php echo esc_attr( $cta_btn_link ); ?>" style="width:100%;">
            </p>

            <p>
              <label for="sedi_cta_support_phone"><?php esc_html_e( 'Numero Verde', 'theme-abcontact' ); ?></label><br>
              <input type="text" id="sedi_cta_support_phone" name="sedi_cta_support_phone" value="<?php echo esc_attr( $cta_phone ); ?>" style="width:100%;">
            </p>

            <p>
              <label for="sedi_cta_support_email"><?php esc_html_e( 'Email di contatto', 'theme-abcontact' ); ?></label><br>
              <input type="email" id="sedi_cta_support_email" name="sedi_cta_support_email" value="<?php echo esc_attr( $cta_email ); ?>" style="width:100%;">
            </p>

          </div>
        </div>

        <style>
          /* small admin styling to keep layout tidy */
          .theme-ab-sedi-metabox .button { cursor: pointer; }
        </style>

        <?php
    }
}

/* ---------- Save handler (robusto) ---------- */
add_action( 'save_post_page', function( $post_id, $post ) {
    // Only pages
    if ( ! $post || 'page' !== $post->post_type ) {
        return;
    }
    // Guards
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! isset( $_POST['theme_ab_sedi_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['theme_ab_sedi_nonce'] ), 'theme_ab_save_sedi_meta' ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Save simple fields (only update if present; do not delete if absent)
    $simple_map = array(
        'sedi_intro_text' => 'sanitize_textarea_field',
        'sedi_map_shortcode' => 'sanitize_text_field',
        'sedi_cta_title' => 'sanitize_text_field',
        'sedi_cta_text' => 'wp_kses_post',
        'sedi_cta_button_label' => 'sanitize_text_field',
        'sedi_cta_button_link' => 'abcontact_sanitize_cta_link',
        'sedi_cta_support_phone' => 'sanitize_text_field',
        'sedi_cta_support_email' => 'sanitize_email',
    );

    foreach ( $simple_map as $key => $sanitize_cb ) {
        if ( isset( $_POST[ $key ] ) ) {
            $val = wp_unslash( $_POST[ $key ] );
            $clean = is_callable( $sanitize_cb ) ? call_user_func( $sanitize_cb, $val ) : sanitize_text_field( $val );
            update_post_meta( $post_id, $key, $clean );
        }
    }

    // Repeater: sedi_blocks
    if ( array_key_exists( 'sedi_blocks', $_POST ) ) {
        if ( isset( $_POST['sedi_blocks'] ) && is_array( $_POST['sedi_blocks'] ) ) {
            $raw_blocks = wp_unslash( $_POST['sedi_blocks'] );
            $blocks = array();
            foreach ( $raw_blocks as $blk ) {
                if ( ! is_array( $blk ) ) continue;
                $title = isset( $blk['title'] ) ? sanitize_text_field( $blk['title'] ) : '';
                $body  = isset( $blk['body'] ) ? wp_kses_post( $blk['body'] ) : '';
                $bullets = array();
                if ( isset( $blk['bullets'] ) && is_array( $blk['bullets'] ) ) {
                    foreach ( $blk['bullets'] as $b ) {
                        $b_clean = sanitize_text_field( $b );
                        if ( $b_clean !== '' ) $bullets[] = $b_clean;
                    }
                }
                if ( $title === '' && $body === '' && empty( $bullets ) ) {
                    continue;
                }
                $blocks[] = array( 'title' => $title, 'body' => $body, 'bullets' => $bullets );
            }

            if ( ! empty( $blocks ) ) {
                update_post_meta( $post_id, 'sedi_blocks', $blocks );
            } else {
                delete_post_meta( $post_id, 'sedi_blocks' );
            }
        } else {
            // submitted but not array: remove meta defensivamente
            delete_post_meta( $post_id, 'sedi_blocks' );
        }
    }

}, 10, 2 );

/* ---------- Admin enqueue: JS + CSS only when editing Sedi page ---------- */
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
        return;
    }

    // detect current post
    $post = null;
    if ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) {
        $post = $GLOBALS['post'];
    } elseif ( isset( $_GET['post'] ) ) {
        $post = get_post( (int) $_GET['post'] );
    }

    if ( ! $post || 'page' !== get_post_type( $post ) ) {
        return;
    }
    if ( ! theme_ab_is_sedi_page( $post ) ) {
        return;
    }

    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    $js_path = $theme_dir . '/assets/js/metaboxes-sedi.js';
    $js_url  = $theme_uri . '/assets/js/metaboxes-sedi.js';
    if ( file_exists( $js_path ) ) {
        wp_enqueue_script( 'theme-ab-metaboxes-sedi', $js_url, array( 'jquery' ), filemtime( $js_path ), true );
    }

    $admin_css = $theme_dir . '/assets/css/sedi-admin.css';
    $admin_css_url = $theme_uri . '/assets/css/sedi-admin.css';
    if ( file_exists( $admin_css ) ) {
        wp_enqueue_style( 'theme-ab-sedi-admin', $admin_css_url, array(), filemtime( $admin_css ) );
    }
}, 20 );

/* ---------- helper to sanitize CTA link (allow '#' placeholder) ---------- */
if ( ! function_exists( 'abcontact_sanitize_cta_link' ) ) {
    function abcontact_sanitize_cta_link( $val ) {
        if ( '#' === trim( $val ) ) {
            return '#';
        }
        return esc_url_raw( $val );
    }
}