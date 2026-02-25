<?php
/**
 * Template Name: News
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<?php
// Hero partial
get_template_part( 'template-parts/news-hero' );
?>

<main id="site-main" class="site-main">
  <div class="container">

    <?php
    // Breadcrumb (simple)
    echo '<nav class="breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'theme-abcontact' ) . '">';
    echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'theme-abcontact' ) . '</a> &raquo; ';
    echo '<span>' . esc_html__( 'News & Aggiornamenti', 'theme-abcontact' ) . '</span>';
    echo '</nav>';
    ?>

    <?php
    // Featured (latest post)
    $featured_args = array(
      'post_type'           => 'post',
      'posts_per_page'      => 1,
      'post_status'         => 'publish',
      'ignore_sticky_posts' => true,
    );
    $featured_q = new WP_Query( $featured_args );
    $featured_post_id = 0;

    if ( $featured_q->have_posts() ) {
        if ( isset( $featured_q->posts[0] ) ) {
            $featured_post_id = (int) $featured_q->posts[0]->ID;
        }

        $featured_q->the_post();
        set_query_var( 'abcontact_featured_post', get_post() );
        get_template_part( 'template-parts/news-featured' );
        wp_reset_postdata();
    }
    ?>

    <!-- category/search filters -->
    <section class="news-filters" aria-label="<?php esc_attr_e( 'Filtri news', 'theme-abcontact' ); ?>">
      <div class="news-filters-panel">
        <div class="news-filters__search-row">
          <label class="screen-reader-text" for="news-filter-search"><?php esc_html_e( 'Cerca articoli', 'theme-abcontact' ); ?></label>
          <input
            id="news-filter-search"
            class="news-filter-search"
            type="search"
            placeholder="<?php esc_attr_e( 'Cerca per titolo o contenuto', 'theme-abcontact' ); ?>"
            autocomplete="off"
          />
          <button id="news-filter-apply" class="button news-filter-apply" type="button">
            <?php esc_html_e( 'Applica filtri', 'theme-abcontact' ); ?>
          </button>
        </div>

        <div class="news-filters-cats-wrap">
          <button
            id="news-filters-toggle"
            class="news-filters-toggle"
            type="button"
            aria-expanded="false"
            aria-controls="news-filters-cats-panel"
          >
            <?php esc_html_e( 'Seleziona categorie', 'theme-abcontact' ); ?>
          </button>

          <div id="news-filters-cats-panel" class="news-filters-cats-panel">
            <div class="news-filters__actions">
              <button id="news-filter-clear" class="news-filter-clear" type="button">
                <?php esc_html_e( 'Azzera', 'theme-abcontact' ); ?>
              </button>
              <p class="news-filters__hint"><?php esc_html_e( 'Seleziona una o piu categorie', 'theme-abcontact' ); ?></p>
            </div>

            <?php
            $cats = get_categories( array( 'hide_empty' => true ) );
            if ( ! empty( $cats ) ) :
                echo '<div class="news-filter-list" role="group" aria-label="' . esc_attr__( 'Categorie', 'theme-abcontact' ) . '">';
                foreach ( $cats as $c ) {
                    printf(
                        '<button class="news-filter-chip" type="button" data-cat="%1$s">%2$s</button>',
                        esc_attr( $c->term_id ),
                        esc_html( $c->name )
                    );
                }
                echo '</div>';
            endif;
            ?>
          </div>
        </div>
      </div>
    </section>

    <!-- Grid container: initial load (page 1) -->
    <section class="news-archive">
      <div id="news-archive-grid" class="news-archive__grid">
        <?php
        $paged = 1;
        $posts_per_page = 6;
        $args = array(
          'post_type'           => 'post',
          'posts_per_page'      => $posts_per_page,
          'post_status'         => 'publish',
          'ignore_sticky_posts' => true,
          'post__not_in'        => $featured_post_id ? array( $featured_post_id ) : array(),
          'paged'               => $paged,
        );

        $grid_q = new WP_Query( $args );
        if ( $grid_q->have_posts() ) {
            while ( $grid_q->have_posts() ) {
                $grid_q->the_post();
                get_template_part( 'template-parts/news-card' );
            }
            wp_reset_postdata();
        }
        ?>
      </div>

      <div class="news-archive__more" style="text-align:center; margin-top:28px;">
        <button id="news-load-more" class="button" data-page="1" data-exclude="<?php echo esc_attr( $featured_post_id ); ?>">
          <?php esc_html_e( 'Carica altri articoli', 'theme-abcontact' ); ?>
        </button>
      </div>
    </section>

  </div><!-- .container -->
</main>

<?php
get_footer();
