<?php
/**
 * Page Template
 *
 * Template for displaying single pages with modular hero.
 *
 * @package theme-abcontact
 */

get_header();

// Include hero for pages (skipped on front page)
get_template_part( 'parts/hero' );

while ( have_posts() ) : the_post();
    get_template_part( 'template-parts/content', 'page' );
endwhile;
get_footer();