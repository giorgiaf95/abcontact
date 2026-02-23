<?php
/**
 * Template Name: Chi Siamo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$post_id = get_the_ID();

/* Enqueue CSS for this template */
$css_path = get_stylesheet_directory() . '/assets/css/page-chi-siamo.css';
if ( file_exists( $css_path ) ) {
    wp_enqueue_style( 'ab-page-chi-siamo', get_stylesheet_directory_uri() . '/assets/css/page-chi-siamo.css', array(), filemtime( $css_path ) );
}

/* =============================== Header =============================== */
get_header();

$hero_id = get_post_thumbnail_id( $post_id );
$hero_image_url = $hero_id ? wp_get_attachment_image_url( $hero_id, 'full' ) : '';

$hero_subtitle = get_post_meta( $post_id, 'cs_hero_subtitle', true );
$hero_subtitle = is_string( $hero_subtitle ) ? wp_kses_post( $hero_subtitle ) : '';

$hero_args = array(
    'eyebrow'   => '',
    'title'     => get_the_title( $post_id ),
    'subtitle'  => $hero_subtitle,
    'bg_url'    => $hero_image_url,
    'cta'       => '',
    'cta_label' => '',
);

set_query_var( 'args', $hero_args );
get_template_part( 'template-parts/news-hero' );
set_query_var( 'args', null );

/* ============================= Data (cards repeater + stats + CTA) ============================= */

$cs_cards_title = get_post_meta( $post_id, 'cs_cards_title', true );
$cs_cards_sub   = get_post_meta( $post_id, 'cs_cards_subtitle', true );

if ( empty( $cs_cards_title ) ) {
    $cs_cards_title = __( 'Come lavoriamo', 'abcontact' );
}

$cs_cards_raw = get_post_meta( $post_id, '_cs_how_steps', true );
$cs_cards = is_array( $cs_cards_raw ) ? $cs_cards_raw : array();

/* Statistics (4 items) — MUST remain unchanged */
$cs_stats_heading = get_post_meta( $post_id, 'cs_stats_heading', true );
$stats = array();
for ( $i=1; $i<=4; $i++ ) {
    $stats[$i] = array(
        'value' => get_post_meta( $post_id, "cs_stat_{$i}_value", true ),
        'label' => get_post_meta( $post_id, "cs_stat_{$i}_label", true ),
    );
}

/* CTA: centralized cta_* meta (preferred) with legacy fallbacks (read-only) */
$cta_title        = get_post_meta( $post_id, 'cta_title', true );
$cta_subtitle     = get_post_meta( $post_id, 'cta_subtitle', true );
$cta_button_label = get_post_meta( $post_id, 'cta_button_label', true );
$cta_button_link  = get_post_meta( $post_id, 'cta_button_link', true );
$cta_button_color = get_post_meta( $post_id, 'cta_button_color', true );
$cta_modal_raw    = get_post_meta( $post_id, 'cta_modal', true );
$cta_modal        = $cta_modal_raw ? true : false;

if ( empty( $cta_title ) ) {
    $cta_title = get_post_meta( $post_id, 'cs_cta_title', true ) ?: get_post_meta( $post_id, 'service_final_cta_title', true );
}
if ( empty( $cta_subtitle ) ) {
    $cta_subtitle = get_post_meta( $post_id, 'cs_cta_text', true ) ?: get_post_meta( $post_id, 'service_final_cta_text', true );
}
if ( empty( $cta_button_label ) ) {
    $cta_button_label = get_post_meta( $post_id, 'cs_cta_button_label', true ) ?: get_post_meta( $post_id, 'service_final_cta_button_label', true );
}
if ( empty( $cta_button_link ) ) {
    $cta_button_link = get_post_meta( $post_id, 'cs_cta_button_link', true ) ?: get_post_meta( $post_id, 'service_final_cta_link', true );
}
if ( empty( $cta_button_color ) ) {
    $cta_button_color = get_post_meta( $post_id, 'cs_cta_button_color', true ) ?: get_post_meta( $post_id, 'service_final_cta_button_color', true );
}

if ( ! empty( $cta_button_link ) ) {
    $raw_link = trim( $cta_button_link );
    if ( ! preg_match( '#^https?://#i', $raw_link ) ) {
        $cta_button_link = home_url( '/' . ltrim( $raw_link, '/' ) );
    } else {
        $cta_button_link = $raw_link;
    }
}
?>

<main class="chi-siamo-main container" role="main">
  <div class="chi-siamo-inner">

    <!-- ===== Contenuto Gutenberg (unico gruppo testo) ===== -->
    <section class="cs-content container" aria-label="<?php esc_attr_e( 'Contenuto', 'abcontact' ); ?>">
      <div class="cs-content-inner">
        <?php
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
        ?>
      </div>
    </section>

    <!-- ===== Card repeater ===== -->
    <?php if ( ! empty( $cs_cards ) ) : ?>
      <section class="cs-how container" aria-label="<?php echo esc_attr( $cs_cards_title ); ?>">
        <header class="cs-how-header">
          <h2 class="cs-how-title"><?php echo esc_html( $cs_cards_title ); ?></h2>
          <?php if ( ! empty( $cs_cards_sub ) ) : ?>
            <p class="cs-how-sub"><?php echo esc_html( $cs_cards_sub ); ?></p>
          <?php endif; ?>
        </header>

        <div class="cs-how-grid">
          <?php foreach ( $cs_cards as $c ) :
            if ( ! is_array( $c ) ) continue;
            $t = isset( $c['title'] ) ? sanitize_text_field( $c['title'] ) : '';
            $x = isset( $c['text'] ) ? sanitize_textarea_field( $c['text'] ) : '';
            $icon_id = isset( $c['icon_id'] ) ? absint( $c['icon_id'] ) : 0;
            if ( $t === '' && $x === '' ) continue;
            $icon_url = $icon_id ? wp_get_attachment_image_url( $icon_id, array(64,64) ) : '';
            ?>
            <article class="cs-how-item">
              <?php if ( $icon_url ) : ?>
                <div class="cs-how-icon" aria-hidden="true">
                  <img src="<?php echo esc_url( $icon_url ); ?>" alt="">
                </div>
              <?php endif; ?>

              <div class="cs-how-body">
                <?php if ( $t ) : ?><h3 class="cs-how-item-title"><?php echo esc_html( $t ); ?></h3><?php endif; ?>
                <?php if ( $x ) : ?><p class="cs-how-item-text"><?php echo esc_html( $x ); ?></p><?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- ===== Team carousel (sotto le card) ===== -->
    <?php
    if ( locate_template( 'template-parts/team-carousel.php' ) ) {
        get_template_part( 'template-parts/team-carousel' );
    }
    ?>

    <!-- Statistics: blue box with white text (UNCHANGED) -->
    <section class="cs-stats" aria-label="<?php esc_attr_e( 'Statistiche aziendali', 'abcontact' ); ?>">
      <div class="container cs-stats-inner">
        <?php if ( $cs_stats_heading ) : ?>
          <h3 class="cs-stats-heading"><?php echo esc_html( $cs_stats_heading ); ?></h3>
        <?php endif; ?>
        <div class="cs-stats-grid">
          <?php foreach ( $stats as $s ) : ?>
            <div class="cs-stat-item">
              <div class="cs-stat-value" data-target="<?php echo esc_attr( preg_replace('/[^0-9]/', '', $s['value'] ) ); ?>">
                <?php echo esc_html( $s['value'] ); ?>
              </div>
              <div class="cs-stat-label"><?php echo esc_html( $s['label'] ); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- CTA finale (central partial) -->
    <?php
    $cta_args = array(
      'title'        => $cta_title,
      'subtitle'     => $cta_subtitle,
      'button_label' => $cta_button_label,
      'button_link'  => $cta_button_link,
      'button_color' => $cta_button_color,
      'modal'        => $cta_modal,
    );

    set_query_var( 'args', $cta_args );
    get_template_part( 'template-parts/cta', null, $cta_args );
    set_query_var( 'args', null );
    ?>

  </div>
</main>

<!-- Inline JS: simple counter animation (UNCHANGED) -->
<script>
(function(){
  function animateCounters() {
    var items = document.querySelectorAll('.cs-stat-value');
    if (!items.length) return;
    var io = new IntersectionObserver(function(entries, obs){
      entries.forEach(function(entry){
        if (!entry.isIntersecting) return;
        var el = entry.target;
        var target = parseInt(el.getAttribute('data-target'), 10) || 0;
        if (el.dataset.animated) return;
        el.dataset.animated = '1';
        var duration = 1300;
        var start = 0;
        var startTime = null;
        function step(ts){
          if (!startTime) startTime = ts;
          var progress = Math.min((ts - startTime) / duration, 1);
          var value = Math.floor(progress * (target - start) + start);
          el.textContent = el.textContent.replace(/^\D*\d[\d,.]*/, value.toLocaleString());
          if (progress < 1) {
            requestAnimationFrame(step);
          }
        }
        requestAnimationFrame(step);
        obs.unobserve(el);
      });
    }, { threshold: 0.3 });
    items.forEach(function(i){ io.observe(i); });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', animateCounters);
  } else {
    animateCounters();
  }
})();
</script>

<?php
get_footer();
?>