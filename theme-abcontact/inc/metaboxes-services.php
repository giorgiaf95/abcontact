<?php
// inc/metaboxes-services.php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Metabox "Contenuti Servizio" (per page template = page-service-template.php e page-chi-siamo.php)
 * - service_intro_title, service_intro_body
 * - service_full_image_id
 * - service_phase_{1..4}_title / _text / _icon_id
 * - service_reviews_title / _sub / _shortcode
 * - service_final_cta_text / _link
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
        'service_intro_title','service_intro_body','service_full_image_id',
        'service_reviews_title','service_reviews_sub','service_reviews_shortcode',
        'service_final_cta_text','service_final_cta_link'
    );
    $meta = array();
    foreach ( $meta_keys as $k ) {
        $meta[ $k ] = get_post_meta( $post->ID, $k, true );
    }

    // phases
    $phases = array();
    for ( $i = 1; $i <= 4; $i++ ) {
        $phases[ $i ] = array(
            'title'   => get_post_meta( $post->ID, "service_phase_{$i}_title", true ),
            'text'    => get_post_meta( $post->ID, "service_phase_{$i}_text", true ),
            'icon_id' => intval( get_post_meta( $post->ID, "service_phase_{$i}_icon_id", true ) ),
        );
    }
    ?>
    <div style="max-width:920px;">
      <h4><?php esc_html_e( 'Intro (titolo e testo)', 'abcontact' ); ?></h4>
      <p>
        <label for="service_intro_title"><?php esc_html_e( 'Titolo intro', 'abcontact' ); ?></label><br>
        <input type="text" id="service_intro_title" name="service_intro_title" value="<?php echo esc_attr( $meta['service_intro_title'] ); ?>" style="width:100%;">
      </p>
      <p>
        <label for="service_intro_body"><?php esc_html_e( 'Testo intro', 'abcontact' ); ?></label><br>
        <textarea id="service_intro_body" name="service_intro_body" rows="4" style="width:100%;"><?php echo esc_textarea( $meta['service_intro_body'] ); ?></textarea>
      </p>

      <hr>

      <h4><?php esc_html_e( 'Immagine full (main image)', 'abcontact' ); ?></h4>
      <p>
        <input type="hidden" id="service_full_image_id" name="service_full_image_id" value="<?php echo esc_attr( $meta['service_full_image_id'] ); ?>">
        <button type="button" class="button" id="ab-select-full-image"><?php esc_html_e( 'Seleziona immagine', 'abcontact' ); ?></button>
        <button type="button" class="button" id="ab-remove-full-image"><?php esc_html_e( 'Rimuovi immagine', 'abcontact' ); ?></button>
      </p>
      <div id="service_full_image_preview" style="margin-top:8px;">
        <?php if ( $meta['service_full_image_id'] ) { echo wp_get_attachment_image( (int) $meta['service_full_image_id'], 'medium' ); } ?>
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

      <h4><?php esc_html_e( 'CTA finale', 'abcontact' ); ?></h4>
      <p>
        <label for="service_final_cta_text"><?php esc_html_e( 'Testo CTA finale', 'abcontact' ); ?></label><br>
        <input type="text" id="service_final_cta_text" name="service_final_cta_text" value="<?php echo esc_attr( $meta['service_final_cta_text'] ); ?>" style="width:100%">
      </p>
      <p>
        <label for="service_final_cta_link"><?php esc_html_e( 'Link CTA finale', 'abcontact' ); ?></label><br>
        <input type="text" id="service_final_cta_link" name="service_final_cta_link" value="<?php echo esc_attr( $meta['service_final_cta_link'] ); ?>" style="width:100%">
      </p>
    </div>
    <?php
}

/* Save metabox */
function abcontact_save_service_metabox( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['ab_service_nonce'] ) || ! wp_verify_nonce( $_POST['ab_service_nonce'], 'ab_service_save' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // simple helper
    $maybe_save = function( $key ) use ( $post_id ) {
        if ( isset( $_POST[ $key ] ) ) {
            $val = $_POST[ $key ];
            if ( is_string( $val ) ) {
                update_post_meta( $post_id, $key, sanitize_text_field( trim( $val ) ) );
            } else {
                update_post_meta( $post_id, $key, $val );
            }
        } else {
            delete_post_meta( $post_id, $key );
        }
    };

    // basic keys
    $keys = array(
        'service_intro_title','service_intro_body','service_full_image_id',
        'service_reviews_title','service_reviews_sub','service_reviews_shortcode',
        'service_final_cta_text','service_final_cta_link'
    );
    foreach ( $keys as $k ) { $maybe_save( $k ); }

    // phases
    for ( $i = 1; $i <= 4; $i++ ) {
        $maybe_save( "service_phase_{$i}_title" );
        $maybe_save( "service_phase_{$i}_text" );
        if ( isset( $_POST[ "service_phase_{$i}_icon_id" ] ) ) {
            update_post_meta( $post_id, "service_phase_{$i}_icon_id", absint( $_POST[ "service_phase_{$i}_icon_id" ] ) );
        } else {
            delete_post_meta( $post_id, "service_phase_{$i}_icon_id" );
        }
    }
}
add_action( 'save_post', 'abcontact_save_service_metabox', 20 );

/* Enqueue admin JS for media frames (select icons / full image) */
function abcontact_enqueue_service_admin_assets( $hook ) {
    global $post;
    if ( ( $hook !== 'post.php' && $hook !== 'post-new.php' ) || ! $post || $post->post_type !== 'page' ) return;
    $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
    $tpl = $tpl ? basename( $tpl ) : '';
    if ( ! in_array( $tpl, array( 'page-service-template.php', 'page-chi-siamo.php' ), true ) && $post->post_name !== 'chi-siamo' ) return;

    // wp media
    wp_enqueue_media();
    $rel = '/assets/js/service-metabox.js';
    $full = get_stylesheet_directory() . $rel;
    if ( file_exists( $full ) ) {
        wp_enqueue_script( 'ab-service-metabox', get_stylesheet_directory_uri() . $rel, array( 'jquery' ), filemtime( $full ), true );
    }
}
add_action( 'admin_enqueue_scripts', 'abcontact_enqueue_service_admin_assets' );
