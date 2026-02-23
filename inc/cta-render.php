<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function abcontact_should_render_cta_prototype() {
    if ( is_admin() ) return false;
    if ( ! is_singular( 'page' ) ) return false;

    $post_id = get_queried_object_id();
    if ( ! $post_id ) return false;

    $show = get_post_meta( $post_id, ABCONTACT_META_SHOW_CTA, true );
    return (string)$show === '1';
}

/**
 * Hook chosen: wp_footer (late), so it appears right before </body>.
 * In most themes this is after footer markup; you asked "above footer".
 * The most correct place is in footer.php before get_footer closes.
 *
 * Since we can't guarantee footer.php structure here, we render on wp_footer
 * but style it to sit visually before footer if footer is last block.
 *
 * If you prefer exact placement, we can instead add a call inside footer.php.
 */
add_action( 'wp_footer', function() {
    if ( ! abcontact_should_render_cta_prototype() ) return;

    if ( locate_template( 'template-parts/cta.php' ) ) {
        get_template_part( 'template-parts/cta' ); // we will replace cta.php with prototype CTA
    }
}, 5 );