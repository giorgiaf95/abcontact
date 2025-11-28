<?php
get_header();
if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        get_template_part( 'template-parts/content', get_post_type() );
    endwhile;

    the_posts_pagination( array(
        'mid_size'  => 2,
        'prev_text' => __( 'Back', 'theme-abcontact' ),
        'next_text' => __( 'Next', 'theme-abcontact' ),
    ) );
else :
    get_template_part( 'template-parts/content', 'none' );
endif;
get_footer();