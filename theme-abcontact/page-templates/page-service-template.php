<?php
/**
 * Template Name: Service Page (ABContact)
 * Description: Service page template with centered text group above the white card,
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$post_id = get_the_ID();

$theme_css_path = get_stylesheet_directory() . '/assets/css/service-template.css';
$theme_css_uri  = get_stylesheet_directory_uri() . '/assets/css/service-template.css';
if ( file_exists( $theme_css_path ) ) {
    wp_enqueue_style( 'ab-service-template', $theme_css_uri, array(), filemtime( $theme_css_path ) );
}

$hero_shared_path = get_stylesheet_directory() . '/assets/css/hero-shared.css';
if ( file_exists( $hero_shared_path ) ) {
    wp_enqueue_style( 'hero-shared', get_stylesheet_directory_uri() . '/assets/css/hero-shared.css', array(), filemtime( $hero_shared_path ) );
}

get_header();

/* ============================= Hero (partial) ============================= */
$hero_id = get_post_thumbnail_id( $post_id );
$hero_image_url = $hero_id ? wp_get_attachment_image_url( $hero_id, 'full' ) : '';

$hero_args = array(
    'eyebrow'   => __( 'Servizi', 'abcontact' ),
    'title'     => get_the_title( $post_id ),
    'subtitle'  => '',
    'bg_url'    => $hero_image_url,
    'cta'       => get_post_meta( $post_id, 'service_form_link', true ),
    'cta_label' => __( 'Richiedi Preventivo', 'abcontact' ),
);

set_query_var( 'args', $hero_args );
get_template_part( 'template-parts/news-hero' );
set_query_var( 'args', null );

/* Floating image */
$floating_image_id = intval( get_post_meta( $post_id, 'service_body_image_id', true ) );
if ( ! $floating_image_id ) {
    $floating_image_id = get_post_thumbnail_id( $post_id );
}

/* Text group */
$heading1 = get_post_meta( $post_id, 'service_box_heading1', true );
if ( empty( $heading1 ) ) {
    $heading1 = get_post_meta( $post_id, 'service_group1_title', true );
}
$text1 = get_post_meta( $post_id, 'service_box_text1', true );
if ( empty( $text1 ) ) {
    $text1 = get_post_meta( $post_id, 'service_group1_text', true );
}

$heading2 = get_post_meta( $post_id, 'service_box_heading2', true );
if ( empty( $heading2 ) ) {
    $heading2 = get_post_meta( $post_id, 'service_group2_title', true );
}
$text2 = get_post_meta( $post_id, 'service_box_text2', true );
if ( empty( $text2 ) ) {
    $text2 = get_post_meta( $post_id, 'service_group2_text', true );
}

/* Phases */
$default_phases = array(
    array( 'title' => __( 'Analisi', 'abcontact' ),     'text' => __( 'Analizziamo le tue bollette attuali', 'abcontact' ) ),
    array( 'title' => __( 'Confronto', 'abcontact' ),   'text' => __( 'Confrontiamo tutte le offerte disponibili', 'abcontact' ) ),
    array( 'title' => __( 'Proposta', 'abcontact' ),    'text' => __( 'Ti presentiamo la soluzione migliore', 'abcontact' ) ),
    array( 'title' => __( 'Attivazione', 'abcontact' ), 'text' => __( 'Gestiamo tutto noi, senza interruzioni', 'abcontact' ) ),
);

$phases = array();
for ( $i = 1; $i <= 4; $i++ ) {
    $t = get_post_meta( $post_id, "service_phase_{$i}_title", true );
    $x = get_post_meta( $post_id, "service_phase_{$i}_text", true );
    if ( $t || $x ) {
        $phases[] = array( 'title' => $t ? $t : $default_phases[ $i - 1 ]['title'], 'text' => $x ? $x : $default_phases[ $i - 1 ]['text'] );
    } else {
        $phases[] = $default_phases[ $i - 1 ];
    }
}

/* Phases section heading/subheading (optional) */
$phases_section_title    = get_post_meta( $post_id, 'service_phases_heading', true );
$phases_section_subtitle = get_post_meta( $post_id, 'service_phases_subheading', true );
?>

<main class="service-main container" role="main">
  <div class="service-content-inner">

    <!-- Floating area: text group + card -->
    <section class="service-floating" aria-hidden="false" role="region" aria-label="<?php echo esc_attr__( 'Box centrale servizi', 'abcontact' ); ?>">
      <div class="service-floating__wrap">

        <!-- TEXT GROUP (centered horizontally; text left-aligned inside) -->
        <?php if ( $heading1 || $text1 || $heading2 || $text2 ) : ?>
          <div class="service-floating-text" role="region" aria-label="<?php echo esc_attr__( 'Testo sopra il box', 'abcontact' ); ?>">
            <div class="service-floating-text__inner">
              <?php if ( $heading1 ) : ?>
                <h2 class="service-floating-text__heading"><?php echo esc_html( $heading1 ); ?></h2>
              <?php endif; ?>

              <?php if ( $text1 ) : ?>
                <div class="service-floating-text__body"><?php echo wp_kses_post( wpautop( $text1 ) ); ?></div>
              <?php endif; ?>

              <?php if ( $heading2 ) : ?>
                <h3 class="service-floating-text__heading-alt"><?php echo esc_html( $heading2 ); ?></h3>
              <?php endif; ?>

              <?php if ( $text2 ) : ?>
                <div class="service-floating-text__body-alt"><?php echo wp_kses_post( wpautop( $text2 ) ); ?></div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- WHITE CARD (contains image if present) -->
        <div class="service-floating-card" role="group" aria-label="<?php echo esc_attr__( 'Box informativo centrale', 'abcontact' ); ?>">
          <?php if ( $floating_image_id ) : ?>
            <div class="service-floating-card__media" aria-hidden="false">
              <?php
              // Use media-figure component
              get_template_part( 'template-parts/components/media-figure', null, array(
                  'attachment_id' => $floating_image_id,
                  'size' => defined( 'SERVICE_GROUP_IMG_NAME' ) ? SERVICE_GROUP_IMG_NAME : array( 600, 400 ),
                  'class' => 'service-floating-card__img-wrap',
                  'alt'  => esc_attr( get_post_meta( $floating_image_id, '_wp_attachment_image_alt', true ) ?: get_the_title( $floating_image_id ) ),
              ) );
              ?>
            </div>
          <?php endif; ?>

          <div class="card-inner" aria-hidden="true"></div>
        </div>

        <!-- CTA: placed inside the wrap so moving it won't leave a gap outside -->
        <div class="service-floating-cta" aria-hidden="false">
          <?php
          $cta_href = get_post_meta( $post_id, 'service_form_link', true ) ?: '#';
          get_template_part( 'template-parts/components/button', null, array(
              'label'   => __( 'Richiedi Preventivo', 'abcontact' ),
              'href'    => $cta_href,
              'variant' => 'primary',
              'size'    => 'lg',
              'class'   => 'service-cta-button',
          ) );
          ?>
        </div>

      </div>
    </section>

    <!-- Optional phases header (title + subtitle) -->
    <?php if ( $phases_section_title || $phases_section_subtitle ) : ?>
      <?php
      if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
          echo "\n<!-- DEBUG: phases heading=" . esc_html( $phases_section_title ) . " | subtitle=" . esc_html( wp_trim_words( wp_strip_all_tags( $phases_section_subtitle ), 16 ) ) . " -->\n";
      }
      ?>
      <header class="service-phases-header">
        <div class="container">
          <?php if ( $phases_section_title ) : ?>
            <h2 class="service-phases-heading"><?php echo esc_html( $phases_section_title ); ?></h2>
          <?php endif; ?>
          <?php if ( $phases_section_subtitle ) : ?>
            <div class="service-phases-subheading"><?php echo wp_kses_post( wpautop( $phases_section_subtitle ) ); ?></div>
          <?php endif; ?>
        </div>
      </header>
    <?php endif; ?>

    <!-- Phases -->
    <section class="service-phases" aria-label="<?php echo esc_attr__( 'Fasi del servizio', 'abcontact' ); ?>">
      <div class="service-phases-inner">
        <?php foreach ( $phases as $idx => $ph ) : $num = $idx + 1; ?>
            <div class="service-phase" role="group" aria-labelledby="service-phase-title-<?php echo esc_attr( $num ); ?>">
              <div class="service-phase-icon" aria-hidden="true">
                <span class="phase-number"><?php echo esc_html( $num ); ?></span>
              </div>
              <?php if ( ! empty( $ph['title'] ) ) : ?>
                <h4 id="service-phase-title-<?php echo esc_attr( $num ); ?>" class="service-phase-title"><?php echo esc_html( $ph['title'] ); ?></h4>
              <?php endif; ?>
              <?php if ( ! empty( $ph['text'] ) ) : ?>
                <div class="service-phase-text"><?php echo esc_html( $ph['text'] ); ?></div>
              <?php endif; ?>
            </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Bottom CTA (global fallback / partial) -->
    <div class="service-bottom-cta">
      <?php
      if ( locate_template( 'template-parts/cta.php' ) ) {
          get_template_part( 'template-parts/cta' );
      } else {
          ?>
          <div class="service-cta-fallback" role="region" aria-label="<?php echo esc_attr__( 'Call to action', 'abcontact' ); ?>">
            <p class="service-cta-text"><?php echo esc_html__( 'Hai un progetto? Contattaci per una consulenza gratuita.', 'abcontact' ); ?></p>
            <p><?php
            get_template_part( 'template-parts/components/button', null, array(
                'label' => __( 'Consulenza Gratuita', 'abcontact' ),
                'href'  => get_post_meta( $post_id, 'service_form_link', true ) ?: home_url( '/contattaci' ),
                'variant'=> 'primary',
                'size' => 'md',
            ) );
            ?></p>
          </div>
          <?php
      }
      ?>
    </div>

  </div>
</main>

<?php
get_footer();