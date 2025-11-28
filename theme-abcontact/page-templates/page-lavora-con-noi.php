<?php
/**
 * Template Name: Lavora con noi
 * Description: Pagina "Lavora con noi" — template vuoto con header, hero, footer e CTA.
 *
 * Posiziona questo file nella root del tema (es. wp-content/themes/abcontact-theme/page-lavora-con-noi.php)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$post_id = get_the_ID();

/* Enqueue page-specific CSS if present */
$css_path = get_stylesheet_directory() . '/assets/css/lavora-con-noi.css';
$css_uri  = get_stylesheet_directory_uri() . '/assets/css/lavora-con-noi.css';
if ( file_exists( $css_path ) ) {
    wp_enqueue_style( 'ab-lavora-con-noi', $css_uri, array( 'abcontact-main', 'abcontact-components' ), filemtime( $css_path ) );
}

get_header();

/* ============================= Hero (use news-hero partial with args) ============================= */
$hero_id = get_post_thumbnail_id( $post_id );
$hero_image_url = $hero_id ? wp_get_attachment_image_url( $hero_id, 'full' ) : '';

$hero_args = array(
    'eyebrow'   => __( 'Lavora con noi', 'abcontact' ),
    'title'     => get_the_title( $post_id ),
    'subtitle'  => '', // lascio vuoto; il metabox in futuro potrà popolare
    'bg_url'    => $hero_image_url,
    'cta'       => '', // nessun CTA specifica nel hero per ora
    'cta_label' => '',
);

set_query_var( 'args', $hero_args );
get_template_part( 'template-parts/news-hero' );
set_query_var( 'args', null );
?>

<main class="lavora-main container" role="main" aria-label="<?php echo esc_attr__( 'Lavora con noi content', 'abcontact' ); ?>">
  <div class="lavora-content-inner">
    <!-- Pagina vuota: qui aggiungeremo i contenuti gestiti dal metabox in seguito -->
    <section class="lavora-section lavora-intro" aria-label="<?php echo esc_attr__( 'Introduzione Lavora con noi', 'abcontact' ); ?>">
      <div class="container">
        <!-- placeholder vuoto intenzionale -->
      </div>
    </section>
  </div>

  <?php
  /* CTA globale (stesso partial usato nel resto del sito) */
  if ( locate_template( 'template-parts/cta.php' ) ) {
      get_template_part( 'template-parts/cta' );
  } else {
      // fallback: include cta.php dalla root del tema se non è in template-parts
      if ( locate_template( 'cta.php' ) ) {
          get_template_part( 'cta' );
      }
  }
  ?>
</main>

<?php
get_footer();