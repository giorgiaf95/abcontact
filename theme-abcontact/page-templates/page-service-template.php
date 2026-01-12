<?php
/**
 * Template Name: Servizi — Template che riusa news-hero
 * Template Post Type: page
 *
 * Uses featured image (post thumbnail) as hero (partial template-parts/news-hero.php).
 * CTA has been removed by request; small spacing kept between hero and intro.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$post_id = get_the_ID();

/* enqueue CSS */
$css_path = get_stylesheet_directory() . '/assets/css/service-template.css';
if ( file_exists( $css_path ) ) {
    wp_enqueue_style( 'service-template', get_stylesheet_directory_uri() . '/assets/css/service-template.css', array(), filemtime( $css_path ) );
}

/* optional JS to set header offset if present */
$js_path = get_stylesheet_directory() . '/assets/js/service-hero-offset.js';
if ( file_exists( $js_path ) ) {
    wp_enqueue_script( 'service-hero-offset', get_stylesheet_directory_uri() . '/assets/js/service-hero-offset.js', array(), filemtime( $js_path ), true );
}

/* HERO */
$hero_image = get_the_post_thumbnail_url( $post_id, 'full' ) ?: '';
$hero_args = array(
    'eyebrow'   => '',
    'title'     => get_the_title( $post_id ),
    'subtitle'  => '',
    'bg_url'    => $hero_image,
    'cta'       => '',
    'cta_label' => '',
);
set_query_var( 'args', $hero_args );
if ( locate_template( 'template-parts/news-hero.php' ) ) {
    get_template_part( 'template-parts/news-hero' );
} else {
    ?>
    <header class="sp-hero" style="<?php if ( $hero_image ) echo 'background-image: url(' . esc_url( $hero_image ) . ');'; ?>">
      <div class="sp-hero-inner container">
        <h1 class="sp-hero-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
      </div>
    </header>
    <?php
}
set_query_var( 'args', null );

/* small spacer between hero and intro (keeps minimal gap as requested) */
?>
<div style="height:32px" aria-hidden="true"></div>
<?php

/* Intro */
$intro_title = get_post_meta( $post_id, 'service_intro_title', true );
$intro_body  = get_post_meta( $post_id, 'service_intro_body', true );

?>
<section class="sp-intro container">
  <div class="sp-intro-inner">
    <?php if ( $intro_title ) : ?><h2 class="sp-intro-title"><?php echo esc_html( $intro_title ); ?></h2><?php endif; ?>
    <?php if ( $intro_body ) : ?><div class="sp-intro-body"><?php echo wp_kses_post( wpautop( $intro_body ) ); ?></div><?php endif; ?>
  </div>
</section>

<?php
/* The rest of the template (full image, how it works, reviews, final CTA) left unchanged.
   Fetch and output the same meta fields and markup as before.
*/

$full_image = '';
$full_id = get_post_meta( $post_id, 'service_full_image_id', true );

if ( $full_id ) {
    $full_image = wp_get_attachment_image_url( (int) $full_id, 'full' );
}

// Fallback: se esiste ancora la funzione helper usata in passato, prova quella
if ( ! $full_image && function_exists( '_meta_img_url' ) ) {
    $full_image = _meta_img_url( $post_id, 'service_full_image_id', 'full' );
}

if ( $full_image ) :
?>
  <figure class="sp-full-image" role="img" aria-hidden="true">
    <img src="<?php echo esc_url( $full_image ); ?>" alt="" />
  </figure>
<?php endif; ?>

<?php
// Phases (same as before)
$phases = array();
for ( $i = 1; $i <= 4; $i++ ) {
    $phases[] = array(
        'title' => get_post_meta( $post_id, "service_phase_{$i}_title", true ),
        'text'  => get_post_meta( $post_id, "service_phase_{$i}_text", true ),
        'icon'  => intval( get_post_meta( $post_id, "service_phase_{$i}_icon_id", true ) ),
    );
}
?>

<section class="sp-how container" aria-labelledby="how-title">
  <header class="sp-section-header">
    <h3 id="how-title"><?php echo esc_html__( 'Come funziona', 'abcontact' ); ?></h3>
    <p class="sp-section-sub"><?php echo esc_html__( 'Il nostro processo in quattro semplici step', 'abcontact' ); ?></p>
  </header>

  <div class="sp-how-grid">
    <?php foreach ( $phases as $p ) :
      if ( empty( $p['title'] ) && empty( $p['text'] ) ) continue;
      $icon_url = $p['icon'] ? wp_get_attachment_image_url( $p['icon'], array(64,64) ) : '';
    ?>
      <article class="sp-how-item">
        <div class="sp-how-icon" aria-hidden="true">
          <?php if ( $icon_url ) : ?>
            <img src="<?php echo esc_url( $icon_url ); ?>" alt="">
          <?php else : ?>
            <svg width="36" height="36" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="4" y="4" width="16" height="16" rx="3" ry="3" fill="#fff" opacity="0.9"/></svg>
          <?php endif; ?>
        </div>
        <div class="sp-how-body">
          <?php if ( $p['title'] ) : ?><h4 class="sp-how-title"><?php echo esc_html( $p['title'] ); ?></h4><?php endif; ?>
          <?php if ( $p['text'] ) : ?><div class="sp-how-text"><?php echo wp_kses_post( wpautop( $p['text'] ) ); ?></div><?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<?php
// Reviews: render ONLY if shortcode is present; otherwise do not print section or title
$reviews_shortcode = get_post_meta( $post_id, 'service_reviews_shortcode', true );
if ( ! empty( $reviews_shortcode ) ) :
    $reviews_title     = get_post_meta( $post_id, 'service_reviews_title', true );
    $reviews_sub       = get_post_meta( $post_id, 'service_reviews_sub', true );
    ?>
    <section class="sp-reviews" aria-label="<?php esc_attr_e( 'Recensioni', 'abcontact' ); ?>">
      <div class="container">
        <div class="sp-reviews-header">
          <?php if ( $reviews_title ) : ?><h3 class="reviews-title"><?php echo esc_html( $reviews_title ); ?></h3><?php else : ?><h3 class="reviews-title"><?php echo esc_html__( 'Cosa dicono di noi', 'abcontact' ); ?></h3><?php endif; ?>
          <?php if ( $reviews_sub ) : ?><p class="reviews-sub"><?php echo esc_html( $reviews_sub ); ?></p><?php endif; ?>
        </div>

        <div class="sp-reviews-inner">
          <?php echo do_shortcode( $reviews_shortcode ); ?>
        </div>
      </div>
    </section>
<?php
endif;
?>

<?php
// Final CTA fallback unchanged
$final_cta_text = get_post_meta( $post_id, 'service_final_cta_text', true );
$final_cta_link = get_post_meta( $post_id, 'service_final_cta_link', true );

if ( locate_template( 'template-parts/cta.php' ) ) {
    get_template_part( 'template-parts/cta' );
} elseif ( locate_template( 'cta.php' ) ) {
    get_template_part( 'cta' );
} elseif ( $final_cta_text ) {
    ?>
    <section class="sp-final-cta">
      <div class="container">
        <div class="sp-final-cta-inner">
          <p class="sp-final-cta-text"><?php echo esc_html( $final_cta_text ); ?></p>
          <?php if ( $final_cta_link ) : ?>
            <a class="sp-final-cta-button" href="<?php echo esc_url( $final_cta_link ); ?>"><?php esc_html_e( 'Richiedi un preventivo', 'abcontact' ); ?></a>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php
}
?>

<?php
get_footer();