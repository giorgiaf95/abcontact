<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function abcontact_send_posts_html( $query ) {
    $html = '';
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            ob_start();
            get_template_part( 'template-parts/news-card' );
            $html .= ob_get_clean();
        }
        wp_reset_postdata();
    }
    return $html;
}

function abcontact_ajax_load_more_news() {
    check_ajax_referer( 'abcontact_news_load_more', 'nonce' );

    $paged = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
    $cat = isset( $_POST['cat'] ) ? intval( $_POST['cat'] ) : 0;
    $exclude = isset( $_POST['exclude'] ) ? intval( $_POST['exclude'] ) : 0;
    $posts_per_page = 6;

    $args = array(
        'post_type'           => 'post',
        'posts_per_page'      => $posts_per_page,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'paged'               => $paged,
    );
    if ( $cat ) {
        $args['cat'] = $cat;
    }
    if ( $exclude ) {
        $args['post__not_in'] = array( $exclude );
    }

    $q = new WP_Query( $args );

    $html = abcontact_send_posts_html( $q );

    wp_send_json_success( array(
        'html' => $html,
        'max_pages' => (int) $q->max_num_pages,
    ) );
}
add_action( 'wp_ajax_abcontact_load_more_news', 'abcontact_ajax_load_more_news' );
add_action( 'wp_ajax_nopriv_abcontact_load_more_news', 'abcontact_ajax_load_more_news' );