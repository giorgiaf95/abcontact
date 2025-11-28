<?php
get_header(); ?>
<section class="error-404 not-found">
    <header class="page-header">
        <h1 class="page-title"><?php esc_html_e( 'Page not available', 'theme-abcontact' ); ?></h1>
    </header>
    <div class="page-content">
        <p><?php esc_html_e( 'Sorry, but the page you were trying to view does not exist.', 'theme-abcontact' ); ?></p>
        <?php get_search_form(); ?>
    </div>
</section>
<?php get_footer(); ?>