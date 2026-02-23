<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Metaboxes for "Lavora con noi" page (v2)
 * - Repeater for "Posizioni Aperte"
 * - Stores data in: _ab_lc_positions_v2 (array)
 *
 * NOTE: This intentionally stops using legacy meta `_lc_positions`.
 */

const ABCONTACT_LC_POSITIONS_META_V2 = '_ab_lc_positions_v2';

/* Helper: detect Lavora page/template (safe, reusable) */
if ( ! function_exists( 'abcontact_is_lavora_post' ) ) {
    function abcontact_is_lavora_post( $post = null ) {
        if ( ! $post ) {
            if ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) {
                $post = $GLOBALS['post'];
            } elseif ( isset( $_GET['post'] ) ) {
                $post = get_post( (int) $_GET['post'] );
            } elseif ( isset( $_POST['post_ID'] ) ) {
                $post = get_post( (int) $_POST['post_ID'] );
            } else {
                return false;
            }
        }

        if ( ! $post || 'page' !== get_post_type( $post ) ) {
            return false;
        }

        $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
        $tpl = $tpl ? basename( $tpl ) : '';

        $allowed_templates = array(
            'page-lavora.php',
            'page-lavora-con-noi.php',
            'page-lavora-con-noi-template.php',
            'page-templates/page-lavora.php',
            'page-templates/page-lavora-con-noi.php',
        );

        if ( in_array( $tpl, $allowed_templates, true ) ) {
            return true;
        }

        $allowed_slugs = array( 'lavora', 'lavora-con-noi', 'lavora-con-noi-page' );
        if ( isset( $post->post_name ) && in_array( $post->post_name, $allowed_slugs, true ) ) {
            return true;
        }

        return false;
    }
}

/* Register metabox only when editing the Lavora page/template */
if ( ! function_exists( 'ab_lavora_register_metaboxes' ) ) {
    function ab_lavora_register_metaboxes() {
        $post = null;
        if ( isset( $_GET['post'] ) ) {
            $post = get_post( (int) $_GET['post'] );
        } elseif ( isset( $GLOBALS['post'] ) ) {
            $post = $GLOBALS['post'];
        } elseif ( isset( $_POST['post_ID'] ) ) {
            $post = get_post( (int) $_POST['post_ID'] );
        }

        if ( ! abcontact_is_lavora_post( $post ) ) {
            return;
        }

        add_meta_box(
            'ab_lavora_positions_v2',
            __( 'Lavora con noi — Posizioni Aperte', 'abcontact' ),
            'ab_lavora_metabox_render_v2',
            'page',
            'normal',
            'high'
        );
    }
    add_action( 'add_meta_boxes', 'ab_lavora_register_metaboxes' );
}

if ( ! function_exists( 'abcontact_lc_v2_generate_id' ) ) {
    function abcontact_lc_v2_generate_id() {
        // stable-enough random id for a repeater item
        $rand = function_exists( 'wp_generate_password' ) ? wp_generate_password( 8, false, false ) : substr( md5( uniqid( '', true ) ), 0, 8 );
        return 'job_' . time() . '_' . strtolower( $rand );
    }
}

if ( ! function_exists( 'ab_lavora_metabox_render_v2' ) ) {
    function ab_lavora_metabox_render_v2( $post ) {
        wp_nonce_field( 'ab_lavora_positions_v2_save', 'ab_lavora_positions_v2_nonce' );

        $positions_raw = get_post_meta( $post->ID, ABCONTACT_LC_POSITIONS_META_V2, true );
        $positions = is_array( $positions_raw ) ? $positions_raw : array();

        ?>
        <style>
          .ab-lc-field{ width:100%; box-sizing:border-box; padding:6px 8px; }
          .lc-positions-repeater { margin-top: 12px; }
          .lc-position-item { border:1px solid #e6e9ee; padding:10px; margin-bottom:10px; border-radius:6px; background:#fff; }
          .lc-position-item .handle { cursor:move; font-weight:600; color:#0b5fff; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;}
          .lc-position-row { display:flex; gap:10px; align-items:flex-start; margin-bottom:10px; }
          .lc-position-row .col { flex:1; }
          .lc-position-actions { text-align:right; display:flex; gap:8px; align-items:center; }
          .lc-position-help { color:#6b7280; margin:6px 0 0; font-size: 12px; }
        </style>

        <h4><?php esc_html_e( 'Posizioni Aperte', 'abcontact' ); ?></h4>
        <p class="description"><?php esc_html_e( 'Aggiungi, riordina o rimuovi le posizioni. Questi dati alimentano la lista e il popup di candidatura nel frontend.', 'abcontact' ); ?></p>

        <div class="lc-positions-repeater" data-repeater="lc-v2">
          <?php
          if ( ! empty( $positions ) ) :
            foreach ( $positions as $index => $pos ) :
              if ( ! is_array( $pos ) ) continue;

              $id = isset( $pos['id'] ) ? sanitize_text_field( $pos['id'] ) : '';
              if ( $id === '' ) $id = abcontact_lc_v2_generate_id();

              $title = isset( $pos['title'] ) ? sanitize_text_field( $pos['title'] ) : '';
              $category = isset( $pos['category'] ) ? sanitize_text_field( $pos['category'] ) : '';
              $employment_type = isset( $pos['employment_type'] ) ? sanitize_text_field( $pos['employment_type'] ) : 'full_time';
              $location = isset( $pos['location'] ) ? sanitize_text_field( $pos['location'] ) : '';
              $description = isset( $pos['description'] ) ? $pos['description'] : '';
              $description = is_string( $description ) ? wp_kses_post( $description ) : '';
          ?>
            <div class="lc-position-item" data-index="<?php echo esc_attr( $index ); ?>">
              <div class="handle">
                <span class="label"><?php echo esc_html( $title ? $title : sprintf( __( 'Posizione %d', 'abcontact' ), $index + 1 ) ); ?></span>
                <div class="lc-position-actions">
                  <button type="button" class="button lc-position-remove"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
                </div>
              </div>

              <input type="hidden" name="lc_positions_v2[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $id ); ?>">

              <div class="lc-position-row">
                <div class="col">
                  <label><?php esc_html_e( 'Nome posizione', 'abcontact' ); ?> *</label>
                  <input type="text" name="lc_positions_v2[<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" class="ab-lc-field" required>
                </div>
                <div class="col">
                  <label><?php esc_html_e( 'Categoria', 'abcontact' ); ?></label>
                  <input type="text" name="lc_positions_v2[<?php echo esc_attr( $index ); ?>][category]" value="<?php echo esc_attr( $category ); ?>" class="ab-lc-field" placeholder="<?php esc_attr_e( 'Es. Consulenza', 'abcontact' ); ?>">
                </div>
              </div>

              <div class="lc-position-row">
                <div class="col">
                  <label><?php esc_html_e( 'Tipo contratto', 'abcontact' ); ?></label>
                  <select name="lc_positions_v2[<?php echo esc_attr( $index ); ?>][employment_type]" class="ab-lc-field">
                    <option value="full_time" <?php selected( $employment_type, 'full_time' ); ?>><?php esc_html_e( 'Full-time', 'abcontact' ); ?></option>
                    <option value="part_time" <?php selected( $employment_type, 'part_time' ); ?>><?php esc_html_e( 'Part-time', 'abcontact' ); ?></option>
                  </select>
                </div>
                <div class="col">
                  <label><?php esc_html_e( 'Luogo', 'abcontact' ); ?></label>
                  <input type="text" name="lc_positions_v2[<?php echo esc_attr( $index ); ?>][location]" value="<?php echo esc_attr( $location ); ?>" class="ab-lc-field" placeholder="<?php esc_attr_e( 'Es. Milano / Remoto', 'abcontact' ); ?>">
                </div>
              </div>

              <div>
                <label><?php esc_html_e( 'Descrizione (popup)', 'abcontact' ); ?></label>
                <textarea name="lc_positions_v2[<?php echo esc_attr( $index ); ?>][description]" rows="5" class="ab-lc-field"><?php echo esc_textarea( wp_strip_all_tags( $description ) ); ?></textarea>
                <p class="lc-position-help"><?php esc_html_e( 'Questo testo viene mostrato nel popup quando l’utente clicca sulla posizione.', 'abcontact' ); ?></p>
              </div>

            </div>
          <?php
            endforeach;
          endif;
          ?>

          <template id="lc-position-template-v2">
            <div class="lc-position-item" data-index="__index__">
              <div class="handle">
                <span class="label"><?php esc_html_e( 'Nuova posizione', 'abcontact' ); ?></span>
                <div class="lc-position-actions">
                  <button type="button" class="button lc-position-remove"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
                </div>
              </div>

              <input type="hidden" name="lc_positions_v2[__index__][id]" value="">

              <div class="lc-position-row">
                <div class="col">
                  <label><?php esc_html_e( 'Nome posizione', 'abcontact' ); ?> *</label>
                  <input type="text" name="lc_positions_v2[__index__][title]" value="" class="ab-lc-field" required>
                </div>
                <div class="col">
                  <label><?php esc_html_e( 'Categoria', 'abcontact' ); ?></label>
                  <input type="text" name="lc_positions_v2[__index__][category]" value="" class="ab-lc-field" placeholder="<?php esc_attr_e( 'Es. Consulenza', 'abcontact' ); ?>">
                </div>
              </div>

              <div class="lc-position-row">
                <div class="col">
                  <label><?php esc_html_e( 'Tipo contratto', 'abcontact' ); ?></label>
                  <select name="lc_positions_v2[__index__][employment_type]" class="ab-lc-field">
                    <option value="full_time"><?php esc_html_e( 'Full-time', 'abcontact' ); ?></option>
                    <option value="part_time"><?php esc_html_e( 'Part-time', 'abcontact' ); ?></option>
                  </select>
                </div>
                <div class="col">
                  <label><?php esc_html_e( 'Luogo', 'abcontact' ); ?></label>
                  <input type="text" name="lc_positions_v2[__index__][location]" value="" class="ab-lc-field" placeholder="<?php esc_attr_e( 'Es. Milano / Remoto', 'abcontact' ); ?>">
                </div>
              </div>

              <div>
                <label><?php esc_html_e( 'Descrizione (popup)', 'abcontact' ); ?></label>
                <textarea name="lc_positions_v2[__index__][description]" rows="5" class="ab-lc-field"></textarea>
              </div>
            </div>
          </template>

          <p><button type="button" class="button button-primary" id="lc-add-position-v2"><?php esc_html_e( 'Aggiungi Posizione', 'abcontact' ); ?></button></p>
        </div>
        <?php
    }
}

/* Save handler (v2) */
if ( ! function_exists( 'ab_lavora_metabox_save' ) ) {
    function ab_lavora_metabox_save( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( ! isset( $_POST['ab_lavora_positions_v2_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ab_lavora_positions_v2_nonce'] ), 'ab_lavora_positions_v2_save' ) ) {
            return;
        }

        $raw = isset( $_POST['lc_positions_v2'] ) && is_array( $_POST['lc_positions_v2'] ) ? wp_unslash( $_POST['lc_positions_v2'] ) : array();
        $clean = array();

        foreach ( $raw as $item ) {
            if ( ! is_array( $item ) ) continue;

            $id = isset( $item['id'] ) ? sanitize_text_field( $item['id'] ) : '';
            if ( $id === '' ) $id = abcontact_lc_v2_generate_id();

            $title = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';
            $category = isset( $item['category'] ) ? sanitize_text_field( $item['category'] ) : '';
            $employment_type = isset( $item['employment_type'] ) ? sanitize_text_field( $item['employment_type'] ) : 'full_time';
            if ( ! in_array( $employment_type, array( 'full_time', 'part_time' ), true ) ) {
                $employment_type = 'full_time';
            }
            $location = isset( $item['location'] ) ? sanitize_text_field( $item['location'] ) : '';
            $description = isset( $item['description'] ) ? sanitize_textarea_field( $item['description'] ) : '';

            // Skip empty rows
            if ( $title === '' && $category === '' && $location === '' && $description === '' ) {
                continue;
            }

            $clean[] = array(
                'id'              => $id,
                'title'           => $title,
                'category'        => $category,
                'employment_type' => $employment_type,
                'location'        => $location,
                'description'     => $description,
            );
        }

        update_post_meta( $post_id, ABCONTACT_LC_POSITIONS_META_V2, $clean );
    }
    add_action( 'save_post', 'ab_lavora_metabox_save' );
}

/* Admin enqueue for Lavora metabox (only when editing the Lavora page/template) */
if ( ! function_exists( 'ab_lavora_admin_assets' ) ) {
    function ab_lavora_admin_assets( $hook ) {
        global $post;
        if ( ( $hook !== 'post.php' && $hook !== 'post-new.php' ) ) {
            return;
        }

        $post_obj = null;
        if ( isset( $post ) && $post instanceof WP_Post ) {
            $post_obj = $post;
        } elseif ( isset( $_GET['post'] ) ) {
            $post_obj = get_post( (int) $_GET['post'] );
        } elseif ( isset( $_POST['post_ID'] ) ) {
            $post_obj = get_post( (int) $_POST['post_ID'] );
        }

        if ( ! $post_obj || $post_obj->post_type !== 'page' ) {
            return;
        }
        if ( ! abcontact_is_lavora_post( $post_obj ) ) {
            return;
        }

        $theme_dir = get_stylesheet_directory();
        $theme_uri = get_stylesheet_directory_uri();

        $js1 = $theme_dir . '/assets/js/admin-lavora-metabox.js';
        if ( file_exists( $js1 ) ) {
            wp_enqueue_script( 'ab-lavora-admin', $theme_uri . '/assets/js/admin-lavora-metabox.js', array( 'jquery' ), filemtime( $js1 ), true );
            wp_localize_script( 'ab-lavora-admin', 'abLavoraPositions', array(
                'removeConfirm' => esc_html__( 'Rimuovere questa posizione?', 'abcontact' ),
            ) );
        }
    }
    add_action( 'admin_enqueue_scripts', 'ab_lavora_admin_assets' );
}