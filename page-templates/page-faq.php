<?php
/**
 * Template Name: FAQ
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$post_id = get_the_ID();

/* Enqueue CSS for FAQ page */
$css_path = get_stylesheet_directory() . '/assets/css/page-faq.css';
if ( file_exists( $css_path ) ) {
    wp_enqueue_style( 'ab-page-faq', get_stylesheet_directory_uri() . '/assets/css/page-faq.css', array(), filemtime( $css_path ) );
}

/* HERO: forza subtitle = false per impedire qualsiasi fallback */
$hero_args = array(
    'eyebrow'   => '',
    'title'     => get_the_title( $post_id ),
    'subtitle'  => false, // <<<<<<<<<< IMPORTANT
    'bg_url'    => get_the_post_thumbnail_url( $post_id, 'full' ),
    'cta'       => '',
    'cta_label' => '',
);

set_query_var( 'args', $hero_args );
get_template_part( 'template-parts/news-hero' );
set_query_var( 'args', null );
?>

<main id="main" class="site-main page-faq">
  <div class="container">
    <div class="page-faq__content">
      <?php
      while ( have_posts() ) :
        the_post();
        the_content();
      endwhile;
      ?>
    </div>
  </div>
</main>

<?php get_footer(); ?>