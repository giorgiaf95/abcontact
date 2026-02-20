<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =================== 01) Theme setup: support, menus, image sizes =================== */

if ( ! function_exists( 'abcontact_setup' ) ) {
    function abcontact_setup() {
        load_theme_textdomain( 'theme-abcontact', get_template_directory() . '/languages' );

        add_theme_support( 'title-tag' );

        add_theme_support( 'automatic-feed-links' );

        add_theme_support( 'post-thumbnails' );
        set_post_thumbnail_size( 1200, 675, true );

        add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

        add_theme_support( 'responsive-embeds' );

        // Custom logo
        add_theme_support( 'custom-logo', array(
            'height'      => 100,
            'width'       => 400,
            'flex-height' => true,
            'flex-width'  => true,
        ) );

        add_theme_support( 'editor-styles' );

        add_theme_support( 'align-wide' );

        // Register nav menus
        register_nav_menus( array(
            'primary' => __( 'Primary Menu', 'theme-abcontact' ),
            'footer'  => __( 'Footer Menu', 'theme-abcontact' ),
        ) );

        // Register image sizes
        add_image_size( 'home-thumb', 840, 420, true );      // home: 840x420, hard crop
        add_image_size( 'service-thumb', 520, 320, true );   // servizi: 520x320
        add_image_size( 'about-thumb', 1200, 600, true );    // chi siamo: 1200x600
    }
}
add_action( 'after_setup_theme', 'abcontact_setup' );

add_filter( 'image_size_names_choose', function( $sizes ) {
    return array_merge( $sizes, array(
        'home-thumb'    => __( 'Home thumbnail', 'theme-abcontact' ),
        'service-thumb' => __( 'Service thumbnail', 'theme-abcontact' ),
        'about-thumb'   => __( 'About thumbnail', 'theme-abcontact' ),
    ) );
} );

/* ===================== 02) Widget area (sidebar & footer & topbar) ===================== */

if ( ! function_exists( 'abcontact_widgets_init' ) ) {
    function abcontact_widgets_init() {
        register_sidebar( array(
            'name'          => __( 'Sidebar', 'theme-abcontact' ),
            'id'            => 'sidebar-1',
            'description'   => __( 'Primary sidebar that appears on posts and pages.', 'theme-abcontact' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ) );

        register_sidebar( array(
            'name'          => __( 'Topbar', 'theme-abcontact' ),
            'id'            => 'topbar-1',
            'description'   => __( 'Area topbar (piccoli link, tel, social)', 'theme-abcontact' ),
            'before_widget' => '<div id="%1$s" class="topbar-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<span class="screen-reader-text">',
            'after_title'   => '</span>',
        ) );

        register_sidebar( array(
            'name'          => __( 'Footer Column 1', 'theme-abcontact' ),
            'id'            => 'footer-1',
            'description'   => __( 'Footer column 1', 'theme-abcontact' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s footer-col footer-col-1">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );

        register_sidebar( array(
            'name'          => __( 'Footer Column 2', 'theme-abcontact' ),
            'id'            => 'footer-2',
            'description'   => __( 'Footer column 2', 'theme-abcontact' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s footer-col footer-col-2">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );

        register_sidebar( array(
            'name'          => __( 'Footer Column 3', 'theme-abcontact' ),
            'id'            => 'footer-3',
            'description'   => __( 'Footer column 3', 'theme-abcontact' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s footer-col footer-col-3">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
    }
}
add_action( 'widgets_init', 'abcontact_widgets_init' );

/* ================================ 03) Hero ================================ */

   add_action( 'customize_register', 'abcontact_customize_hero' );
function abcontact_customize_hero( $wp_customize ) {
    $wp_customize->add_section( 'abcontact_hero_section', array(
        'title'       => __( 'Hero', 'theme-abcontact' ),
        'priority'    => 30,
        'description' => __( 'Imposta l\'immagine di sfondo per l\'hero (homepage).', 'theme-abcontact' ),
    ) );

    $wp_customize->add_setting( 'abcontact_hero_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'abcontact_hero_image', array(
        'label'    => __( 'Hero image', 'theme-abcontact' ),
        'section'  => 'abcontact_hero_section',
        'settings' => 'abcontact_hero_image',
    ) ) );

    $wp_customize->add_setting( 'abcontact_hero_overlay_color', array(
        'default'           => 'rgba(6,68,179,0.36)',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'abcontact_hero_overlay_color', array(
        'label'    => __( 'Hero overlay (RGBA)', 'theme-abcontact' ),
        'section'  => 'abcontact_hero_section',
        'settings' => 'abcontact_hero_overlay_color',
        'type'     => 'text',
    ) ) );
}