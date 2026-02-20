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

    <!-- category filter -->
    <div class="news-filters" style="margin:28px 0; text-align:center;">
      <?php
      $cats = get_categories( array( 'hide_empty' => true ) );
      echo '<div class="news-filter-list">';
      echo '<button class="news-filter-button active" data-cat="0">' . esc_html__( 'Tutte', 'theme-abcontact' ) . '</button>';
      foreach ( $cats as $c ) {
          printf(
              '<button class="news-filter-button" data-cat="%1$s">%2$s</button>',
              esc_attr( $c->term_id ),
              esc_html( $c->name )
          );
      }
      echo '</div>';
      ?>
    </div>

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