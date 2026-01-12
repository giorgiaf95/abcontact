<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

// Hero
get_template_part( 'template-parts/hero' );

if ( function_exists( 'render_why_us_cards' ) ) {
    echo render_why_us_cards( array(
        'count'   => 4,
        'columns' => 4,
        'class'   => 'why-us--home',
    ) );
} else {
    // fallback: niente (opzionale)
    // echo '<!-- Perché Noi: plugin non attivo o funzione non disponibile -->';
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
    } else {
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

<?php get_template_part( 'template-parts/cta' ); ?>

<?php
get_footer();