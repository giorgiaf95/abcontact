<?php
/**
 * Hero Template Part
 *
 * Generic hero section for pages other than front page.
 * Can be customized via passed arguments or post meta.
 *
 * @package theme-abcontact
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Skip on front page (use hero-home instead)
if ( is_front_page() ) {
    return;
}

// Get arguments passed via get_template_part
$args = isset( $args ) ? $args : array();

// Default values
$defaults = array(
    'title'    => get_the_title(),
    'subtitle' => '',
    'eyebrow'  => '',
    'image'    => '',
    'overlay'  => 'rgba(23, 80, 177, 0.36)',
    'class'    => '',
);

$hero_args = wp_parse_args( $args, $defaults );

/**
 * Sanitize CSS color value to prevent injection
 * Accepts rgba(), rgb(), hex colors, and CSS color names
 *
 * @param string $color The color value to sanitize.
 * @return string Sanitized color value or default.
 */
$sanitize_css_color = function( $color ) {
    $color = wp_strip_all_tags( $color );
    // Allow rgba/rgb, hex colors, and basic color names
    if ( preg_match( '/^(rgba?\([^)]+\)|#[a-fA-F0-9]{3,8}|[a-zA-Z]+)$/', $color ) ) {
        return $color;
    }
    return 'rgba(23, 80, 177, 0.36)'; // Return default if invalid
};

// Build style attribute for background image
$style_attr = '';
if ( ! empty( $hero_args['image'] ) ) {
    $sanitized_overlay = $sanitize_css_color( $hero_args['overlay'] );
    $style_attr = "background-image: url('" . esc_url( $hero_args['image'] ) . "'); --hero-overlay: " . esc_attr( $sanitized_overlay ) . ";";
}

// Build class attribute
$hero_classes = array( 'hero', 'hero-page' );
if ( ! empty( $hero_args['class'] ) ) {
    $hero_classes[] = sanitize_html_class( $hero_args['class'] );
}
?>
<section class="<?php echo esc_attr( implode( ' ', $hero_classes ) ); ?>" aria-label="<?php esc_attr_e( 'Hero', 'theme-abcontact' ); ?>" <?php if ( $style_attr ) : ?>style="<?php echo $style_attr; ?>"<?php endif; ?>>
  <div class="hero-inner container">
    <div class="hero-content">
      <?php if ( ! empty( $hero_args['eyebrow'] ) ) : ?>
        <p class="hero__eyebrow"><?php echo esc_html( $hero_args['eyebrow'] ); ?></p>
      <?php endif; ?>

      <h1 class="hero-title"><?php echo esc_html( $hero_args['title'] ); ?></h1>

      <?php if ( ! empty( $hero_args['subtitle'] ) ) : ?>
        <p class="hero-subtitle"><?php echo esc_html( $hero_args['subtitle'] ); ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>
