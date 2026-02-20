<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* Single card (used in news grid) */
global $post;
if ( ! $post ) return;
?>
<article id="post-<?php echo esc_attr( $post->ID ); ?>" class="card news-card-grid" role="article" aria-labelledby="post-title-<?php echo esc_attr( $post->ID ); ?>">
  <a class="card__link" href="<?php the_permalink(); ?>">
    <div class="card__media">
      <?php
      if ( has_post_thumbnail( $post ) ) {
          echo get_the_post_thumbnail( $post, 'news-thumb', array( 'class' => 'card__thumb-img', 'loading' => 'lazy', 'alt' => esc_attr( get_the_title( $post ) ) ) );
      } else {
          echo '<div class="card__thumb-img" aria-hidden="true"></div>';
      }
      ?>
    </div>

    <div class="card__body">
      <?php
      $cats = get_the_category( $post->ID );
      if ( ! empty( $cats ) ) {
          $cat = $cats[0];
          $cat_color = get_term_meta( $cat->term_id, 'category_color', true );
          $cat_text = $cat_color ? abcontact_get_contrast_color( $cat_color ) : '';
          $style = $cat_color ? 'style="background:' . esc_attr( $cat_color ) . '; color:' . esc_attr( $cat_text ) . ';"' : '';
          echo '<span class="news-label news-label--small" ' . $style . '>' . esc_html( $cat->name ) . '</span>';
      }
      ?>
      <h3 id="post-title-<?php echo esc_attr( $post->ID ); ?>" class="card__title"><?php echo esc_html( get_the_title( $post ) ); ?></h3>

      <div class="card__excerpt">
        <?php
        $excerpt = get_post_field( 'post_excerpt', $post );
        if ( empty( $excerpt ) ) {
            $excerpt = wp_trim_words( wp_strip_all_tags( $post->post_content ), 18 );
        }
        echo wp_kses_post( wpautop( $excerpt ) );
        ?>
      </div>

      <div class="card__meta" aria-hidden="true" style="display:flex; gap:12px; align-items:center;">
        <time class="meta-date" datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>">
          <svg class="meta-icon" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false" style="vertical-align:middle; margin-right:6px;"><path fill="currentColor" d="M7 10h5v5H7z" opacity="0.9"></path><path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v13c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 15H5V9h14v10z"></path></svg>
          <?php echo esc_html( get_the_date( '', $post ) ); ?>
        </time>

        <span class="meta-sep">•</span>

        <span class="meta-reading">
          <svg class="meta-icon" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false" style="vertical-align:middle; margin-right:6px;"><path fill="currentColor" d="M12 1a11 11 0 1011 11A11.013 11.013 0 0012 1zm0 20a9 9 0 119-9 9.01 9.01 0 01-9 9zm.5-13H13v6l5 2-.5.87L12.5 13V8z"></path></svg>
          <?php echo esc_html( abcontact_get_reading_time( $post->ID ) ); ?> <?php esc_html_e( 'min', 'theme-abcontact' ); ?>
        </span>
      </div>

      <div class="card__cta" style="margin-top:12px;">
        <span class="read-more-link"><?php esc_html_e( 'Leggi di più', 'theme-abcontact' ); ?> →</span>
      </div>
    </div>
  </a>
</article>