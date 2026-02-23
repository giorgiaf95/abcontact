<?php
if ( ! defined( 'ABSPATH' ) ) exit;

const ABCONTACT_META_SHOW_CTA = '_abcontact_show_cta_prototype';

function abcontact_should_render_cta_prototype() {
    if ( is_admin() ) return false;
    if ( ! is_singular( 'page' ) ) return false;

    $post_id = get_queried_object_id();
    if ( ! $post_id ) return false;

    $show = get_post_meta( $post_id, ABCONTACT_META_SHOW_CTA, true );
    return (string) $show === '1';
}

add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'ab_cta_toggle_metabox',
        __( 'CTA (Prototipo)', 'theme-abcontact' ),
        function( $post ) {
            wp_nonce_field( 'abcontact_cta_toggle_save', 'abcontact_cta_toggle_nonce' );
            $val = get_post_meta( $post->ID, ABCONTACT_META_SHOW_CTA, true );
            ?>
            <label style="display:flex;gap:10px;align-items:center;">
              <input type="checkbox" name="abcontact_show_cta_prototype" value="1" <?php checked( (string)$val === '1' ); ?>>
              <strong><?php esc_html_e( 'Mostra CTA sopra il footer in questa pagina', 'theme-abcontact' ); ?></strong>
            </label>
            <p class="description" style="margin-top:8px;">
              <?php esc_html_e( 'Di default è disattivata. Attivala solo nelle pagine dove vuoi mostrare la CTA del prototipo.', 'theme-abcontact' ); ?>
            </p>
            <?php
        },
        'page',
        'side',
        'high'
    );
} );

add_action( 'save_post_page', function( $post_id ) {
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['abcontact_cta_toggle_nonce'] ) || ! wp_verify_nonce( $_POST['abcontact_cta_toggle_nonce'], 'abcontact_cta_toggle_save' ) ) return;
    if ( ! current_user_can( 'edit_page', $post_id ) ) return;

    update_post_meta( $post_id, ABCONTACT_META_SHOW_CTA, isset( $_POST['abcontact_show_cta_prototype'] ) ? '1' : '0' );
} );