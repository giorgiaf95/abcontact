<?php
if ( ! defined( 'ABSPATH' ) ) exit;

const ABCONTACT_META_SHOW_CTA = '_abcontact_show_cta_prototype';

function abcontact_get_cta_target_page_id() {
    // Home page (static front page)
    if ( is_front_page() ) {
        return (int) get_option( 'page_on_front' );
    }

    // Normal pages
    if ( is_page() ) {
        return (int) get_queried_object_id();
    }

    return 0;
}

function abcontact_should_render_cta_prototype() {
    if ( is_admin() ) return false;

    // ✅ HOME: always on
    if ( is_front_page() ) {
        return true;
    }

    // ✅ Rest of site: only pages, controlled by metabox toggle
    if ( ! is_page() ) {
        return false;
    }

    $post_id = abcontact_get_cta_target_page_id();
    if ( ! $post_id ) return false;

    return (string) get_post_meta( $post_id, ABCONTACT_META_SHOW_CTA, true ) === '1';
}

add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'ab_cta_toggle_metabox',
        __( 'CTA (Prototipo)', 'theme-abcontact' ),
        'abcontact_render_cta_toggle_metabox',
        'page',
        'side',
        'high',
        array(
            '__back_compat_meta_box' => true,
        )
    );
} );

function abcontact_render_cta_toggle_metabox( $post ) {
    wp_nonce_field( 'abcontact_cta_toggle_save', 'abcontact_cta_toggle_nonce' );
    $val = get_post_meta( $post->ID, ABCONTACT_META_SHOW_CTA, true );

    $hint = '';
    if ( (int) get_option('page_on_front') === (int) $post->ID ) {
        $hint = __( 'Nota: questa è la Home e la CTA è sempre attiva (il toggle non ha effetto).', 'theme-abcontact' );
    }
    ?>
    <p style="margin:0 0 10px;">
      <label style="display:flex;gap:10px;align-items:flex-start;">
        <input type="checkbox" name="abcontact_show_cta_prototype" value="1" <?php checked( (string)$val === '1' ); ?> style="margin-top:2px;">
        <span>
          <strong><?php esc_html_e( 'Mostra CTA sopra il footer', 'theme-abcontact' ); ?></strong><br>
          <span class="description"><?php esc_html_e( 'Default OFF. Attivala solo in questa pagina.', 'theme-abcontact' ); ?></span>
          <?php if ( $hint ) : ?>
            <br><span class="description" style="color:#1d2327;"><?php echo esc_html( $hint ); ?></span>
          <?php endif; ?>
        </span>
      </label>
    </p>
    <?php
}

add_action( 'save_post_page', function( $post_id ) {
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision( $post_id ) ) return;

    if ( ! isset( $_POST['abcontact_cta_toggle_nonce'] ) || ! wp_verify_nonce( $_POST['abcontact_cta_toggle_nonce'], 'abcontact_cta_toggle_save' ) ) return;
    if ( ! current_user_can( 'edit_page', $post_id ) ) return;

    update_post_meta(
        $post_id,
        ABCONTACT_META_SHOW_CTA,
        isset( $_POST['abcontact_show_cta_prototype'] ) ? '1' : '0'
    );
} );