<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
  <?php if ( has_post_thumbnail() ) : ?>
    <a class="card__thumb" href="<?php the_permalink(); ?>">
      <?php the_post_thumbnail( 'home-thumb', array( 'class' => 'card__thumb-img', 'loading' => 'lazy' ) ); ?>
    </a>
  <?php endif; ?>

  <div class="card__body">
    <header class="card__header">
      <h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
      <div class="entry-meta card__meta">
        <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
        <span class="meta-sep">•</span>
        <span class="byline"><?php the_author(); ?></span>
      </div>
    </header>

    <div class="card__excerpt">
      <?php the_excerpt(); ?>
    </div>

    <footer class="card__footer">
      <a class="button button--ghost" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Leggi', 'theme-abcontact' ); ?></a>
    </footer>
  </div>
</article>