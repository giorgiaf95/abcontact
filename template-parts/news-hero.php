<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * INTERNAL HERO (prototype-style, NO IMAGE)
 * Used across: page templates, single posts, news page.
 *
 * IMPORTANT:
 * - No featured image background for internal heroes (as requested).
 * - Home hero remains the only one with background image.
 *
 * Inputs priority for texts:
 * 1) passed args via set_query_var('args')
 * 2) metabox (post meta) on queried object
 * 3) fallbacks (title/excerpt/first paragraph)
 */

$defaults = array(
    'eyebrow'      => '',
    'title'        => '',
    'subtitle'     => '',
    'cta'          => '',
    'cta_label'    => '',
    'back_link'    => '',
    'back_label'   => '',
    'overlay_rgba' => '',
    'bg_color'     => '', // optional: solid background color
);

$provided_args = get_query_var( 'args', array() );
if ( ! is_array( $provided_args ) ) {
    $provided_args = array();
}
$passed = wp_parse_args( $provided_args, $defaults );

/* -------------------- metabox (queried object) -------------------- */
$qo_id = (int) get_queried_object_id();
$meta = array();
if ( $qo_id ) {
    $meta['enabled']      = get_post_meta( $qo_id, 'ab_hero_enabled', true );
    $meta['eyebrow']      = get_post_meta( $qo_id, 'ab_hero_eyebrow', true );
    $meta['title']        = get_post_meta( $qo_id, 'ab_hero_title', true );
    $meta['subtitle']     = get_post_meta( $qo_id, 'ab_hero_subtitle', true );
    $meta['cta_label']    = get_post_meta( $qo_id, 'ab_hero_cta_label', true );
    $meta['cta_url']      = get_post_meta( $qo_id, 'ab_hero_cta_url', true );
    $meta['overlay_rgba'] = get_post_meta( $qo_id, 'ab_hero_overlay_rgba', true );
    $meta['bg_color']     = get_post_meta( $qo_id, 'ab_hero_bg_color', true ); // optional
    $meta['back_label']   = get_post_meta( $qo_id, 'ab_hero_back_label', true );
    $meta['back_url']     = get_post_meta( $qo_id, 'ab_hero_back_url', true );
}

/* allow disabling hero on pages via metabox */
if ( is_page() && isset( $meta['enabled'] ) && $meta['enabled'] !== '' ) {
    if ( (int) $meta['enabled'] !== 1 ) {
        return;
    }
}

/* Eyebrow */
$eyebrow = $passed['eyebrow'] !== '' ? $passed['eyebrow'] : ( $meta['eyebrow'] ?? '' );

/* Title */
$title = '';
if ( $passed['title'] !== '' ) {
    $title = $passed['title'];
} elseif ( ! empty( $meta['title'] ) ) {
    $title = $meta['title'];
} elseif ( is_singular() ) {
    $title = get_the_title();
} elseif ( is_home() ) {
    $posts_page_id = (int) get_option( 'page_for_posts' );
    $title = $posts_page_id ? get_the_title( $posts_page_id ) : __( 'News & Aggiornamenti', 'theme-abcontact' );
} elseif ( is_archive() ) {
    $title = get_the_archive_title() ?: __( 'News & Aggiornamenti', 'theme-abcontact' );
} else {
    $title = __( 'News & Aggiornamenti', 'theme-abcontact' );
}

/* Subtitle */
$subtitle = '';
if ( $passed['subtitle'] !== '' ) {
    $subtitle = $passed['subtitle'];
} elseif ( ! empty( $meta['subtitle'] ) ) {
    $subtitle = $meta['subtitle'];
} else {
    if ( is_singular() && has_excerpt() ) {
        $subtitle = get_the_excerpt();
    } elseif ( is_singular() ) {
        $content = get_post_field( 'post_content', get_the_ID() );
        if ( $content ) {
            $content = apply_filters( 'the_content', $content );
            $content_no_imgs = preg_replace( '/<img[^>]+\\>/i', '', $content );
            if ( preg_match( '/<p[^>]*>(.*?)<\\/p>/is', $content_no_imgs, $m ) ) {
                $subtitle = strip_tags( $m[1] );
            } else {
                $subtitle = wp_trim_words( wp_strip_all_tags( $content_no_imgs ), 26 );
            }
        }
    } elseif ( $qo_id && get_post_field( 'post_excerpt', $qo_id ) ) {
        $subtitle = get_post_field( 'post_excerpt', $qo_id );
    }
}

/* CTA */
$cta_url   = $passed['cta'] !== '' ? $passed['cta'] : ( $meta['cta_url'] ?? '' );
$cta_label = $passed['cta_label'] !== '' ? $passed['cta_label'] : ( $meta['cta_label'] ?? '' );

/* Back link */
$back_url   = $passed['back_link'] !== '' ? $passed['back_link'] : ( $meta['back_url'] ?? '' );
$back_label = $passed['back_label'] !== '' ? $passed['back_label'] : ( $meta['back_label'] ?? '' );
if ( $back_url === '' && is_page() ) {
    $back_url = home_url( '/' );
    $back_label = $back_label ?: __( 'Torna alla home', 'theme-abcontact' );
}

/* Solid background color (prototype) */
$bg_color = $passed['bg_color'] !== '' ? $passed['bg_color'] : ( $meta['bg_color'] ?? '' );
if ( $bg_color === '' ) {
    $bg_color = '#0f1b33'; // prototype-like navy
}

/* Overlay (kept for flexibility; can be transparent) */
$overlay = '';
if ( $passed['overlay_rgba'] !== '' ) {
    $overlay = $passed['overlay_rgba'];
} elseif ( ! empty( $meta['overlay_rgba'] ) ) {
    $overlay = $meta['overlay_rgba'];
} else {
    // For solid background we can keep overlay subtle or transparent.
    $overlay = 'rgba(0,0,0,0.00)';
}

$style_attr = '--hero-overlay:' . $overlay . '; --news-hero-bg:' . $bg_color . ';';
?>
<section class="hero news-hero news-hero--solid" aria-label="<?php echo esc_attr( wp_strip_all_tags( $title ) ); ?>" style="<?php echo esc_attr( $style_attr ); ?>">
  <div class="hero-inner container">
    <div class="hero-content hero-content--internal">

      <?php if ( $back_url && $back_label ) : ?>
        <a class="hero__back-link" href="<?php echo esc_url( $back_url ); ?>">← <?php echo esc_html( $back_label ); ?></a>
      <?php endif; ?>

      <?php if ( $eyebrow ) : ?>
        <span class="hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
      <?php endif; ?>

      <h1 class="hero-title"><?php echo esc_html( $title ); ?></h1>

      <?php if ( $subtitle ) : ?>
        <p class="hero-subtitle"><?php echo esc_html( $subtitle ); ?></p>
      <?php endif; ?>

      <?php if ( $cta_url ) : ?>
        <div class="hero__actions">
          <?php
          get_template_part( 'template-parts/components/button', null, array(
              'label'   => $cta_label ?: __( 'Richiedi consulenza', 'theme-abcontact' ),
              'href'    => $cta_url,
              'variant' => 'primary',
              'size'    => 'md',
          ) );
          ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
</section>