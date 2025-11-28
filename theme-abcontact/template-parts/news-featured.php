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
  <div class="featured-inner container" style="display:flex; gap:28px; align-items:stretch; box-sizing:border-box;">
    <div class="featured-thumb" style="flex: 1 1 50%; overflow:hidden; border-top-left-radius:12px; border-bottom-left-radius:12px;">
      <?php
      if ( has_post_thumbnail( $post ) ) {
          echo get_the_post_thumbnail( $post, 'news-large', array( 'class' => 'card__thumb-img featured-thumb__img', 'loading' => 'lazy', 'alt' => esc_attr( get_the_title( $post ) ) ) );
      } else {
          echo '<div class="card__thumb-img featured-thumb__img" aria-hidden="true"></div>';
      }
      ?>
    </div>

    <div class="featured-body" style="flex:1 1 50%; display:flex; flex-direction:column; justify-content:center; align-items:flex-start; padding: 28px 32px 28px 24px; box-sizing:border-box;">
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
      <h2 id="featured-title" class="entry-title" style="margin:8px 0 12px;"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

      <div class="entry-content" style="color:var(--color-muted);">
        <?php the_excerpt(); ?>
      </div>

      <div class="entry-meta" style="margin-top:18px; display:flex; gap:14px; align-items:center; color:var(--color-muted);">
        <span class="meta-author" aria-hidden="true">
          <!-- optional author icon (hidden if you don't want author shown) -->
        </span>

        <time class="meta-date" datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>">
          <!-- calendar icon -->
          <svg class="meta-icon" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false" style="vertical-align:middle; margin-right:8px;"><path fill="currentColor" d="M7 10h5v5H7z" opacity="0.9"></path><path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v13c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 15H5V9h14v10z"></path></svg>
          <?php echo esc_html( get_the_date( '', $post ) ); ?>
        </time>

        <span class="news-meta__separator" aria-hidden="true">•</span>

        <span class="meta-reading">
          <!-- clock icon -->
          <svg class="meta-icon" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false" style="vertical-align:middle; margin-right:8px;"><path fill="currentColor" d="M12 1a11 11 0 1011 11A11.013 11.013 0 0012 1zm0 20a9 9 0 119-9 9.01 9.01 0 01-9 9zm.5-13H13v6l5 2-.5.87L12.5 13V8z"></path></svg>
          <?php echo esc_html( abcontact_get_reading_time( $post->ID ) ); ?> <?php esc_html_e( 'min', 'theme-abcontact' ); ?>
        </span>
      </div>

      <div style="margin-top:22px;">
        <a class="button button--ghost" href="<?php the_permalink(); ?>"><?php esc_html_e( "Leggi l'Articolo", 'theme-abcontact' ); ?></a>
      </div>
    </div>
  </div>
</article>
<?php
wp_reset_postdata();