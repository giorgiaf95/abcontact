<?php
/**
 * Template Name: Front Page Template
 *
 * Alternative front page template that can be selected from page attributes.
 * This template uses modular parts for hero, services, and CTA sections.
 *
 * @package theme-abcontact
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

// Hero (front page specific)
get_template_part( 'parts/hero-home' );

if ( function_exists( 'render_why_us_cards' ) ) {
    echo render_why_us_cards( array(
        'count'   => 4,
        'columns' => 4,
        'class'   => 'why-us--home',
    ) );
}

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
    } elseif ( defined( 'ABCONTACT_DIR' ) ) {
        $plugin_template = ABCONTACT_DIR . 'templates/template-parts/services.php';
        if ( file_exists( $plugin_template ) ) {
            ob_start();
            include $plugin_template;
            $services_output = ob_get_clean();
        }
    }
}

if ( ! empty( $services_output ) ) {
    echo $services_output;
}

?>

<div class="container">
    <?php get_template_part( 'template-parts/featured-grid' ); ?>
</div>

<?php 
// CTA section (uses modular part)
get_template_part( 'parts/cta' );

get_footer();
