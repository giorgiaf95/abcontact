<?php
/**
 * Single post template — custom layout:
 * - reuse template-parts/news-hero.php (featured image as hero)
 * - below hero: back to news button, categories (styled as in admin), title, date and manual reading time
 * - article content
 * - related news previews (uses template-parts/news-card.php)
 * - CTA partial (template-parts/cta.php)
 * - footer (existing)
 *
 * Replace theme's single.php with this file.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

/* Helper: try several meta keys for manual reading time (author writes it into a custom field) */
function abcontact_get_manual_reading_time( $post_id ) {
    $keys = array( 'reading_time', 'reading_minutes', 'reading-time', 'read_time', 'read_minutes' );
    foreach ( $keys as $k ) {
        $v = get_post_meta( $post_id, $k, true );
        if ( $v !== '' && $v !== false && intval( $v ) > 0 ) {
            return intval( $v );
        }
    }
    return '';
}

while ( have_posts() ) : the_post();

    $post_id = get_the_ID();

    /* Hero args for template-parts/news-hero.php - pass subtitle => false so partial prints ONLY the title */
    $hero_args = array(
        'eyebrow'   => '',
        'title'     => '', // leave default (news-hero will pick post title)
        'subtitle'  => false, // explicitly disable subtitle (so no excerpt/text in hero)
        'bg_url'    => '', // let partial pick featured image automatically
        'cta'       => '',
        'cta_label' => '',
    );
    set_query_var( 'args', $hero_args );
    // Use partial (news-hero.php) - it will show featured image as background and only the title
    if ( locate_template( 'template-parts/news-hero.php' ) ) {
        get_template_part( 'template-parts/news-hero' );
    } else {
        // fallback very small hero
        ?>
        <header class="sp-hero" style="<?php if ( has_post_thumbnail( $post_id ) ) echo 'background-image:url(' . esc_url( get_the_post_thumbnail_url( $post_id, 'full' ) ) . ');'; ?>">
          <div class="sp-hero-inner container">
            <h1 class="sp-hero-title"><?php the_title(); ?></h1>
          </div>
        </header>
        <?php
    }
    set_query_var( 'args', null );
    ?>

    <div class="container single-post-wrapper" style="padding-top:32px; padding-bottom:40px;">
      <div class="single-top-meta" style="display:flex;flex-direction:column;gap:18px;">
        <!-- Back to news -->
        <div class="single-back">
          <?php
          $posts_page_id = (int) get_option( 'page_for_posts' );
          $news_url = $posts_page_id ? get_permalink( $posts_page_id ) : home_url( '/news' );
          ?>
          <a class="button button--ghost" href="<?php echo esc_url( $news_url ); ?>" aria-label="<?php esc_attr_e( 'Torna alle news', 'theme-abcontact' ); ?>">← <?php esc_html_e( 'Torna alle news', 'theme-abcontact' ); ?></a>
        </div>

        <!-- Categories -->
        <div class="single-categories" aria-hidden="false" style="display:flex;gap:10px;flex-wrap:wrap;">
          <?php
          $cats = get_the_category( $post_id );
          if ( ! empty( $cats ) ) {
              foreach ( $cats as $c ) {
                  // Keep the same visual style used elsewhere: try to fetch category_color meta if set
                  $cat_color = get_term_meta( $c->term_id, 'category_color', true );
                  $cat_text_color = '';
                  if ( function_exists( 'abcontact_get_contrast_color' ) && $cat_color ) {
                      $cat_text_color = abcontact_get_contrast_color( $cat_color );
                  } else {
                      $cat_text_color = $cat_color ? '#fff' : '';
                  }

                  $style = '';
                  if ( $cat_color ) {
                      $style = 'background:' . esc_attr( $cat_color ) . '; color:' . esc_attr( $cat_text_color ) . '; padding:6px 10px; border-radius:8px; display:inline-block; font-weight:700; font-size:.9rem;';
                  } else {
                      $style = 'display:inline-block; padding:6px 10px; border-radius:8px; background:#f1f5f9; color:#0f1724; font-weight:700; font-size:.9rem;';
                  }

                  // categories as text (not link) per your instruction
                  echo '<span class="post-category" ' . ( $style ? 'style="' . esc_attr( $style ) . '"' : '' ) . '>' . esc_html( $c->name ) . '</span>';
              }
          }
          ?>
        </div>

        <!-- Title + meta -->
        <header class="entry-header" style="margin-top:6px;">
          <h1 class="entry-title" style="font-size:clamp(1.6rem,2.8vw,2.4rem);margin:0 0 10px;"><?php the_title(); ?></h1>

          <div class="entry-meta" style="color:var(--color-muted); font-size:0.95rem; display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( '', $post_id ) ); ?></time>

            <?php
            $manual_rt = abcontact_get_manual_reading_time( $post_id );
            if ( $manual_rt ) {
                echo '<span class="meta-sep">•</span>';
                echo '<span class="reading-time">' . intval( $manual_rt ) . ' ' . esc_html__( 'min', 'theme-abcontact' ) . '</span>';
            }
            ?>
          </div>
        </header>
      </div>

      <!-- Article content -->
      <article id="post-<?php the_ID(); ?>" <?php post_class( 'post-single entry-content' ); ?> style="margin-top:22px;">
        <div class="post-content-wrapper" style="max-width:920px;">
          <?php
          // Main content output
          the_content();

          // If you use page-breaks or Gutenberg <!--nextpage--> you'll get pagination via wp_link_pages
          wp_link_pages( array(
              'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Pagine articolo', 'theme-abcontact' ) . '"><span class="page-links-title">' . esc_html__( 'Pagine:', 'theme-abcontact' ) . '</span>',
              'after'  => '</nav>',
          ) );
          ?>
        </div>
      </article>

      <!-- Related news / previews -->
      <section class="related-news" aria-labelledby="related-news-title" style="margin-top:48px;">
        <div class="container" style="max-width:var(--site-max);">
          <h2 id="related-news-title" style="text-align:center;margin-bottom:22px;"><?php esc_html_e( 'Altre news', 'theme-abcontact' ); ?></h2>
          <div class="grid" style="display:grid; gap:24px; grid-template-columns: repeat(1,1fr);">
            <?php
            // Query related: latest 4 excluding current
            $q = new WP_Query( array(
                'post_type'      => 'post',
                'posts_per_page' => 4,
                'post__not_in'   => array( $post_id ),
                'orderby'        => 'date',
                'order'          => 'DESC',
            ) );
            if ( $q->have_posts() ) {
                echo '<div class="grid">';
                while ( $q->have_posts() ) {
                    $q->the_post();
                    set_query_var( 'post', get_post() );
                    get_template_part( 'template-parts/news-card' );
                    set_query_var( 'post', null );
                }
                echo '</div>';
                wp_reset_postdata();
            } else {
                echo '<p style="text-align:center;color:var(--color-muted);">' . esc_html__( 'Nessun altro articolo disponibile al momento.', 'theme-abcontact' ) . '</p>';
            }
            ?>
          </div>
        </div>
      </section>

      <!-- CTA partial (same used elsewhere) -->
      <section class="single-cta" style="margin-top:48px;">
        <?php
        if ( locate_template( 'template-parts/cta.php' ) ) {
            get_template_part( 'template-parts/cta' );
        } elseif ( locate_template( 'cta.php' ) ) {
            get_template_part( 'cta' );
        }
        ?>
      </section>

    </div> <!-- .single-post-wrapper -->

<?php
endwhile;

get_footer();