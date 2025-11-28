<?php
/**
 * Archive Template
 *
 * Template for displaying archive pages with modular hero.
 *
 * @package theme-abcontact
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

// Include hero for archive pages
get_template_part( 'parts/hero', null, array(
    'title'    => get_the_archive_title(),
    'subtitle' => get_the_archive_description(),
) );

if ( have_posts() ) :
    echo '<div class="archive-content container">';
    echo '<div class="grid">';
    
    while ( have_posts() ) : the_post();
        get_template_part( 'template-parts/content', get_post_type() );
    endwhile;
    
    echo '</div>';
    
    the_posts_pagination( array(
        'mid_size'  => 2,
        'prev_text' => __( '&laquo; Precedente', 'theme-abcontact' ),
        'next_text' => __( 'Successivo &raquo;', 'theme-abcontact' ),
    ) );
    
    echo '</div>';
else :
    get_template_part( 'template-parts/content', 'none' );
endif;

get_footer();
