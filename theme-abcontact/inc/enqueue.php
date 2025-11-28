<?php
if ( ! function_exists( 'abcontact_enqueue_assets' ) ) {
    function abcontact_enqueue_assets() {
        $theme_version = wp_get_theme()->get( 'Version' );

        $css_file = get_stylesheet_directory() . '/assets/css/main.css';
        if ( file_exists( $css_file ) ) {
            wp_enqueue_style(
                'abcontact-main',
                get_stylesheet_directory_uri() . '/assets/css/main.css',
                array(),
                filemtime( $css_file )
            );
        } else {
            wp_enqueue_style( 'abcontact-style', get_stylesheet_uri(), array(), $theme_version );
        }

        // Components CSS (buttons, media-figure)
        $components_css = get_stylesheet_directory() . '/assets/css/components.css';
        if ( file_exists( $components_css ) ) {
            wp_enqueue_style(
                'abcontact-components',
                get_stylesheet_directory_uri() . '/assets/css/components.css',
                array( 'abcontact-main' ),
                filemtime( $components_css )
            );
        }

/* =============================== CTA CSS =============================== */
        $cta_css = get_stylesheet_directory() . '/assets/css/cta.css';
        if ( file_exists( $cta_css ) ) {
            wp_enqueue_style(
                'abcontact-cta',
                get_stylesheet_directory_uri() . '/assets/css/cta.css',
                array( 'abcontact-main' ),
                filemtime( $cta_css )
            );
        }

/* ========================== Footer-specifics css ========================== */
        $footer_css = get_stylesheet_directory() . '/assets/css/footer.css';
        if ( file_exists( $footer_css ) ) {
            wp_enqueue_style(
                'abcontact-footer',
                get_stylesheet_directory_uri() . '/assets/css/footer.css',
                array( 'abcontact-main' ),
                filemtime( $footer_css )
            );
        }

/* ========================== News-specifics css ========================== */
        $news_css = get_stylesheet_directory() . '/assets/css/news.css';
        if ( file_exists( $news_css ) ) {
            wp_enqueue_style(
                'abcontact-news',
                get_stylesheet_directory_uri() . '/assets/css/news.css',
                array( 'abcontact-main' ),
                filemtime( $news_css )
            );
        }

/* ========================== News archive css ========================== */
        $news_archive_css = get_stylesheet_directory() . '/assets/css/news-archive.css';
        if ( file_exists( $news_archive_css ) ) {
            wp_enqueue_style(
                'abcontact-news-archive',
                get_stylesheet_directory_uri() . '/assets/css/news-archive.css',
                array( 'abcontact-main' ),
                filemtime( $news_archive_css )
            );
        }

        $js_file = get_stylesheet_directory() . '/assets/js/main.js';
        if ( file_exists( $js_file ) ) {
            wp_enqueue_script(
                'abcontact-main',
                get_stylesheet_directory_uri() . '/assets/js/main.js',
                array(),
                filemtime( $js_file ),
                true
            );
        }

        // Components JS
        $components_js = get_stylesheet_directory() . '/assets/js/components.js';
        if ( file_exists( $components_js ) ) {
            wp_enqueue_script(
                'abcontact-components',
                get_stylesheet_directory_uri() . '/assets/js/components.js',
                array(),
                filemtime( $components_js ),
                true
            );
        }

/* =============================== CTA js =============================== */
        $cta_js = get_stylesheet_directory() . '/assets/js/cta.js';
        if ( file_exists( $cta_js ) ) {
            wp_enqueue_script(
                'abcontact-cta',
                get_stylesheet_directory_uri() . '/assets/js/cta.js',
                array(),
                filemtime( $cta_js ),
                true
            );
        }

/* ============================ News archive js ============================ */
        $news_archive_js = get_stylesheet_directory() . '/assets/js/news-archive.js';
        if ( file_exists( $news_archive_js ) ) {
            wp_enqueue_script(
                'abcontact-news-archive',
                get_stylesheet_directory_uri() . '/assets/js/news-archive.js',
                array(),
                filemtime( $news_archive_js ),
                true
            );
            wp_localize_script( 'abcontact-news-archive', 'abcontactNews', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'abcontact_news_load_more' ),
                'posts_per_page' => 6,
                'load_more_label' => __( 'Carica altri articoli', 'theme-abcontact' ),
            ) );
        }

/* ================================ Footer js ================================ */
        $footer_js = get_stylesheet_directory() . '/assets/js/footer.js';
        if ( file_exists( $footer_js ) ) {
            wp_enqueue_script(
                'abcontact-footer',
                get_stylesheet_directory_uri() . '/assets/js/footer.js',
                array(),
                filemtime( $footer_js ),
                true
            );
        }

/* ========================== Optional News js ========================== */
        $news_js = get_stylesheet_directory() . '/assets/js/news.js';
        if ( file_exists( $news_js ) ) {
            wp_enqueue_script(
                'abcontact-news',
                get_stylesheet_directory_uri() . '/assets/js/news.js',
                array(),
                filemtime( $news_js ),
                true
            );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'abcontact_enqueue_assets' );

/* ========================== Enqueue Shared Hero css ========================== */
function abcontact_enqueue_hero_shared_css() {
    $file = get_stylesheet_directory() . '/assets/css/hero-shared.css';
    if ( file_exists( $file ) && ! wp_style_is( 'hero-shared', 'registered' ) ) {
        wp_register_style( 'hero-shared', get_stylesheet_directory_uri() . '/assets/css/hero-shared.css', array(), filemtime( $file ) );
    }
    if ( file_exists( $file ) && ! wp_style_is( 'hero-shared', 'enqueued' ) ) {
        wp_enqueue_style( 'hero-shared' );
    }
}
add_action( 'wp_enqueue_scripts', 'abcontact_enqueue_hero_shared_css', 16 );

/* ========================== Common CSS (all pages) ========================== */
function abcontact_enqueue_common_css() {
    $common_css = get_stylesheet_directory() . '/assets/css/common.css';
    if ( file_exists( $common_css ) ) {
        wp_enqueue_style(
            'abcontact-common',
            get_stylesheet_directory_uri() . '/assets/css/common.css',
            array( 'abcontact-main' ),
            filemtime( $common_css )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'abcontact_enqueue_common_css', 12 );

/* ========================== Common JS (all pages) ========================== */
function abcontact_enqueue_common_js() {
    $common_js = get_stylesheet_directory() . '/assets/js/common.js';
    if ( file_exists( $common_js ) ) {
        wp_enqueue_script(
            'abcontact-common',
            get_stylesheet_directory_uri() . '/assets/js/common.js',
            array(),
            filemtime( $common_js ),
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'abcontact_enqueue_common_js', 12 );

/* ========================== Hero Home CSS (front-page only) ========================== */
function abcontact_enqueue_hero_home_css() {
    if ( ! is_front_page() ) {
        return;
    }
    $hero_home_css = get_stylesheet_directory() . '/assets/css/hero-home.css';
    if ( file_exists( $hero_home_css ) ) {
        wp_enqueue_style(
            'abcontact-hero-home',
            get_stylesheet_directory_uri() . '/assets/css/hero-home.css',
            array( 'abcontact-main' ),
            filemtime( $hero_home_css )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'abcontact_enqueue_hero_home_css', 17 );

/* ========================== Hero CSS (non-front-page pages) ========================== */
function abcontact_enqueue_hero_css() {
    if ( is_front_page() ) {
        return;
    }
    $hero_css = get_stylesheet_directory() . '/assets/css/hero.css';
    if ( file_exists( $hero_css ) ) {
        wp_enqueue_style(
            'abcontact-hero',
            get_stylesheet_directory_uri() . '/assets/css/hero.css',
            array( 'abcontact-main' ),
            filemtime( $hero_css )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'abcontact_enqueue_hero_css', 17 );

/* ========================== Front Page CSS (front-page only) ========================== */
function abcontact_enqueue_front_page_css() {
    if ( ! is_front_page() ) {
        return;
    }
    $front_page_css = get_stylesheet_directory() . '/assets/css/front-page.css';
    if ( file_exists( $front_page_css ) ) {
        wp_enqueue_style(
            'abcontact-front-page',
            get_stylesheet_directory_uri() . '/assets/css/front-page.css',
            array( 'abcontact-main' ),
            filemtime( $front_page_css )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'abcontact_enqueue_front_page_css', 18 );