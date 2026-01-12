<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Metaboxes for "Lavora con noi" page
 * repeater for "Posizioni Aperte" (title, excerpt, image_id, requirements, offer, apply_url)
 */

/* Register metabox */
add_action( 'add_meta_boxes', 'ab_lavora_register_metaboxes' );
function ab_lavora_register_metaboxes() {
    add_meta_box(
        'ab_lavora_sections',
        __( 'Lavora con noi — Posizioni Aperte', 'abcontact' ),
        'ab_lavora_metabox_render',
        'page',
        'normal',
        'high'
    );
}

/* Render metabox */
function ab_lavora_metabox_render( $post ) {
    // nonce for positions repeater
    wp_nonce_field( 'ab_lavora_positions_save', 'ab_lavora_positions_nonce' );

    // load existing positions meta
    $positions_raw = get_post_meta( $post->ID, '_lc_positions', true );
    $positions = array();
    if ( $positions_raw ) {
        $decoded = json_decode( $positions_raw, true );
        if ( is_array( $decoded ) ) {
            $positions = $decoded;
        }
    }
    ?>
    <style>
      .ab-lc-row{ margin-bottom:18px; }
      .ab-lc-img-preview{ display:block; max-width:220px; height:auto; margin-top:8px; border-radius:8px; box-shadow:0 6px 20px rgba(0,0,0,0.06);}
      .ab-lc-field{ width:100%; box-sizing:border-box; padding:6px 8px; }
      /* positions repeater styling inside metabox */
      .lc-positions-repeater { margin-top: 12px; }
      .lc-position-item { border:1px solid #e6e9ee; padding:10px; margin-bottom:10px; border-radius:6px; background:#fff; }
      .lc-position-item .handle { cursor:move; font-weight:600; color:#0b5fff; margin-bottom:6px; display:flex; justify-content:space-between; align-items:center;}
      .lc-position-row { display:flex; gap:8px; align-items:flex-start; margin-bottom:8px; }
      .lc-position-row .col { flex:1; }
      .lc-position-actions { text-align:right; }
    </style>

    <h4><?php esc_html_e( 'Posizioni Aperte (gestione)', 'abcontact' ); ?></h4>
    <p class="description"><?php esc_html_e( 'Aggiungi qui le posizioni aperte che vuoi mostrare nella sezione. Puoi riordinare, modificare o rimuovere.', 'abcontact' ); ?></p>

    <div class="lc-positions-repeater" data-repeater>
      <?php
      if ( ! empty( $positions ) ) :
        foreach ( $positions as $index => $pos ) :
          $title = isset( $pos['title'] ) ? $pos['title'] : '';
          $excerpt = isset( $pos['excerpt'] ) ? $pos['excerpt'] : '';
          $image_id = isset( $pos['image_id'] ) ? intval( $pos['image_id'] ) : 0;
          $requirements = isset( $pos['requirements'] ) ? $pos['requirements'] : '';
          $offer = isset( $pos['offer'] ) ? $pos['offer'] : '';
          $apply = isset( $pos['apply_url'] ) ? $pos['apply_url'] : '';
      ?>
        <div class="lc-position-item" data-index="<?php echo esc_attr( $index ); ?>">
          <div class="handle">
            <span class="label"><?php echo esc_html( $title ? $title : sprintf( __( 'Posizione %d', 'abcontact' ), $index + 1 ) ); ?></span>
            <div class="lc-position-actions">
              <button type="button" class="button lc-position-remove"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
            </div>
          </div>

          <div class="lc-position-row">
            <div class="col">
              <label><?php esc_html_e( 'Titolo', 'abcontact' ); ?></label>
              <input type="text" name="lc_positions[<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" class="ab-lc-field">
            </div>
            <div class="col">
              <label><?php esc_html_e( 'Candidatura (URL)', 'abcontact' ); ?></label>
              <input type="text" name="lc_positions[<?php echo esc_attr( $index ); ?>][apply_url]" value="<?php echo esc_attr( $apply ); ?>" class="ab-lc-field">
            </div>
          </div>

          <div class="lc-position-row">
            <div class="col">
              <label><?php esc_html_e( 'Immagine (anteprima)', 'abcontact' ); ?></label><br>
              <input type="hidden" name="lc_positions[<?php echo esc_attr( $index ); ?>][image_id]" class="lc-pos-image-id" value="<?php echo esc_attr( $image_id ); ?>">
              <button type="button" class="button lc-pos-image-button"><?php esc_html_e( 'Seleziona immagine', 'abcontact' ); ?></button>
              <button type="button" class="button lc-pos-image-remove" style="margin-left:8px;"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
              <div class="lc-pos-image-preview">
                <?php if ( $image_id ) echo wp_get_attachment_image( $image_id, 'medium', false, array( 'class' => 'ab-lc-img-preview' ) ); ?>
              </div>
            </div>
          </div>

          <div>
            <label><?php esc_html_e( 'Descrizione / Intro', 'abcontact' ); ?></label>
            <textarea name="lc_positions[<?php echo esc_attr( $index ); ?>][excerpt]" rows="3" class="ab-lc-field"><?php echo esc_textarea( $excerpt ); ?></textarea>
          </div>

          <div class="lc-position-row">
            <div class="col">
              <label><?php esc_html_e( 'Requisiti (una voce per riga)', 'abcontact' ); ?></label>
              <textarea name="lc_positions[<?php echo esc_attr( $index ); ?>][requirements]" rows="3" class="ab-lc-field"><?php echo esc_textarea( $requirements ); ?></textarea>
            </div>
            <div class="col">
              <label><?php esc_html_e( 'Cosa Offriamo (una voce per riga)', 'abcontact' ); ?></label>
              <textarea name="lc_positions[<?php echo esc_attr( $index ); ?>][offer]" rows="3" class="ab-lc-field"><?php echo esc_textarea( $offer ); ?></textarea>
            </div>
          </div>
        </div>
      <?php
        endforeach;
      endif;
      ?>

      <!-- template for new item (cloned by JS) -->
      <template id="lc-position-template">
        <div class="lc-position-item" data-index="__index__">
          <div class="handle">
            <span class="label"><?php esc_html_e( 'Nuova posizione', 'abcontact' ); ?></span>
            <div class="lc-position-actions">
              <button type="button" class="button lc-position-remove"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
            </div>
          </div>

          <div class="lc-position-row">
            <div class="col">
              <label><?php esc_html_e( 'Titolo', 'abcontact' ); ?></label>
              <input type="text" name="lc_positions[__index__][title]" value="" class="ab-lc-field">
            </div>
            <div class="col">
              <label><?php esc_html_e( 'Candidatura (URL)', 'abcontact' ); ?></label>
              <input type="text" name="lc_positions[__index__][apply_url]" value="" class="ab-lc-field">
            </div>
          </div>

          <div class="lc-position-row">
            <div class="col">
              <label><?php esc_html_e( 'Immagine (anteprima)', 'abcontact' ); ?></label><br>
              <input type="hidden" name="lc_positions[__index__][image_id]" class="lc-pos-image-id" value="">
              <button type="button" class="button lc-pos-image-button"><?php esc_html_e( 'Seleziona immagine', 'abcontact' ); ?></button>
              <button type="button" class="button lc-pos-image-remove" style="margin-left:8px;"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
              <div class="lc-pos-image-preview"></div>
            </div>
          </div>

          <div>
            <label><?php esc_html_e( 'Descrizione / Intro', 'abcontact' ); ?></label>
            <textarea name="lc_positions[__index__][excerpt]" rows="3" class="ab-lc-field"></textarea>
          </div>

          <div class="lc-position-row">
            <div class="col">
              <label><?php esc_html_e( 'Requisiti (una voce per riga)', 'abcontact' ); ?></label>
              <textarea name="lc_positions[__index__][requirements]" rows="3" class="ab-lc-field"></textarea>
            </div>
            <div class="col">
              <label><?php esc_html_e( 'Cosa Offriamo (una voce per riga)', 'abcontact' ); ?></label>
              <textarea name="lc_positions[__index__][offer]" rows="3" class="ab-lc-field"></textarea>
            </div>
          </div>
        </div>
      </template>

      <p><button type="button" class="button button-primary" id="lc-add-position"><?php esc_html_e( 'Aggiungi Posizione', 'abcontact' ); ?></button></p>

    </div>

    <?php
}

/* Save meta: only save the positions repeater (legacy front/agent metas removed) */
add_action( 'save_post', 'ab_lavora_metabox_save' );
function ab_lavora_metabox_save( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    /* Save positions repeater */
    if ( isset( $_POST['lc_positions'] ) && isset( $_POST['ab_lavora_positions_nonce'] ) && wp_verify_nonce( $_POST['ab_lavora_positions_nonce'], 'ab_lavora_positions_save' ) ) {
        $raw = $_POST['lc_positions'];
        $clean = array();

        if ( is_array( $raw ) ) {
            foreach ( $raw as $item ) {
                // sanitize each expected field
                $title = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';
                $excerpt = isset( $item['excerpt'] ) ? wp_kses_post( $item['excerpt'] ) : '';
                $image_id = isset( $item['image_id'] ) ? intval( $item['image_id'] ) : 0;
                $requirements = isset( $item['requirements'] ) ? sanitize_textarea_field( $item['requirements'] ) : '';
                $offer = isset( $item['offer'] ) ? sanitize_textarea_field( $item['offer'] ) : '';
                $apply_url = isset( $item['apply_url'] ) ? esc_url_raw( $item['apply_url'] ) : '';

                // skip empty entries (optional)
                if ( $title === '' && $excerpt === '' && $image_id === 0 && $requirements === '' && $offer === '' && $apply_url === '' ) {
                    continue;
                }

                $clean[] = array(
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'image_id' => $image_id,
                    'requirements' => $requirements,
                    'offer' => $offer,
                    'apply_url' => $apply_url,
                );
            }
        }

        update_post_meta( $post_id, '_lc_positions', wp_json_encode( $clean ) );
    } else {
        // do not delete existing positions if none sent
    }
}

/* Admin assets: media uploader JS + positions repeater script */
add_action( 'admin_enqueue_scripts', 'ab_lavora_admin_assets' );
function ab_lavora_admin_assets( $hook ) {
    global $post;
    if ( ( $hook !== 'post.php' && $hook !== 'post-new.php' ) || ! $post || $post->post_type !== 'page' ) {
        return;
    }
    wp_enqueue_media();

    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    $js1 = $theme_dir . '/assets/js/admin-lavora-metabox.js';
    if ( file_exists( $js1 ) ) {
        wp_enqueue_script( 'ab-lavora-admin', $theme_uri . '/assets/js/admin-lavora-metabox.js', array( 'jquery' ), filemtime( $js1 ), true );
    }

    // localize (small strings)
    wp_localize_script( 'ab-lavora-admin', 'abLavoraPositions', array(
      'removeConfirm' => esc_html__( 'Rimuovere questa posizione?', 'abcontact' ),
    ) );
}