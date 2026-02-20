<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ============================ Config ============================ */
if ( ! defined( 'SERVICE_GROUP_IMG_WIDTH' ) ) {
    define( 'SERVICE_GROUP_IMG_WIDTH', 700 );
}
if ( ! defined( 'SERVICE_GROUP_IMG_HEIGHT' ) ) {
    define( 'SERVICE_GROUP_IMG_HEIGHT', 200 );
}
if ( ! defined( 'SERVICE_GROUP_IMG_NAME' ) ) {
    define( 'SERVICE_GROUP_IMG_NAME', 'service_group_fixed' );
}

/* ============================ Includes ============================ */
if ( ! defined( 'THEME_INC_DIR' ) ) {
    define( 'THEME_INC_DIR', get_stylesheet_directory() . '/inc' );
}

$inc_files = array(
    get_stylesheet_directory() . '/inc/setup.php',
    get_stylesheet_directory() . '/inc/enqueue.php',
    get_stylesheet_directory() . '/inc/news.php',
    get_stylesheet_directory() . '/inc/metaboxes-lavora.php',
    get_stylesheet_directory() . '/inc/metaboxes-chisiamo-team.php',
    get_stylesheet_directory() . '/inc/metaboxes-services.php',
    get_stylesheet_directory() . '/inc/metaboxes-cta.php',
    get_stylesheet_directory() . '/inc/metaboxes-sedi.php',
    get_stylesheet_directory() . '/inc/chi-siamo-admin.php',
    get_stylesheet_directory() . '/inc/metaboxes-landing.php',
);

foreach ( $inc_files as $file ) {
    if ( file_exists( $file ) ) {
        require_once $file;
    }
}

$news_ajax_path = get_stylesheet_directory() . '/inc/news-ajax.php';
if ( file_exists( $news_ajax_path ) ) {
    require_once $news_ajax_path;
}

/* ============================ Image sizes ============================ */
function abcontact_register_image_sizes() {
    add_image_size( SERVICE_GROUP_IMG_NAME, (int) SERVICE_GROUP_IMG_WIDTH, (int) SERVICE_GROUP_IMG_HEIGHT, true );

    add_image_size( 'chi_siamo_image', 760, 500, true );
    add_image_size( 'chi_siamo_icon', 120, 120, true );

    add_image_size( 'footer-logo', 300, 80, false );
}
add_action( 'after_setup_theme', 'abcontact_register_image_sizes' );

function abcontact_print_image_size_css_var() {
    if ( is_admin() ) {
        return;
    }
    $w = (int) SERVICE_GROUP_IMG_WIDTH;
    $h = (int) SERVICE_GROUP_IMG_HEIGHT;
    echo "<style id=\"abcontact-service-image-size-vars\" type=\"text/css\">:root{ --service-group-image-max: {$w}px; --service-group-image-height: {$h}px; }</style>\n";
}
add_action( 'wp_head', 'abcontact_print_image_size_css_var', 1 );

/* ============================ Light Enqueue helpers (header) ============================ */
function abcontact_enqueue_header_assets() {
    if ( is_admin() ) {
        return;
    }
    $theme_dir  = get_stylesheet_directory();
    $theme_uri  = get_stylesheet_directory_uri();
    $header_js_path = $theme_dir . '/assets/js/header.js';

    if ( file_exists( $header_js_path ) ) {
        wp_enqueue_script( 'theme-header', $theme_uri . '/assets/js/header.js', array(), filemtime( $header_js_path ), true );
    }

    $drawer_css_path = $theme_dir . '/assets/css/drawer-menu.css';
    if ( file_exists( $drawer_css_path ) ) {
        wp_enqueue_style( 'theme-drawer-menu', $theme_uri . '/assets/css/drawer-menu.css', array(), filemtime( $drawer_css_path ) );
    }
    $drawer_js_path = $theme_dir . '/assets/js/drawer-menu.js';
    if ( file_exists( $drawer_js_path ) ) {
        wp_enqueue_script( 'theme-drawer-menu', $theme_uri . '/assets/js/drawer-menu.js', array(), filemtime( $drawer_js_path ), true );
    }

    // Enqueue CTA CSS if present
    $cta_css_path = $theme_dir . '/assets/css/cta.css';
    if ( file_exists( $cta_css_path ) ) {
        wp_enqueue_style( 'theme-cta', $theme_uri . '/assets/css/cta.css', array(), filemtime( $cta_css_path ) );
    }

}
add_action( 'wp_enqueue_scripts', 'abcontact_enqueue_header_assets', 20 );

/* Optional header color toggles */
function abcontact_enqueue_header_color_switch() {
    if ( is_admin() ) {
        return;
    }
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    $css_rel = '/assets/css/header-colors.css';
    $js_rel  = '/assets/js/header-toggle.js';
    $css_path = $theme_dir . $css_rel;
    $js_path  = $theme_dir . $js_rel;

    $possible_main_handles = array( 'theme-main','main-style','theme-style','main-css','style','styles','app','bundle' );
    $dependency = array();
    foreach ( $possible_main_handles as $h ) {
        if ( wp_style_is( $h, 'registered' ) ) {
            $dependency = array( $h );
            break;
        }
    }

    if ( file_exists( $css_path ) ) {
        wp_enqueue_style( 'theme-header-colors', $theme_uri . $css_rel, $dependency, filemtime( $css_path ) );
    }
    if ( file_exists( $js_path ) ) {
        wp_enqueue_script( 'theme-header-toggle', $theme_uri . $js_rel, array(), filemtime( $js_path ), true );
    }
}
add_action( 'wp_enqueue_scripts', 'abcontact_enqueue_header_color_switch', 25 );

/* ======================== Accessibility helpers / menus / sidebars ======================== */
function abcontact_safe_menu_heading_handler( $atts, $item, $args ) {
    if ( is_admin() ) {
        return $atts;
    }
    $classes = (array) $item->classes;
    if ( ! in_array( 'menu-heading', $classes, true ) && ! in_array( 'column-title', $classes, true ) ) {
        return $atts;
    }

    $href = '';
    if ( isset( $item->url ) ) {
        $href = trim( (string) $item->url );
    } elseif ( isset( $atts['href'] ) ) {
        $href = trim( (string) $atts['href'] );
    }

    $is_placeholder = ( $href === '#' || $href === '' || stripos( $href, 'javascript:void(0)' ) !== false );

    if ( $is_placeholder ) {
        $atts['data-menu-heading'] = '1';
        $atts['aria-disabled']     = 'true';
        $atts['tabindex']          = '-1';
        $atts['role']              = 'presentation';
        if ( isset( $atts['class'] ) ) {
            if ( strpos( $atts['class'], 'menu-heading__anchor' ) === false ) {
                $atts['class'] = $atts['class'] . ' menu-heading__anchor';
            }
        } else {
            $atts['class'] = 'menu-heading__anchor';
        }
    } else {
        if ( isset( $atts['aria-disabled'] ) ) {
            unset( $atts['aria-disabled'] );
        }
        if ( isset( $atts['tabindex'] ) && $atts['tabindex'] === '-1' ) {
            unset( $atts['tabindex'] );
        }
        if ( isset( $atts['role'] ) && $atts['role'] === 'presentation' ) {
            unset( $atts['role'] );
        }
        if ( isset( $atts['data-menu-heading'] ) ) {
            unset( $atts['data-menu-heading'] );
        }
    }

    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'abcontact_safe_menu_heading_handler', 10, 3 );

function abcontact_register_menus() {
    register_nav_menus( array(
        'primary'         => __( 'Primary Menu', 'abcontact' ),
        'footer'          => __( 'Footer Links', 'abcontact' ),
        'footer_services' => __( 'Footer Services', 'abcontact' ),
        'social'          => __( 'Social Menu', 'abcontact' ),
    ) );
}
add_action( 'after_setup_theme', 'abcontact_register_menus' );

function abcontact_register_sidebars() {
    $sidebars = array(
        'footer-1' => __( 'Footer Column 1', 'abcontact' ),
        'footer-2' => __( 'Footer Column 2', 'abcontact' ),
        'footer-3' => __( 'Footer Column 3', 'abcontact' ),
    );
    foreach ( $sidebars as $id => $name ) {
        register_sidebar( array(
            'name'          => $name,
            'id'            => $id,
            'before_widget' => '<div id="%1$s" class="widget %2$s footer-widget">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
    }
}
add_action( 'widgets_init', 'abcontact_register_sidebars' );

/* ============================ Customizer: Footer settings ============================ */
function abcontact_customize_register( $wp_customize ) {
   
$wp_customize->add_setting( 'hero_primary_label', array(
    'default'           => __( 'Richiedi Consulenza', 'theme-abcontact' ),
    'sanitize_callback' => 'sanitize_text_field',
    'capability'        => 'manage_options',
) );

$wp_customize->add_control( 'hero_primary_label', array(
    'label'    => __( 'Hero - Testo bottone principale', 'theme-abcontact' ),
    'section'  => 'abcontact_footer_section', 
    'type'     => 'text',
) );

$wp_customize->add_setting( 'hero_primary_link', array(
    'default'           => home_url( '/contatti' ),
    'sanitize_callback' => 'esc_url_raw',
    'capability'        => 'manage_options',
) );

$wp_customize->add_control( 'hero_primary_link', array(
    'label'    => __( 'Hero - Link bottone principale', 'theme-abcontact' ),
    'section'  => 'abcontact_footer_section',
    'type'     => 'url',
) );
   
// Header CTA: label e link
$wp_customize->add_setting( 'header_cta_label', array(
    'default'           => __( 'Contattaci', 'theme-abcontact' ),
    'sanitize_callback' => 'sanitize_text_field',
    'capability'        => 'manage_options',
) );

$wp_customize->add_control( 'header_cta_label', array(
    'label'    => __( 'Testo pulsante header', 'theme-abcontact' ),
    'section'  => 'abcontact_footer_section', 
    'type'     => 'text',
) );

$wp_customize->add_setting( 'header_cta_link', array(
    'default'           => home_url( '/contatti' ),
    'sanitize_callback' => 'esc_url_raw',
    'capability'        => 'manage_options',
) );

$wp_customize->add_control( 'header_cta_link', array(
    'label'    => __( 'Link pulsante header', 'theme-abcontact' ),
    'section'  => 'abcontact_footer_section',
    'type'     => 'url',
) );
    
// Impostazione e controllo per la email di ricezione richieste bolletta
$wp_customize->add_setting( 'abcontact_recipient_email', array(
    'default'           => '',
    'sanitize_callback' => 'sanitize_email',
    'capability'        => 'manage_options',
    'type'              => 'option', 
) );

$wp_customize->add_control( 'abcontact_recipient_email', array(
    'label'    => __( 'Email ricezione richieste bolletta', 'abcontact' ),
    'section'  => 'abcontact_footer_section', 
    'type'     => 'email',
) );

    $wp_customize->add_section( 'abcontact_footer_section', array(
        'title'       => __( 'Footer settings', 'abcontact' ),
        'priority'    => 160,
        'description' => __( 'Contenuti e contatti nel footer', 'abcontact' ),
    ) );

    $wp_customize->add_setting( 'footer_about_text', array(
        'default'           => __( 'Testo breve con slogan o call to action inerente ai nostri servizi e/o obiettivi', 'abcontact' ),
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'footer_about_text', array(
        'label'    => __( 'About (testo)', 'abcontact' ),
        'section'  => 'abcontact_footer_section',
        'type'     => 'textarea',
    ) );

    $wp_customize->add_setting( 'footer_email', array(
        'default'           => 'info@example.com',
        'sanitize_callback' => 'sanitize_email',
    ) );
    $wp_customize->add_control( 'footer_email', array(
        'label'    => __( 'E-mail di contatto', 'abcontact' ),
        'section'  => 'abcontact_footer_section',
        'type'     => 'email',
    ) );

    $wp_customize->add_setting( 'footer_phone', array(
        'default'           => '+39 000 000 0000',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'footer_phone', array(
        'label'    => __( 'Telefono', 'abcontact' ),
        'section'  => 'abcontact_footer_section',
        'type'     => 'text',
    ) );

    $wp_customize->add_setting( 'footer_address', array(
        'default'           => __( 'Indirizzo, città', 'abcontact' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'footer_address', array(
        'label'    => __( 'Indirizzo', 'abcontact' ),
        'section'  => 'abcontact_footer_section',
        'type'     => 'text',
    ) );

    $wp_customize->add_setting( 'footer_copyright', array(
        'default'           => sprintf( '&copy; %1$s %2$s. %3$s', date_i18n( 'Y' ), get_bloginfo( 'name' ), __( 'Tutti i diritti riservati.', 'abcontact' ) ),
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'footer_copyright', array(
        'label'    => __( 'Copyright (HTML allowed)', 'abcontact' ),
        'section'  => 'abcontact_footer_section',
        'type'     => 'textarea',
    ) );

    // Social icons
    for ( $i = 1; $i <= 4; $i++ ) {
        $wp_customize->add_setting( "footer_social_{$i}_image", array(
            'default'           => '',
            'sanitize_callback' => 'absint',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );
        $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, "footer_social_{$i}_image", array(
            'label'    => sprintf( __( 'Social %d - Icona (carica immagine)', 'abcontact' ), $i ),
            'section'  => 'abcontact_footer_section',
            'settings' => "footer_social_{$i}_image",
            'mime_type' => 'image',
        ) ) );

        $wp_customize->add_setting( "footer_social_{$i}_url", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );
        $wp_customize->add_control( "footer_social_{$i}_url", array(
            'label'    => sprintf( __( 'Social %d - Link', 'abcontact' ), $i ),
            'section'  => 'abcontact_footer_section',
            'type'     => 'url',
        ) );
    }

    // Footer logo
    $wp_customize->add_setting( 'footer_logo', array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'footer_logo', array(
        'label'       => __( 'Footer Logo', 'abcontact' ),
        'section'     => 'abcontact_footer_section',
        'settings'    => 'footer_logo',
        'mime_type'   => 'image',
        'description' => __( 'Carica un logo da usare solo nel footer. Se vuoto verrà usato il logo principale del sito.', 'abcontact' ),
    ) ) );

}
add_action( 'customize_register', 'abcontact_customize_register' );

function abcontact_print_hero_custom_css_vars() {
    if ( is_admin() ) {
        return;
    }

    $size   = get_theme_mod( 'hero_subtitle_size', '1.05rem' );
    $weight = (int) get_theme_mod( 'hero_subtitle_weight', 700 );

    $size_esc = esc_attr( $size );
    $weight_esc = esc_attr( $weight );

    echo "<style id=\"abcontact-hero-custom-css\" type=\"text/css\">:root{ --hero-subtitle-size: {$size_esc}; --hero-subtitle-weight: {$weight_esc}; }</style>\n";
}
add_action( 'wp_head', 'abcontact_print_hero_custom_css_vars', 5 );

add_action( 'customize_register', 'abcontact_customize_register' );

add_action( 'admin_init', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( get_option( 'abcontact_migrated_footer_images' ) ) {
        return;
    }

    $theme = get_stylesheet();
    $option = 'theme_mods_' . $theme;
    $mods = get_option( $option, array() );
    $updated = false;

    if ( ! empty( $mods['footer_logo'] ) && ! is_numeric( $mods['footer_logo'] ) ) {
        $id = attachment_url_to_postid( $mods['footer_logo'] );
        if ( $id ) { $mods['footer_logo'] = (int) $id; $updated = true; }
    }

    for ( $i = 1; $i <= 4; $i++ ) {
        $key = "footer_social_{$i}_image";
        if ( ! empty( $mods[ $key ] ) && ! is_numeric( $mods[ $key ] ) ) {
            $id = attachment_url_to_postid( $mods[ $key ] );
            if ( $id ) { $mods[ $key ] = (int) $id; $updated = true; }
        }
    }

    if ( $updated ) {
        update_option( $option, $mods );
        update_option( 'abcontact_migrated_footer_images', 1 );
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>abcontact: migrazione immagini footer completata (URL -> attachment ID).</p></div>';
        } );
    } else {
        update_option( 'abcontact_migrated_footer_images', 1 );
    }
}, 20 );

/* ================== Admin enqueue (for media picker script) ================== */

/* NOTE:
   The "Chi Siamo" grouped metabox (render / save / admin enqueue) has been moved
   to inc/chi-siamo-admin.php. This keeps functions.php lean and avoids duplicate
   declarations. Ensure the file inc/chi-siamo-admin.php exists (we provided it).
*/

/* ===================== Remaining admin / frontend code is loaded from inc/ files ===================== */

?>