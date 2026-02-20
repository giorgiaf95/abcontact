<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

// Hero
get_template_part( 'template-parts/hero' );

/* ============================= Sezione Servizi ============================= */
$services_output = '';

if ( function_exists( 'render_abcontact_services' ) ) {
    $services_output = render_abcontact_services();
}

if ( empty( $services_output ) ) {
    if ( locate_template( 'template-parts/services.php' ) ) {
        ob_start();
        get_template_part( 'template-parts/services' );
        $services_output = ob_get_clean();
    } else {
        $plugin_template = defined( 'ABCONTACT_DIR' ) ? ABCONTACT_DIR . 'templates/template-parts/services.php' : '';
        if ( $plugin_template && file_exists( $plugin_template ) ) {
            ob_start();
            include $plugin_template;
            $services_output = ob_get_clean();
        }
    }
}

if ( ! empty( $services_output ) ) {
    echo $services_output;
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
/* ============================= CTA: include partial (the partial will source metaboxes itself) ============================= */

get_template_part( 'template-parts/cta' );

get_footer();