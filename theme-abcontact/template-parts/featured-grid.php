<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$limit = 4; // default (1 + 3)
$args = array(
    'post_type'           => 'post',
    'posts_per_page'      => $limit,
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
);

$query = new WP_Query( $args );

if ( ! $query->have_posts() ) {
    wp_reset_postdata();
    return;
}

$posts = $query->posts;
?>
<section id="featured-news" class="featured-news container" aria-label="<?php esc_attr_e( 'News & Aggiornamenti', 'theme-abcontact' ); ?>">
  <header class="section-header">
    <h2 class="section-title"><?php esc_html_e( 'News & Aggiornamenti', 'theme-abcontact' ); ?></h2>
    <p class="section-lead"><?php esc_html_e( 'Resta aggiornato sulle ultime novità del settore energetico', 'theme-abcontact' ); ?></p>
  </header>

  <div class="news-grid news-grid--equal-rows">
    <?php
    $first = array_shift( $posts );

    if ( $first ) :
        $post = $first;
        setup_postdata( $post );
    ?>
      <article class="news-card news-card--large" id="post-<?php echo esc_attr( $post->ID ); ?>" role="article" aria-labelledby="news-title-<?php echo esc_attr( $post->ID ); ?>">
        <a class="news-thumb-link" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
          <?php
          if ( has_post_thumbnail( $post ) ) {
              echo get_the_post_thumbnail( $post, 'news-large', array( 'class' => 'news-card__thumb news-card__thumb--large', 'loading' => 'lazy', 'alt' => esc_attr( get_the_title( $post ) ) ) );
          } else {
              echo '<div class="news-card__thumb news-card__thumb--large" aria-hidden="true"></div>';
          }
          ?>
        </a>

        <div class="news-card__body">
          <?php
          // category label
          $cats = get_the_category( $post->ID );
          if ( ! empty( $cats ) ) {
              $cat = $cats[0];
              $cat_color = get_term_meta( $cat->term_id, 'category_color', true );
              if ( $cat_color ) {
                  $cat_text_color = abcontact_get_contrast_color( $cat_color );
                  $label_style = 'style="background:' . esc_attr( $cat_color ) . '; color:' . esc_attr( $cat_text_color ) . ';"';
              } else {
                  $label_style = '';
              }
              echo '<span class="news-label" ' . $label_style . '>' . esc_html( $cat->name ) . '</span>';
          }

          // title
          echo '<h3 id="news-title-' . esc_attr( $post->ID ) . '" class="news-card__title"><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></h3>';

          // excerpt
          $excerpt = get_post_field( 'post_excerpt', $post );
          if ( empty( $excerpt ) ) {
              $excerpt = wp_trim_words( wp_strip_all_tags( $post->post_content ), 32 );
          }
          echo '<div class="news-card__excerpt">' . wp_kses_post( wpautop( $excerpt ) ) . '</div>';

          $date = get_the_date( '', $post );
          $rt = abcontact_get_reading_time( $post->ID );
          ?>
          <div class="news-card__meta">
            <time class="news-meta__date" datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>">
              <svg class="meta-icon" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M7 10h5v5H7z" opacity="0.9"></path><path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v13c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 15H5V9h14v10z"></path></svg>
              <?php echo esc_html( $date ); ?>
            </time>

            <span class="news-meta__separator">•</span>

            <span class="news-meta__reading">
              <svg class="meta-icon" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 1a11 11 0 1011 11A11.013 11.013 0 0012 1zm0 20a9 9 0 119-9 9.01 9.01 0 01-9 9zm.5-13H13v6l5 2-.5.87L12.5 13V8z"></path></svg>
              <?php echo esc_html( $rt ); ?> <?php esc_html_e( 'min', 'theme-abcontact' ); ?>
            </span>
          </div>

          <div class="news-card__cta">
            <a class="button button--ghost" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
              <?php esc_html_e( "Leggi l'Articolo", 'theme-abcontact' ); ?> <span aria-hidden="true">→</span>
            </a>
          </div>
        </div>
      </article>
    <?php
    endif;
    ?>

    <div class="news-grid__right">
      <?php
      if ( ! empty( $posts ) ) {
          foreach ( $posts as $p ) {
              $post = $p;
              setup_postdata( $post );

              $small_excerpt = get_post_field( 'post_excerpt', $post );
              if ( empty( $small_excerpt ) ) {
                  $small_excerpt = wp_trim_words( wp_strip_all_tags( $post->post_content ), 18 );
              }

              $thumb_url = '';
              $srcset = '';
              $sizes_attr = '';
              if ( has_post_thumbnail( $post ) ) {
                  $thumb_id = get_post_thumbnail_id( $post->ID );
                  $thumb_url = wp_get_attachment_image_url( $thumb_id, 'news-thumb' );
                  $srcset = wp_get_attachment_image_srcset( $thumb_id, 'news-thumb' );
                  $sizes_attr = wp_get_attachment_image_sizes( $thumb_id, 'news-thumb' );
              }
              ?>
<article class="news-card news-card--small" id="post-<?php echo esc_attr( $post->ID ); ?>" role="article" aria-labelledby="news-title-<?php echo esc_attr( $post->ID ); ?>">
  <a class="news-card__link" href="<?php echo esc_url( get_permalink( $post ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $post ) ); ?>">
    <div class="news-card__media" style="<?php echo $thumb_url ? 'background-image:url(' . esc_url( $thumb_url ) . '); background-size:cover; background-position:center;' : ''; ?>">
      <?php
      if ( $thumb_url ) {
          echo '<img src="' . esc_url( $thumb_url ) . '" ' .
               ( $srcset ? 'srcset="' . esc_attr( $srcset ) . '" ' : '' ) .
               ( $sizes_attr ? 'sizes="' . esc_attr( $sizes_attr ) . '" ' : '' ) .
               'class="news-card__thumb news-card__thumb--small" loading="lazy" alt="' . esc_attr( get_the_title( $post ) ) . '" />';
      } else {
          echo '<div class="news-card__thumb news-card__thumb--small" aria-hidden="true"></div>';
      }
      ?>
    </div>

    <div class="news-card__body">
      <?php
      $cats = get_the_category( $post->ID );
      if ( ! empty( $cats ) ) {
          $cat = $cats[0];
          $cat_color = get_term_meta( $cat->term_id, 'category_color', true );
          if ( $cat_color ) {
              $cat_text_color = abcontact_get_contrast_color( $cat_color );
              $label_style = 'style="background:' . esc_attr( $cat_color ) . '; color:' . esc_attr( $cat_text_color ) . ';"';
          } else {
              $label_style = '';
          }
          echo '<span class="news-label news-label--small" ' . $label_style . '>' . esc_html( $cat->name ) . '</span>';
      }
      ?>
      <h4 id="news-title-<?php echo esc_attr( $post->ID ); ?>" class="news-card__title"><?php echo esc_html( get_the_title( $post ) ); ?></h4>

      <div class="news-card__excerpt news-card__excerpt--small"><?php echo wp_kses_post( wpautop( $small_excerpt ) ); ?></div>

      <div class="news-card__meta">
        <span class="news-meta__date">
          <svg class="meta-icon" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M7 10h5v5H7z" opacity="0.9"></path><path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v13c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 15H5V9h14v10z"></path></svg>
          <?php echo esc_html( get_the_date( '', $post ) ); ?>
        </span>
        <span class="news-meta__separator">•</span>
        <span class="news-meta__reading">
          <svg class="meta-icon" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 1a11 11 0 1011 11A11.013 11.013 0 0012 1zm0 20a9 9 0 119-9 9.01 9.01 0 01-9 9zm.5-13H13v6l5 2-.5.87L12.5 13V8z"></path></svg>
          <?php echo esc_html( abcontact_get_reading_time( $post->ID ) ); ?> <?php esc_html_e( 'min', 'theme-abcontact' ); ?>
        </span>
      </div>
    </div>
  </a>
</article>
              <?php
          }
      }
      ?>
    </div><!-- .news-grid__right -->
  </div><!-- .news-grid -->

  <div class="featured-grid__more container" role="navigation" aria-label="<?php esc_attr_e( 'Link a tutte le news', 'theme-abcontact' ); ?>">
    <?php
    $news_page = get_page_by_path( 'news' );
    $news_url  = $news_page ? get_permalink( $news_page ) : home_url( '/news' );
    ?>
    <a href="<?php echo esc_url( $news_url ); ?>" class="button featured-more-button"><?php esc_html_e( 'Vedi Tutte le News', 'theme-abcontact' ); ?> →</a>
  </div>
</section>

<?php
wp_reset_postdata();