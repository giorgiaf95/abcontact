<?php
/**
 * Hero section for all pages except front-page.
 *
 * This hero template is intended for inner pages (about, services, etc.).
 * Uses page title and optional featured image as background.
 *
 * @package theme-abcontact
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Skip if on front page - use hero-home.php instead
if ( is_front_page() ) {
    return;
}

$hero_title    = get_the_title();
$hero_subtitle = '';

// Check for page excerpt as subtitle
if ( has_excerpt() ) {
    $hero_subtitle = get_the_excerpt();
}

// Use featured image as background if available
$hero_bg_style = '';
if ( has_post_thumbnail() ) {
    $hero_img_url  = get_the_post_thumbnail_url( get_the_ID(), 'full' );
    $hero_bg_style = "background-image: url('" . esc_url( $hero_img_url ) . "');";
}
?>
<section class="hero hero--inner" aria-label="<?php echo esc_attr( $hero_title ); ?>" <?php if ( $hero_bg_style ) : ?>style="<?php echo esc_attr( $hero_bg_style ); ?>"<?php endif; ?>>
    <div class="hero__inner container">
        <div class="hero__content">
            <?php if ( $hero_title ) : ?>
                <h1 class="hero__title"><?php echo esc_html( $hero_title ); ?></h1>
            <?php endif; ?>

            <?php if ( $hero_subtitle ) : ?>
                <p class="hero__lead"><?php echo esc_html( $hero_subtitle ); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
