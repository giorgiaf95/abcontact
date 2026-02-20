<?php
/**
 * inc/chi-siamo-admin.php
 *
 * Metabox "Chi Siamo" (grouped) — register, render, save, admin enqueue for media JS.
 * Robust admin enqueue and registration limited to the Chi Siamo page/template.
 *
 * Drop this file in your theme's inc/ folder and make sure functions.php includes it in $inc_files.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* Helper: check if given $post is Chi Siamo page/template */
if ( ! function_exists( 'abcontact_is_chisiamo_post' ) ) {
    function abcontact_is_chisiamo_post( $post = null ) {
        if ( ! $post ) {
            // try global
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
        if ( $tpl === 'page-chi-siamo.php' ) {
            return true;
        }
        if ( isset( $post->post_name ) && $post->post_name === 'chi-siamo' ) {
            return true;
        }
        return false;
    }
}

/* ===================== Register grouped metabox (only when editing Chi Siamo) ===================== */
if ( ! function_exists( 'abcontact_register_chisiamo_grouped_metabox' ) ) {
    function abcontact_register_chisiamo_grouped_metabox() {
        // Determine current post early when possible
        $post = null;
        if ( isset( $_GET['post'] ) ) {
            $post = get_post( (int) $_GET['post'] );
        } elseif ( isset( $GLOBALS['post'] ) ) {
            $post = $GLOBALS['post'];
        }

        if ( ! abcontact_is_chisiamo_post( $post ) ) {
            // Do not register metabox for other pages
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

/* Render the grouped metabox */
if ( ! function_exists( 'abcontact_render_chisiamo_grouped_metabox' ) ) {
    function abcontact_render_chisiamo_grouped_metabox( $post ) {
        if ( ! abcontact_is_chisiamo_post( $post ) ) {
            echo '<p>' . esc_html__( 'Questi campi sono disponibili solo per la pagina "Chi Siamo".', 'abcontact' ) . '</p>';
            return;
        }

        wp_nonce_field( 'ab_chisiamo_save', 'ab_chisiamo_nonce' );

        $keys = array(
            'cs_section_a_title','cs_section_a_text','cs_section_a_image_id',
            'cs_values_title','cs_values_subtitle',
            'cs_section_c_title','cs_section_c_text','cs_section_c_image_id',
            'cs_stats_heading',
            'cs_stat_1_value','cs_stat_1_label',
            'cs_stat_2_value','cs_stat_2_label',
            'cs_stat_3_value','cs_stat_3_label',
            'cs_stat_4_value','cs_stat_4_label'
        );

        for ( $i = 1; $i <= 4; $i++ ) {
            $keys[] = "cs_value_{$i}_icon_id";
            $keys[] = "cs_value_{$i}_title";
            $keys[] = "cs_value_{$i}_text";
        }

        $cs = array();
        foreach ( $keys as $k ) {
            $cs[ $k ] = get_post_meta( $post->ID, $k, true );
        }

        ?>
        <div style="max-width:920px; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;">
          <h3 style="margin-top:0;"><?php esc_html_e( 'Gruppo A', 'abcontact' ); ?> — <?php esc_html_e( 'Immagine, Titolo, Testo', 'abcontact' ); ?></h3>

          <div style="display:flex;gap:18px;flex-wrap:wrap;">
            <div style="flex:1 1 320px;min-width:260px;">
              <p>
                <label style="font-weight:600;"><?php esc_html_e( 'Immagine (A)', 'abcontact' ); ?></label><br>
                <input type="hidden" id="cs_section_a_image_id" name="cs_section_a_image_id" value="<?php echo esc_attr( $cs['cs_section_a_image_id'] ); ?>">
                <button type="button" class="button" id="cs_select_a_image"><?php esc_html_e( 'Seleziona immagine', 'abcontact' ); ?></button>
                <button type="button" class="button" id="cs_remove_a_image"><?php esc_html_e( 'Rimuovi immagine', 'abcontact' ); ?></button>
              </p>
              <div id="cs_preview_a" style="margin-top:8px;">
                <?php if ( $cs['cs_section_a_image_id'] ) { echo wp_get_attachment_image( (int) $cs['cs_section_a_image_id'], 'chi_siamo_image' ); } ?>
              </div>
            </div>

            <div style="flex:2 1 420px;min-width:260px;">
              <p>
                <label for="cs_section_a_title"><?php esc_html_e( 'Titolo sezione (A)', 'abcontact' ); ?></label><br>
                <input type="text" id="cs_section_a_title" name="cs_section_a_title" value="<?php echo esc_attr( $cs['cs_section_a_title'] ); ?>" style="width:100%">
              </p>

              <p>
                <label for="cs_section_a_text"><?php esc_html_e( 'Testo sezione (A)', 'abcontact' ); ?></label><br>
                <textarea id="cs_section_a_text" name="cs_section_a_text" rows="6" style="width:100%"><?php echo esc_textarea( $cs['cs_section_a_text'] ); ?></textarea>
                <small class="description" style="display:block;margin-top:6px;color:#666;"><?php esc_html_e( 'Il testo sarà mostrato nella pagina; a desktop verrà disposto in due colonne se il template lo richiede.', 'abcontact' ); ?></small>
              </p>
            </div>
          </div>

          <hr style="margin:18px 0;border:none;border-top:1px solid #eee;">

          <h3 style="margin:0 0 8px;"><?php esc_html_e( 'Gruppo B (Sezione C)', 'abcontact' ); ?> — <?php esc_html_e( 'Immagine, Titolo, Testo', 'abcontact' ); ?></h3>

          <div style="display:flex;gap:18px;flex-wrap:wrap;">
            <div style="flex:1 1 320px;min-width:260px;">
              <p>
                <label style="font-weight:600;"><?php esc_html_e( 'Immagine (C)', 'abcontact' ); ?></label><br>
                <input type="hidden" id="cs_section_c_image_id" name="cs_section_c_image_id" value="<?php echo esc_attr( $cs['cs_section_c_image_id'] ); ?>">
                <button type="button" class="button" id="cs_select_c_image"><?php esc_html_e( 'Seleziona immagine', 'abcontact' ); ?></button>
                <button type="button" class="button" id="cs_remove_c_image"><?php esc_html_e( 'Rimuovi immagine', 'abcontact' ); ?></button>
              </p>
              <div id="cs_preview_c" style="margin-top:8px;">
                <?php if ( $cs['cs_section_c_image_id'] ) { echo wp_get_attachment_image( (int) $cs['cs_section_c_image_id'], 'chi_siamo_image' ); } ?>
              </div>
            </div>

            <div style="flex:2 1 420px;min-width:260px;">
              <p>
                <label for="cs_section_c_title"><?php esc_html_e( 'Titolo sezione (C)', 'abcontact' ); ?></label><br>
                <input type="text" id="cs_section_c_title" name="cs_section_c_title" value="<?php echo esc_attr( $cs['cs_section_c_title'] ); ?>" style="width:100%">
              </p>

              <p>
                <label for="cs_section_c_text"><?php esc_html_e( 'Testo sezione (C)', 'abcontact' ); ?></label><br>
                <textarea id="cs_section_c_text" name="cs_section_c_text" rows="6" style="width:100%"><?php echo esc_textarea( $cs['cs_section_c_text'] ); ?></textarea>
              </p>
            </div>
          </div>

          <hr style="margin:18px 0;border:none;border-top:1px solid #eee;">

          <h3 style="margin:0 0 8px;"><?php esc_html_e( 'I nostri valori (4 elementi)', 'abcontact' ); ?></h3>

          <p>
            <label for="cs_values_title"><?php esc_html_e( 'Titolo sezione valori', 'abcontact' ); ?></label><br>
            <input type="text" id="cs_values_title" name="cs_values_title" value="<?php echo esc_attr( $cs['cs_values_title'] ); ?>" style="width:100%">
          </p>

          <p>
            <label for="cs_values_subtitle"><?php esc_html_e( 'Sottotitolo sezione valori', 'abcontact' ); ?></label><br>
            <textarea id="cs_values_subtitle" name="cs_values_subtitle" rows="3" style="width:100%"><?php echo esc_textarea( $cs['cs_values_subtitle'] ); ?></textarea>
          </p>

          <?php for ( $i = 1; $i <= 4; $i++ ) :
              $icon_id = isset( $cs["cs_value_{$i}_icon_id"] ) ? $cs["cs_value_{$i}_icon_id"] : '';
          ?>
            <fieldset style="margin:10px 0;padding:10px;border:1px solid #eee;border-radius:6px">
              <legend><?php echo sprintf( esc_html__( 'Valore %d', 'abcontact' ), $i ); ?></legend>

              <p>
                <label><?php esc_html_e( 'Icona (Media Library)', 'abcontact' ); ?></label><br>
                <input type="hidden" id="cs_value_<?php echo $i; ?>_icon_id" name="cs_value_<?php echo $i; ?>_icon_id" value="<?php echo esc_attr( $icon_id ); ?>">
                <button type="button" class="button cs-select-icon" data-target="<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Seleziona icona', 'abcontact' ); ?></button>
                <button type="button" class="button cs-remove-icon" data-target="<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Rimuovi icona', 'abcontact' ); ?></button>
                <div id="cs_preview_icon_<?php echo $i; ?>" style="margin-top:8px;">
                  <?php if ( $icon_id ) { echo wp_get_attachment_image( (int) $icon_id, 'chi_siamo_icon' ); } ?>
                </div>
              </p>

              <p>
                <label for="cs_value_<?php echo $i; ?>_title"><?php esc_html_e( 'Titolo valore', 'abcontact' ); ?></label><br>
                <input type="text" id="cs_value_<?php echo $i; ?>_title" name="cs_value_<?php echo $i; ?>_title" value="<?php echo esc_attr( $cs["cs_value_{$i}_title"] ); ?>" style="width:100%">
              </p>

              <p>
                <label for="cs_value_<?php echo $i; ?>_text"><?php esc_html_e( 'Testo valore', 'abcontact' ); ?></label><br>
                <textarea id="cs_value_<?php echo $i; ?>_text" name="cs_value_<?php echo $i; ?>_text" rows="3" style="width:100%"><?php echo esc_textarea( $cs["cs_value_{$i}_text"] ); ?></textarea>
              </p>
            </fieldset>
          <?php endfor; ?>

          <hr style="margin:18px 0;border:none;border-top:1px solid #eee;">

          <h3 style="margin:0 0 8px;"><?php esc_html_e( 'Statistiche e CTA finale', 'abcontact' ); ?></h3>

          <p>
            <label for="cs_stats_heading"><?php esc_html_e( 'Titolo statistica (opzionale)', 'abcontact' ); ?></label><br>
            <input type="text" id="cs_stats_heading" name="cs_stats_heading" value="<?php echo esc_attr( $cs['cs_stats_heading'] ); ?>" style="width:100%">
          </p>

          <?php for ( $i = 1; $i <= 4; $i++ ) : ?>
            <p style="display:flex;gap:8px;margin-bottom:8px;">
              <input type="text" name="cs_stat_<?php echo $i; ?>_value" value="<?php echo esc_attr( $cs["cs_stat_{$i}_value"] ); ?>" placeholder="<?php echo esc_attr( '10+', 'abcontact' ); ?>" style="width:30%">
              <input type="text" name="cs_stat_<?php echo $i; ?>_label" value="<?php echo esc_attr( $cs["cs_stat_{$i}_label"] ); ?>" placeholder="<?php echo esc_attr( 'Etichetta', 'abcontact' ); ?>" style="width:70%">
            </p>
          <?php endfor; ?>

          <hr style="margin:18px 0;border:none;border-top:1px solid #eee;">

          <!-- CTA preview (kept as before) -->
          <div class="cs-cta-preview" style="margin-top:12px;padding:12px;border:1px solid #eee;border-radius:8px;background:#fafafa;">
            <h4><?php esc_html_e( 'Anteprima CTA principale', 'abcontact' ); ?></h4>
            <p class="description">
              <?php esc_html_e( 'La CTA è gestita centralmente. Modifica la CTA tramite il metabox "Front CTA" o i metadati cta_* della pagina. Qui sotto vedi una preview (se impostata).', 'abcontact' ); ?>
            </p>

            <?php
            $post_id_for_cta = isset( $post->ID ) ? (int) $post->ID : get_the_ID();

            $cta_title        = get_post_meta( $post_id_for_cta, 'cta_title', true );
            $cta_subtitle     = get_post_meta( $post_id_for_cta, 'cta_subtitle', true );
            $cta_button_label = get_post_meta( $post_id_for_cta, 'cta_button_label', true );
            $cta_button_link  = get_post_meta( $post_id_for_cta, 'cta_button_link', true );
            $cta_button_color = get_post_meta( $post_id_for_cta, 'cta_button_color', true );
            $cta_modal_raw    = get_post_meta( $post_id_for_cta, 'cta_modal', true );
            $cta_modal        = $cta_modal_raw ? true : false;

            if ( empty( $cta_title ) ) {
                $cta_title = get_post_meta( $post_id_for_cta, 'cs_cta_title', true );
            }
            if ( empty( $cta_subtitle ) ) {
                $cta_subtitle = get_post_meta( $post_id_for_cta, 'cs_cta_text', true );
            }
            if ( empty( $cta_button_label ) ) {
                $cta_button_label = get_post_meta( $post_id_for_cta, 'cs_cta_button_label', true );
            }
            if ( empty( $cta_button_link ) ) {
                $cta_button_link = get_post_meta( $post_id_for_cta, 'cs_cta_button_link', true );
            }
            if ( empty( $cta_button_color ) ) {
                $cta_button_color = get_post_meta( $post_id_for_cta, 'cs_cta_button_color', true );
            }

            if ( ! empty( $cta_button_link ) ) {
                $raw_link = trim( $cta_button_link );
                if ( ! preg_match( '#^https?://#i', $raw_link ) ) {
                    $cta_button_link = home_url( '/' . ltrim( $raw_link, '/' ) );
                } else {
                    $cta_button_link = $raw_link;
                }
            }

            $preview_args = array(
                'title'        => $cta_title,
                'subtitle'     => $cta_subtitle,
                'button_label' => $cta_button_label,
                'button_link'  => $cta_button_link,
                'button_color' => $cta_button_color,
                'modal'        => $cta_modal,
            );

            set_query_var( 'args', $preview_args );
            get_template_part( 'template-parts/cta', null, $preview_args );
            set_query_var( 'args', null );
            ?>

          </div>

        </div>
        <?php
    }
}

/* Save handler for Chi Siamo grouped metabox */
if ( ! function_exists( 'ab_chisiamo_save_fields' ) ) {
    function ab_chisiamo_save_fields( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! isset( $_POST['ab_chisiamo_nonce'] ) || ! wp_verify_nonce( $_POST['ab_chisiamo_nonce'], 'ab_chisiamo_save' ) ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $fields = array(
            'cs_section_a_title','cs_section_a_text','cs_section_a_image_id',
            'cs_values_title','cs_values_subtitle',
            'cs_section_c_title','cs_section_c_text','cs_section_c_image_id',
            'cs_stats_heading',
            'cs_stat_1_value','cs_stat_1_label',
            'cs_stat_2_value','cs_stat_2_label',
            'cs_stat_3_value','cs_stat_3_label',
            'cs_stat_4_value','cs_stat_4_label'
        );

        for ( $i = 1; $i <= 4; $i++ ) {
            $fields[] = "cs_value_{$i}_icon_id";
            $fields[] = "cs_value_{$i}_title";
            $fields[] = "cs_value_{$i}_text";
        }

        foreach ( $fields as $f ) {
            if ( isset( $_POST[ $f ] ) ) {
                $val = $_POST[ $f ];
                if ( strpos( $f, '_image_id' ) !== false || strpos( $f, '_icon_id' ) !== false ) {
                    update_post_meta( $post_id, $f, absint( $val ) );
                } elseif ( strpos( $f, '_text' ) !== false || stripos( $f, 'subtitle' ) !== false ) {
                    update_post_meta( $post_id, $f, wp_kses_post( $val ) );
                } elseif ( stripos( $f, 'link' ) !== false ) {
                    update_post_meta( $post_id, $f, esc_url_raw( $val ) );
                } else {
                    update_post_meta( $post_id, $f, sanitize_text_field( $val ) );
                }
            } else {
                // keep existing if omitted
            }
        }
    }
    add_action( 'save_post', 'ab_chisiamo_save_fields', 20 );
}

/* ================= Admin enqueue for Chi Siamo metabox JS (robust) ================= */
if ( ! function_exists( 'ab_chisiamo_admin_enqueue' ) ) {
    function ab_chisiamo_admin_enqueue( $hook ) {
        // Only run on post edit/new screens
        if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
            return;
        }

        // Resolve post id robustly
        $post_id = 0;
        if ( isset( $_GET['post'] ) ) {
            $post_id = (int) $_GET['post'];
        } elseif ( isset( $_POST['post_ID'] ) ) {
            $post_id = (int) $_POST['post_ID'];
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

        if ( ! abcontact_is_chisiamo_post( $post ) ) {
            return;
        }

        // Ensure core libs are registered/enqueued
        if ( ! wp_script_is( 'underscore', 'registered' ) ) {
            wp_register_script( 'underscore', includes_url( 'js/underscore.min.js' ), array(), null );
        }
        if ( ! wp_script_is( 'backbone', 'registered' ) ) {
            wp_register_script( 'backbone', includes_url( 'js/backbone.min.js' ), array( 'underscore', 'jquery' ), null );
        }
        if ( ! wp_script_is( 'wp-util', 'registered' ) ) {
            wp_register_script( 'wp-util', includes_url( 'js/wp-util.min.js' ), array( 'jquery' ), null );
        }
        if ( ! wp_script_is( 'underscore', 'enqueued' ) ) wp_enqueue_script( 'underscore' );
        if ( ! wp_script_is( 'backbone', 'enqueued' ) ) wp_enqueue_script( 'backbone' );
        if ( ! wp_script_is( 'wp-util', 'enqueued' ) ) wp_enqueue_script( 'wp-util' );

        // wp.media + media modules
        wp_enqueue_media();
        if ( ! wp_script_is( 'media-views', 'enqueued' ) ) wp_enqueue_script( 'media-views' );
        if ( ! wp_script_is( 'media-editor', 'enqueued' ) ) wp_enqueue_script( 'media-editor' );

        // find and enqueue chi-siamo-admin.js (child then parent)
        $candidates = array(
            '/assets/js/chi-siamo-admin.js',
            '/assets/js/admin/chi-siamo-admin.js',
            '/assets/js/chi-siamo-admin.min.js',
            '/assets/js/admin/chi-siamo-admin.min.js',
        );

        $found = false;
        foreach ( $candidates as $rel ) {
            $child_path  = get_stylesheet_directory() . $rel;
            $parent_path = get_template_directory() . $rel;
            if ( file_exists( $child_path ) ) {
                $src = get_stylesheet_directory_uri() . $rel;
                $ver = filemtime( $child_path );
                $found = true;
                break;
            }
            if ( file_exists( $parent_path ) ) {
                $src = get_template_directory_uri() . $rel;
                $ver = filemtime( $parent_path );
                $found = true;
                break;
            }
        }

        if ( ! $found ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'abcontact: chi-siamo admin script not found. Checked: ' . implode( ', ', $candidates ) );
            }
            return;
        }

        wp_register_script( 'ab-chisiamo-admin', $src, array( 'jquery', 'media-editor', 'media-views', 'wp-util' ), $ver, true );
        wp_enqueue_script( 'ab-chisiamo-admin' );

        wp_localize_script( 'ab-chisiamo-admin', 'abcontactChiSiamo', array(
            'l10n' => array(
                'title'  => esc_html__( 'Seleziona immagine', 'abcontact' ),
                'button' => esc_html__( 'Usa immagine', 'abcontact' ),
            ),
        ) );

        $admin_css = get_stylesheet_directory() . '/assets/css/admin-metaboxes.css';
        if ( file_exists( $admin_css ) ) {
            wp_enqueue_style( 'ab-admin-metabox', get_stylesheet_directory_uri() . '/assets/css/admin-metaboxes.css', array(), filemtime( $admin_css ) );
        }

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'abcontact: enqueued ab-chisiamo-admin from ' . $src . ' for post ' . $post_id );
        }
    }
    add_action( 'admin_enqueue_scripts', 'ab_chisiamo_admin_enqueue', 10 );
}