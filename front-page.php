<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

// Hero
get_template_part( 'template-parts/hero' );

/* ============================= Sezione Servizi (THEME ONLY) ============================= */
if ( locate_template( 'template-parts/services.php' ) ) {
    get_template_part( 'template-parts/services' );
}

/* ============================= Sezione Why us card ============================= */
if ( function_exists( 'render_why_us_cards' ) ) {
    echo render_why_us_cards( array(
        'count'   => 4,
        'columns' => 4,
        'class'   => 'why-us--home',
    ) );
}

/* ============================= Featured grid ============================= */
?>
<div class="container">
    <?php get_template_part( 'template-parts/featured-grid' ); ?>
</div>

<?php
/* ============================= CTA ============================= */
get_template_part( 'template-parts/cta' );

get_footer();