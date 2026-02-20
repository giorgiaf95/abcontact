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

// --- Full image block (high-res with srcset where available) ---
$full_img_markup = '';
$full_id = intval( get_post_meta( $post_id, 'service_full_image_id', true ) );

if ( $full_id ) {
    // Use 'full' so WP outputs a high-res src (and srcset if available).
    $full_img_markup = wp_get_attachment_image( $full_id, 'full', false, array(
        'class' => 'service-full-image',
        'alt'   => get_the_title( $post_id ),
    ) );
} elseif ( function_exists( '_meta_img_url' ) ) {
    $fallback_url = _meta_img_url( $post_id, 'service_full_image_id', 'full' );
    if ( $fallback_url ) {
        $full_img_markup = '<img src="' . esc_url( $fallback_url ) . '" class="service-full-image" alt="' . esc_attr( get_the_title( $post_id ) ) . '" />';
    }
}

if ( $full_img_markup ) :
?>
  <figure class="sp-full-image" role="img" aria-hidden="true">
    <?php echo $full_img_markup; ?>
  </figure>
<?php endif; ?>

<?php
// Render additional text groups (repeater) stored in _service_additional_texts
// Robust handling: meta may be stored as an array (WP-serialized), as JSON string, or serialized string.
// We try to support all forms and produce a clean array for rendering.

$additional_raw = get_post_meta( $post_id, '_service_additional_texts', true );
$additional = array();

if ( is_array( $additional_raw ) && ! empty( $additional_raw ) ) {
    // WP already returned the unserialized array
    $additional = $additional_raw;
} elseif ( $additional_raw ) {
    // Try JSON decode (legacy format)
    $decoded = json_decode( $additional_raw, true );
    if ( $decoded === null ) {
        // maybe escaped JSON
        $decoded = json_decode( wp_unslash( $additional_raw ), true );
    }
    if ( is_array( $decoded ) ) {
        $additional = $decoded;
    } else {
        // Fallback: maybe serialized PHP array
        $maybe = maybe_unserialize( $additional_raw );
        if ( is_array( $maybe ) ) {
            $additional = $maybe;
        }
    }
}

/**
 * We render additional blocks using a centered container pattern that matches
 * the intro block: a .container wrapper and an inner element centered to the same width.
 * This ensures alignment and consistent behavior.
 */
if ( ! empty( $additional ) ) :
    foreach ( $additional as $block ) :
        $block_title = isset( $block['title'] ) ? $block['title'] : '';
        $block_body  = isset( $block['body'] ) ? $block['body'] : '';

        // sanitize before output
        $block_title_s = $block_title ? sanitize_text_field( $block_title ) : '';
        // body may contain allowed HTML; sanitize and preserve newlines
        $block_body_s = $block_body ? wp_kses_post( $block_body ) : '';
        ?>
        <section class="sp-additional container" aria-label="<?php echo esc_attr( $block_title_s ? $block_title_s : __( 'Sezione Aggiuntiva', 'abcontact' ) ); ?>">
          <div class="sp-additional-inner">
            <div class="sp-additional-content">
              <?php if ( $block_title_s ) : ?>
                <h2 class="sp-additional-title"><?php echo esc_html( $block_title_s ); ?></h2>
              <?php endif; ?>
              <?php if ( $block_body_s ) : ?>
                <div class="sp-additional-body"><?php echo wpautop( $block_body_s ); ?></div>
              <?php endif; ?>
            </div>
          </div>
        </section>
        <?php
    endforeach;
endif;
?>

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

<?php
// Read the "How it works" title/subtitle from meta with fallbacks
$how_title = get_post_meta( $post_id, 'service_how_title', true );
$how_sub   = get_post_meta( $post_id, 'service_how_subtitle', true );

if ( empty( $how_title ) ) {
    $how_title = __( 'Come funziona', 'abcontact' );
}
if ( empty( $how_sub ) ) {
    $how_sub = __( 'Il nostro processo in quattro semplici step', 'abcontact' );
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
?>

<?php
get_footer();
?>