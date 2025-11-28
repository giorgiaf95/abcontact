<?php
/**
 * Common Template Part
 *
 * This file contains common functions and utilities used across the theme.
 * Include this file in functions.php or use get_template_part('parts/common').
 *
 * @package theme-abcontact
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Helper function to get theme asset URL with cache busting
 *
 * @param string $path Relative path to asset from theme directory.
 * @return string Full URL to asset with version parameter.
 */
function abcontact_get_asset_url( $path ) {
    $file_path = get_stylesheet_directory() . '/' . ltrim( $path, '/' );
    $file_uri  = get_stylesheet_directory_uri() . '/' . ltrim( $path, '/' );
    
    if ( file_exists( $file_path ) ) {
        return add_query_arg( 'ver', filemtime( $file_path ), $file_uri );
    }
    
    return $file_uri;
}

/**
 * Helper function to safely output escaped attribute
 *
 * @param string $value Value to escape.
 * @return string Escaped value.
 */
function abcontact_esc_attr( $value ) {
    return esc_attr( $value );
}

/**
 * Helper function to safely output escaped HTML
 *
 * @param string $value Value to escape.
 * @return string Escaped value.
 */
function abcontact_esc_html( $value ) {
    return esc_html( $value );
}
