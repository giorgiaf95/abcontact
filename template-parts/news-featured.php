<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$featured_post = get_query_var( 'abcontact_featured_post' );
if ( ! $featured_post ) {
    return;
}

$post = $featured_post;
setup_postdata( $post );
?>
<article class="featured-article card" aria-labelledby="featured-title">
  <div class="featured-inner container">
    <?php
    $thumb_id  = get_post_thumbnail_id( $post );
    $thumb_alt = '';
    if ( $thumb_id ) {
        $thumb_alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
        if ( ! $thumb_alt ) {
            $thumb_alt = get_the_title( $post );
        }
    }
    ?>
    <div class="featured-thumb<?php echo $thumb_id ? ' has-thumb' : ' no-thumb'; ?>">
      <?php
      if ( $thumb_id ) {
          echo wp_get_attachment_image( $thumb_id, 'news-large', false, array(
              'class'    => 'card__thumb-img featured-thumb__img wp-post-image',
              'alt'      => esc_attr( $thumb_alt ),
              'loading'  => 'lazy',
              'decoding' => 'async',
          ) );
      } else {
          echo '<div class="featured-thumb__placeholder" aria-hidden="true"></div>';
      }
      ?>
    </div>

    <div class="featured-body">
      <?php
      $cats = get_the_category( $post->ID );
      if ( ! empty( $cats ) ) {
          $cat = $cats[0];
          $cat_color = get_term_meta( $cat->term_id, 'category_color', true );
          $cat_text = $cat_color ? abcontact_get_contrast_color( $cat_color ) : '';
          $style = $cat_color ? 'style="background:' . esc_attr( $cat_color ) . '; color:' . esc_attr( $cat_text ) . ';"' : '';
          echo '<span class="news-label news-label--featured" ' . $style . '>' . esc_html( $cat->name ) . '</span>';
      }
      ?>
      <h2 id="featured-title" class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

      <div class="entry-content">
        <?php
        $manual_excerpt = get_the_excerpt();
        $trimmed = wp_trim_words( wp_strip_all_tags( $manual_excerpt ), 18, '...' );
        echo wp_kses_post( wpautop( $trimmed ) );
        ?>
      </div>

      <div class="entry-meta">
        <time class="meta-date" datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>">
          <?php echo esc_html( get_the_date( '', $post ) ); ?>
        </time>

        <span class="news-meta__separator" aria-hidden="true">•</span>

        <span class="meta-reading">
          <?php echo esc_html( abcontact_get_reading_time( $post->ID ) ); ?> <?php esc_html_e( 'min', 'theme-abcontact' ); ?>
        </span>
      </div>

      <div class="featured-cta">
        <a class="button button--ghost" href="<?php the_permalink(); ?>"><?php esc_html_e( "Leggi l'Articolo", 'theme-abcontact' ); ?></a>
      </div>
    </div>
  </div>
</article>

<?php
wp_reset_postdata();
?>