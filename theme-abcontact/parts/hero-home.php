<?php
/**
 * Hero Home Template Part
 *
 * Hero section specifically designed for the front page.
 * This template includes the full hero with eyebrow, title, lead text, and CTA buttons.
 *
 * @package theme-abcontact
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Only render on front page
if ( ! is_front_page() ) {
    return;
}

$hero_img = get_theme_mod( 'abcontact_hero_image' );
$overlay  = get_theme_mod( 'abcontact_hero_overlay_color', 'rgba(6,68,179,0.36)' );
$style_attr = '';

if ( $hero_img ) {
    $style_attr = "background-image: url('" . esc_url( $hero_img ) . "'); --hero-overlay: " . esc_attr( $overlay ) . ";";
}
?>
<section class="hero hero-home" aria-label="<?php esc_attr_e( 'Hero', 'theme-abcontact' ); ?>" <?php if ( $style_attr ) : ?>style="<?php echo $style_attr; ?>"<?php endif; ?>>
  <div class="hero__inner container">
    <div class="hero__content">
      <p class="hero__eyebrow"><?php echo esc_html__( "Il Tuo Partner per l'Energia del Futuro", 'theme-abcontact' ); ?></p>

      <h1 class="hero__title">
        <?php
        echo esc_html( get_bloginfo( 'name' ) );
        ?>
      </h1>

      <?php if ( get_bloginfo( 'description' ) ) : ?>
        <p class="hero__lead"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
      <?php endif; ?>

      <div class="hero__actions">
        <?php
        get_template_part( 'template-parts/components/button', null, array(
            'label'   => __( 'Richiedi Consulenza', 'theme-abcontact' ),
            'href'    => home_url( '/contatti' ),
            'variant' => 'primary',
            'size'    => 'lg',
        ) );
        get_template_part( 'template-parts/components/button', null, array(
            'label'   => __( 'Contattaci', 'theme-abcontact' ),
            'href'    => home_url( '/contatti' ),
            'variant' => 'ghost',
            'size'    => 'md',
        ) );
        ?>
      </div>
    </div>
  </div>
</section>
