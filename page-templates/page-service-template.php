<?php
/**
 * Template Name: Servizi
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

/* HERO (title from page title; subtitle from metabox) */
$hero_image = get_the_post_thumbnail_url( $post_id, 'full' ) ?: '';
$hero_subtitle = get_post_meta( $post_id, 'service_hero_subtitle', true );
$hero_subtitle = is_string( $hero_subtitle ) ? wp_kses_post( $hero_subtitle ) : '';

$hero_args = array(
    'eyebrow'   => '',
    'title'     => get_the_title( $post_id ),
    'subtitle'  => $hero_subtitle,
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
        <?php if ( $hero_subtitle ) : ?>
          <p class="sp-hero-sub"><?php echo wp_kses_post( wpautop( $hero_subtitle ) ); ?></p>
        <?php endif; ?>
      </div>
    </header>
    <?php
}
set_query_var( 'args', null );

/* small spacer between hero and content */
?>
<div style="height:32px" aria-hidden="true"></div>
<?php

/* =========================
   MAIN CONTENT (Gutenberg)
   ========================= */
?>
<section class="sp-content container" aria-label="<?php esc_attr_e( 'Contenuto del servizio', 'abcontact' ); ?>">
  <div class="sp-content-inner">
    <?php
    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;
    ?>
  </div>
</section>

<?php
/* =========================
   KEEP: Come funziona / Reviews / CTA
   - Come funziona: ora legge il repeater _service_how_steps (con fallback legacy a 4 fasi)
   ========================= */

/* Read the "How it works" title/subtitle from meta with fallbacks */
$how_title = get_post_meta( $post_id, 'service_how_title', true );
$how_sub   = get_post_meta( $post_id, 'service_how_subtitle', true );

if ( empty( $how_title ) ) {
    $how_title = __( 'Come funziona', 'abcontact' );
}
if ( empty( $how_sub ) ) {
    $how_sub = __( 'Il nostro processo in quattro semplici step', 'abcontact' );
}

/* NEW: Repeater steps (preferred) */
$steps_raw = get_post_meta( $post_id, '_service_how_steps', true );
$steps = is_array( $steps_raw ) ? $steps_raw : array();

/* Fallback legacy 4 phases if repeater empty */
if ( empty( $steps ) ) {
    $phases = array();
    for ( $i = 1; $i <= 4; $i++ ) {
        $phases[] = array(
            'title'   => get_post_meta( $post_id, "service_phase_{$i}_title", true ),
            'text'    => get_post_meta( $post_id, "service_phase_{$i}_text", true ),
            'icon_id' => intval( get_post_meta( $post_id, "service_phase_{$i}_icon_id", true ) ),
        );
    }

    foreach ( $phases as $p ) {
        if ( empty( $p['title'] ) && empty( $p['text'] ) ) {
            continue;
        }
        $steps[] = array(
            'title'   => (string) $p['title'],
            'text'    => (string) $p['text'],
            'icon_id' => (int) $p['icon_id'],
        );
    }
}
?>

<section class="sp-how container" aria-labelledby="how-title">
  <header class="sp-section-header">
    <h3 id="how-title"><?php echo esc_html( $how_title ); ?></h3>
    <?php if ( $how_sub ) : ?>
      <p class="sp-section-sub"><?php echo wp_kses_post( wpautop( $how_sub ) ); ?></p>
    <?php endif; ?>
  </header>

  <div class="sp-how-grid">
    <?php foreach ( $steps as $s ) :
      if ( ! is_array( $s ) ) continue;

      $t = isset( $s['title'] ) ? sanitize_text_field( $s['title'] ) : '';
      $x = isset( $s['text'] ) ? sanitize_textarea_field( $s['text'] ) : '';
      $icon_id = isset( $s['icon_id'] ) ? absint( $s['icon_id'] ) : 0;

      if ( $t === '' && $x === '' ) continue;

      $icon_url = $icon_id ? wp_get_attachment_image_url( $icon_id, array(64,64) ) : '';
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
          <?php if ( $t ) : ?><h4 class="sp-how-title"><?php echo esc_html( $t ); ?></h4><?php endif; ?>
          <?php if ( $x ) : ?><div class="sp-how-text"><?php echo esc_html( $x ); ?></div><?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<?php
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
$post_id = isset( $post_id ) ? (int) $post_id : get_the_ID();

$cta_title        = get_post_meta( $post_id, 'cta_title', true );
$cta_subtitle     = get_post_meta( $post_id, 'cta_subtitle', true );
$cta_button_label = get_post_meta( $post_id, 'cta_button_label', true );
$cta_button_link  = get_post_meta( $post_id, 'cta_button_link', true );
$cta_button_color = get_post_meta( $post_id, 'cta_button_color', true );
$cta_modal_raw    = get_post_meta( $post_id, 'cta_modal', true );
$cta_modal        = $cta_modal_raw ? true : false;

if ( empty( $cta_title ) ) {
    $cta_title = get_post_meta( $post_id, 'cs_cta_title', true ) ?: get_post_meta( $post_id, 'service_final_cta_title', true ) ?: get_post_meta( $post_id, 'lc_cta_title', true );
}
if ( empty( $cta_subtitle ) ) {
    $cta_subtitle = get_post_meta( $post_id, 'cs_cta_text', true ) ?: get_post_meta( $post_id, 'service_final_cta_text', true ) ?: get_post_meta( $post_id, 'lc_cta_text', true );
}
if ( empty( $cta_button_label ) ) {
    $cta_button_label = get_post_meta( $post_id, 'cs_cta_button_label', true ) ?: get_post_meta( $post_id, 'service_final_cta_button_label', true ) ?: get_post_meta( $post_id, 'lc_cta_button_label', true );
}
if ( empty( $cta_button_link ) ) {
    $cta_button_link = get_post_meta( $post_id, 'cs_cta_button_link', true ) ?: get_post_meta( $post_id, 'service_final_cta_link', true ) ?: get_post_meta( $post_id, 'lc_cta_button_link', true );
}
if ( empty( $cta_button_color ) ) {
    $cta_button_color = get_post_meta( $post_id, 'cs_cta_button_color', true ) ?: get_post_meta( $post_id, 'service_final_cta_button_color', true ) ?: get_post_meta( $post_id, 'lc_cta_button_color', true );
}

if ( ! empty( $cta_button_link ) ) {
    $raw = trim( $cta_button_link );
    if ( ! preg_match( '#^https?://#i', $raw ) ) {
        $cta_button_link = home_url( '/' . ltrim( $raw, '/' ) );
    } else {
        $cta_button_link = $raw;
    }
}

$cta_args = array(
    'title'        => $cta_title,
    'subtitle'     => $cta_subtitle,
    'button_label' => $cta_button_label,
    'button_link'  => $cta_button_link,
    'button_color' => $cta_button_color,
    'modal'        => $cta_modal,
);

set_query_var( 'args', $cta_args );
get_template_part( 'template-parts/cta', null, $cta_args );
set_query_var( 'args', null );

get_footer();
?>