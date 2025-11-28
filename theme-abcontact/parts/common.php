<?php
/**
 * Common includes and setup for all pages.
 *
 * This file can be included to ensure common theme elements are loaded.
 * It sets up common hooks and includes shared functionality.
 *
 * @package theme-abcontact
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get the appropriate hero template based on current page context.
 *
 * @return void
 */
function abcontact_get_hero() {
    if ( is_front_page() ) {
        get_template_part( 'parts/hero-home' );
    } else {
        get_template_part( 'parts/hero' );
    }
}

/**
 * Get the CTA section template.
 *
 * @return void
 */
function abcontact_get_cta() {
    get_template_part( 'parts/cta' );
}

/**
 * Get the footer template part.
 *
 * @return void
 */
function abcontact_get_footer_part() {
    get_template_part( 'parts/footer' );
}
