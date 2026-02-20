<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_front_page() ) {
    return;
}

/**
 * HOME HERO (prototype-style) - uses existing theme CSS classes:
 * .hero, .hero__inner, .hero__content, .hero__eyebrow, .hero__title, .hero__lead, .hero__actions
 * + adds: .hero__title-highlight, .hero__kpis (styled in main.css patch)
 *
 * Data sources: theme_mods (Customizer) with safe fallbacks.
 */

$hero_img = get_theme_mod( 'abcontact_hero_image', '' );

/**
 * IMPORTANT: CSS expects --hero-overlay (see main.css patch).
 * Backward compatible: if old customizer key exists, we reuse it.
 */
$overlay = get_theme_mod( 'abcontact_hero_overlay_color', '' );
if ( ! $overlay ) {
    $overlay = get_theme_mod( 'hero_overlay', '' );
}
if ( ! $overlay ) {
    $overlay = 'rgba(23, 80, 177, 0.60)';
}

$style_attr = '';
if ( $hero_img ) {
    $style_attr = "background-image:url('" . esc_url( $hero_img ) . "'); --hero-overlay:" . esc_attr( $overlay ) . ';';
} else {
    $style_attr = "--hero-overlay:" . esc_attr( $overlay ) . ';';
}

/* Text content */
$eyebrow = get_theme_mod( 'hero_eyebrow', __( "Il tuo partner per l'energia del futuro", 'theme-abcontact' ) );

/**
 * Title split to allow highlighted word as in prototype.
 * If not configured, fall back to site name.
 */
$title_before    = get_theme_mod( 'hero_title_before', '' );
$title_highlight = get_theme_mod( 'hero_title_highlight', '' );
$title_after     = get_theme_mod( 'hero_title_after', '' );

if ( $title_before === '' && $title_highlight === '' && $title_after === '' ) {
    // reasonable default if nothing set
    $title_before    = __( 'Risparmia in bolletta con', 'theme-abcontact' );
    $title_highlight = __( 'Abcontact', 'theme-abcontact' );
    $title_after     = '';
}

$lead = get_theme_mod( 'hero_lead', '' );
if ( $lead === '' ) {
    $lead = get_bloginfo( 'description' );
}

/* CTA buttons (use existing button component) */
$primary_label = get_theme_mod( 'hero_primary_label', __( 'Scopri i servizi', 'theme-abcontact' ) );
$primary_link  = get_theme_mod( 'hero_primary_link', home_url( '/servizi' ) );

$secondary_label = get_theme_mod( 'hero_secondary_label', __( 'Richiedi consulenza', 'theme-abcontact' ) );
$secondary_link  = get_theme_mod( 'hero_secondary_link', home_url( '/contatti' ) );

/* KPIs (3) */
$kpi_1_value = get_theme_mod( 'hero_kpi_1_value', '30%' );
$kpi_1_label = get_theme_mod( 'hero_kpi_1_label', __( 'Risparmio medio', 'theme-abcontact' ) );

$kpi_2_value = get_theme_mod( 'hero_kpi_2_value', '1000+' );
$kpi_2_label = get_theme_mod( 'hero_kpi_2_label', __( 'Clienti soddisfatti', 'theme-abcontact' ) );

$kpi_3_value = get_theme_mod( 'hero_kpi_3_value', '10+' );
$kpi_3_label = get_theme_mod( 'hero_kpi_3_label', __( 'Anni di esperienza', 'theme-abcontact' ) );
?>
<section class="hero" aria-label="<?php esc_attr_e( 'Hero', 'theme-abcontact' ); ?>" style="<?php echo esc_attr( $style_attr ); ?>">
  <div class="hero__inner container">
    <div class="hero__content">
      <?php if ( $eyebrow ) : ?>
        <p class="hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
      <?php endif; ?>

      <h1 class="hero__title">
        <?php echo esc_html( $title_before ); ?>
        <?php if ( $title_highlight !== '' ) : ?>
          <span class="hero__title-highlight"><?php echo esc_html( $title_highlight ); ?></span>
        <?php endif; ?>
        <?php echo esc_html( $title_after ); ?>
      </h1>

      <?php if ( $lead ) : ?>
        <p class="hero__lead"><?php echo esc_html( $lead ); ?></p>
      <?php endif; ?>

      <div class="hero__actions">
        <?php
        if ( $primary_link ) {
            // try to keep button classes consistent with your CSS (btn-primary / btn-ghost)
            get_template_part( 'template-parts/components/button', null, array(
                'label'   => $primary_label,
                'href'    => $primary_link,
                'variant' => 'primary',
                'size'    => 'lg',
                'class'   => 'btn btn-primary',
            ) );
        }

        if ( $secondary_link ) {
            get_template_part( 'template-parts/components/button', null, array(
                'label'   => $secondary_label,
                'href'    => $secondary_link,
                'variant' => 'ghost',
                'size'    => 'lg',
                'class'   => 'btn btn-ghost',
            ) );
        }
        ?>
      </div>

      <div class="hero__kpis" role="list" aria-label="<?php echo esc_attr__( 'Statistiche', 'theme-abcontact' ); ?>">
        <div class="hero__kpi" role="listitem">
          <div class="hero__kpi-value"><?php echo esc_html( $kpi_1_value ); ?></div>
          <div class="hero__kpi-label"><?php echo esc_html( $kpi_1_label ); ?></div>
        </div>
        <div class="hero__kpi" role="listitem">
          <div class="hero__kpi-value"><?php echo esc_html( $kpi_2_value ); ?></div>
          <div class="hero__kpi-label"><?php echo esc_html( $kpi_2_label ); ?></div>
        </div>
        <div class="hero__kpi" role="listitem">
          <div class="hero__kpi-value"><?php echo esc_html( $kpi_3_value ); ?></div>
          <div class="hero__kpi-label"><?php echo esc_html( $kpi_3_label ); ?></div>
        </div>
      </div>

    </div>
  </div>
</section>