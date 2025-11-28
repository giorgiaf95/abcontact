<?php
/**
 * Single Post Template
 *
 * Template for displaying single posts with modular hero.
 *
 * @package theme-abcontact
 */

get_header();

// Include hero for single posts
get_template_part( 'parts/hero' );

while ( have_posts() ) : the_post();
    get_template_part( 'template-parts/content', get_post_type() );
endwhile;
get_footer();