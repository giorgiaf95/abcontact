<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$defaults = array(
    'eyebrow'   => '',
    'title'     => '',
    'subtitle'  => '',
    'bg_url'    => '',
    'cta'       => '',
    'cta_label' => '',
);

$provided_args = get_query_var( 'args', array() );
if ( ! is_array( $provided_args ) ) {
    $provided_args = array();
}
$passed = wp_parse_args( $provided_args, $defaults );

$eyebrow_passed  = isset( $passed['eyebrow'] ) ? $passed['eyebrow'] : '';
$title_passed    = isset( $passed['title'] ) ? $passed['title'] : '';
$subtitle_passed = isset( $passed['subtitle'] ) ? $passed['subtitle'] : '';
$bg_url_passed   = isset( $passed['bg_url'] ) ? $passed['bg_url'] : '';
$cta             = isset( $passed['cta'] ) ? $passed['cta'] : '';
$cta_label       = isset( $passed['cta_label'] ) ? $passed['cta_label'] : '';

/* ============================= Title selection ============================= */
$title = '';
if ( $title_passed !== '' ) {
    $title = $title_passed;
} elseif ( is_singular() ) {
    $title = get_the_title();
} elseif ( is_home() ) {
    $posts_page_id = (int) get_option( 'page_for_posts' );
    if ( $posts_page_id ) {
        $title = get_the_title( $posts_page_id );
    } else {
        $title = __( 'News & Aggiornamenti', 'theme-abcontact' );
    }
} elseif ( is_archive() ) {
    $title = get_the_archive_title();
    if ( ! $title ) {
        $title = __( 'News & Aggiornamenti', 'theme-abcontact' );
    }
} else {
    $title = __( 'News & Aggiornamenti', 'theme-abcontact' );
}

/* ============================= Subtitle fallback logic ============================= */
$subtitle = '';
if ( $subtitle_passed !== '' ) {
    $subtitle = $subtitle_passed;
} else {
    if ( is_singular() && has_excerpt() ) {
        $subtitle = get_the_excerpt();
    } elseif ( is_singular() ) {
        $content = get_post_field( 'post_content', get_the_ID() );
        if ( $content ) {
            $content = apply_filters( 'the_content', $content );
            $content_no_imgs = preg_replace( '/<img[^>]+\>/i', '', $content );
            if ( preg_match( '/<p[^>]*>(.*?)<\/p>/is', $content_no_imgs, $m ) ) {
                $subtitle = strip_tags( $m[1] );
            } else {
                $subtitle = wp_trim_words( wp_strip_all_tags( $content_no_imgs ), 30 );
            }
        }
    } elseif ( is_home() ) {
        $posts_page_id = (int) get_option( 'page_for_posts' );
        if ( $posts_page_id && get_post_field( 'post_excerpt', $posts_page_id ) ) {
            $subtitle = get_post_field( 'post_excerpt', $posts_page_id );
        }
    }
}

/* ============================= bg_url fallback ============================= */
$bg_url = $bg_url_passed ?: '';

if ( empty( $bg_url ) ) {
    $queried_id = get_queried_object_id();
    if ( $queried_id ) {
        $hid = get_post_thumbnail_id( $queried_id );
        if ( $hid ) {
            $bg_url = wp_get_attachment_image_url( $hid, 'full' );
        }
    }
}

// As extra fallback for singular if still empty
if ( empty( $bg_url ) && is_singular() ) {
    $hid = get_post_thumbnail_id( get_the_ID() );
    if ( $hid ) {
        $bg_url = wp_get_attachment_image_url( $hid, 'full' );
    }
}

$title_attr = wp_strip_all_tags( $title );
?>
<section class="hero news-hero" role="banner" aria-label="<?php echo esc_attr( $title_attr ); ?>">
  <?php if ( $bg_url ) : ?>
    <img class="hero__bg-img" src="<?php echo esc_url( $bg_url ); ?>" alt="" aria-hidden="true" />
  <?php endif; ?>

  <div class="hero-inner container">
    <div class="hero-content">
      <?php if ( $eyebrow_passed ) : ?>
        <p class="hero__eyebrow"><?php echo esc_html( $eyebrow_passed ); ?></p>
      <?php endif; ?>

      <?php if ( $title ) : ?>
        <h1 class="hero-title"><?php echo esc_html( $title ); ?></h1>
      <?php endif; ?>

      <?php if ( $subtitle ) : ?>
        <p class="hero-subtitle hero__lead"><?php echo wp_kses_post( wpautop( $subtitle ) ); ?></p>
      <?php endif; ?>

      <?php if ( $cta ) : ?>
        <p class="hero-cta">
          <?php
          get_template_part( 'template-parts/components/button', null, array(
            'label' => $cta_label ?: __( 'Scopri', 'theme-abcontact' ),
            'href'  => $cta,
            'variant' => 'primary',
            'size' => 'md',
          ) );
          ?>
        </p>
      <?php endif; ?>
    </div>
  </div>

  <div class="service-hero-overlay" aria-hidden="true"></div>
</section>