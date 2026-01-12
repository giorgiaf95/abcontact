<?php
/**
 * Template Name: Chi Siamo
 * Description: Pagina "Chi Siamo"
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$post_id = get_the_ID();

$css_path = get_stylesheet_directory() . '/assets/css/page-chi-siamo.css';
if ( file_exists( $css_path ) ) {
    wp_enqueue_style( 'ab-page-chi-siamo', get_stylesheet_directory_uri() . '/assets/css/page-chi-siamo.css', array(), filemtime( $css_path ) );
}

/* =============================== Header =============================== */
get_header();

$hero_id = get_post_thumbnail_id( $post_id );
$hero_image_url = $hero_id ? wp_get_attachment_image_url( $hero_id, 'full' ) : '';

$hero_args = array(
    'eyebrow'   => '',
    'title'     => get_the_title( $post_id ),
    'subtitle'  => '', 
    'bg_url'    => $hero_image_url,
    'cta'       => '',
    'cta_label' => '',
);
set_query_var( 'args', $hero_args );
get_template_part( 'template-parts/news-hero' );
set_query_var( 'args', null );

if ( locate_template( 'template-parts/team-carousel.php' ) ) {
    get_template_part( 'template-parts/team-carousel' );
}

/* ============================= Metabox ============================= */
/* Section A */
$cs_a_title = get_post_meta( $post_id, 'cs_section_a_title', true );
$cs_a_text  = get_post_meta( $post_id, 'cs_section_a_text', true );
$cs_a_image_id = absint( get_post_meta( $post_id, 'cs_section_a_image_id', true ) );
$cs_a_image_url = $cs_a_image_id ? wp_get_attachment_image_url( $cs_a_image_id, 'chi_siamo_image' ) : '';

/* I nostri valori */
$cs_values_title = get_post_meta( $post_id, 'cs_values_title', true );
$cs_values_sub = get_post_meta( $post_id, 'cs_values_subtitle', true );

$values = array();
for ( $i = 1; $i <= 4; $i++ ) {
    $values[$i] = array(
        'icon_id' => absint( get_post_meta( $post_id, "cs_value_{$i}_icon_id", true ) ),
        'icon_url' => '',
        'title' => get_post_meta( $post_id, "cs_value_{$i}_title", true ),
        'text' => get_post_meta( $post_id, "cs_value_{$i}_text", true ),
    );
    if ( $values[$i]['icon_id'] ) {
        $values[$i]['icon_url'] = wp_get_attachment_image_url( $values[$i]['icon_id'], 'chi_siamo_icon' );
    }
}

/* Section C */
$cs_c_title = get_post_meta( $post_id, 'cs_section_c_title', true );
$cs_c_text  = get_post_meta( $post_id, 'cs_section_c_text', true );
$cs_c_image_id = absint( get_post_meta( $post_id, 'cs_section_c_image_id', true ) );
$cs_c_image_url = $cs_c_image_id ? wp_get_attachment_image_url( $cs_c_image_id, 'chi_siamo_image' ) : '';

/* Statistics (4 items) */
$cs_stats_heading = get_post_meta( $post_id, 'cs_stats_heading', true );
$stats = array();
for ( $i=1; $i<=4; $i++ ) {
    $stats[$i] = array(
        'value' => get_post_meta( $post_id, "cs_stat_{$i}_value", true ),
        'label' => get_post_meta( $post_id, "cs_stat_{$i}_label", true ),
    );
}

/* CTA final */
$cs_cta_title = get_post_meta( $post_id, 'cs_cta_title', true ) ?: __( 'Vuoi lavorare con noi?', 'abcontact' );
$cs_cta_text  = get_post_meta( $post_id, 'cs_cta_text', true );
$cs_cta_button_label = get_post_meta( $post_id, 'cs_cta_button_label', true ) ?: __( 'Candidati ora', 'abcontact' );
$cs_cta_button_link  = get_post_meta( $post_id, 'cs_cta_button_link', true );

?>

<main class="chi-siamo-main container" role="main">
  <div class="chi-siamo-inner">

    <!-- Section A: testo a sinistra, foto a destra -->
    <section class="cs-section cs-section-a">
      <div class="cs-row container">
        <div class="cs-col cs-col--text">
          <?php if ( $cs_a_title ) : ?>
            <h2 class="cs-title"><?php echo esc_html( $cs_a_title ); ?></h2>
          <?php endif; ?>
          <?php if ( $cs_a_text ) : ?>
            <div class="cs-text"><?php echo wp_kses_post( wpautop( $cs_a_text ) ); ?></div>
          <?php endif; ?>
        </div>
        <div class="cs-col cs-col--media">
          <?php if ( $cs_a_image_url ) : ?>
            <img class="cs-image" src="<?php echo esc_url( $cs_a_image_url ); ?>" alt="<?php echo esc_attr( $cs_a_title ?: get_the_title() ); ?>">
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- I nostri valori -->
    <section class="cs-values">
      <div class="container">
        <?php if ( $cs_values_title ) : ?>
          <p class="values-eyebrow"><?php echo esc_html( $cs_values_title ); ?></p>
        <?php endif; ?>
        <?php if ( $cs_values_sub ) : ?>
          <div class="values-sub"><?php echo wp_kses_post( wpautop( $cs_values_sub ) ); ?></div>
        <?php endif; ?>

        <div class="values-grid">
          <?php foreach ( $values as $v ) : ?>
            <div class="values-item">
              <?php if ( ! empty( $v['icon_url'] ) ) : ?>
                <div class="values-icon"><img src="<?php echo esc_url( $v['icon_url'] ); ?>" alt="" /></div>
              <?php endif; ?>
              <?php if ( $v['title'] ) : ?><h4 class="values-item-title"><?php echo esc_html( $v['title'] ); ?></h4><?php endif; ?>
              <?php if ( $v['text'] ) : ?><div class="values-item-text"><?php echo wp_kses_post( wpautop( $v['text'] ) ); ?></div><?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Section C: foto a sinistra, testo a destra -->
    <section class="cs-section cs-section-c">
      <div class="cs-row container reverse-on-desktop">
        <div class="cs-col cs-col--media">
          <?php if ( $cs_c_image_url ) : ?>
            <img class="cs-image" src="<?php echo esc_url( $cs_c_image_url ); ?>" alt="<?php echo esc_attr( $cs_c_title ?: get_the_title() ); ?>">
          <?php endif; ?>
        </div>
        <div class="cs-col cs-col--text">
          <?php if ( $cs_c_title ) : ?>
            <h3 class="cs-title"><?php echo esc_html( $cs_c_title ); ?></h3>
          <?php endif; ?>
          <?php if ( $cs_c_text ) : ?>
            <div class="cs-text"><?php echo wp_kses_post( wpautop( $cs_c_text ) ); ?></div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Insert Reviews Shortcode (if provided in metabox) -->
    <?php
    $cs_reviews_shortcode = get_post_meta( $post_id, 'cs_reviews_shortcode', true );
    if ( ! empty( $cs_reviews_shortcode ) ) :
    ?>
      <section class="cs-reviews" aria-label="<?php esc_attr_e( 'Recensioni', 'abcontact' ); ?>">
        <div class="container cs-reviews-inner" style="max-width:var(--max-width); margin:28px auto;">
          <?php echo do_shortcode( $cs_reviews_shortcode ); ?>
        </div>
      </section>
    <?php
    endif;
    ?>

    <!-- Statistics: blue box with white text -->
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

    <!-- CTA finale -->
<section class="cs-cta">
  <div class="container">
    <div class="cs-cta-inner">
      <div class="cs-cta-text">
        <?php if ( $cs_cta_title ) : ?><h3 class="cs-cta-title"><?php echo esc_html( $cs_cta_title ); ?></h3><?php endif; ?>
        <?php if ( $cs_cta_text ) : ?><div class="cs-cta-sub"><?php echo wp_kses_post( wpautop( $cs_cta_text ) ); ?></div><?php endif; ?>
      </div>

      <div class="cs-cta-action">
        <?php if ( $cs_cta_button_link ) : ?>
          <a class="cta-button" href="<?php echo esc_url( $cs_cta_button_link ); ?>" rel="noopener"><?php echo esc_html( $cs_cta_button_label ); ?></a>
        <?php else : ?>
          <button class="cta-button" type="button"><?php echo esc_html( $cs_cta_button_label ); ?></button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

  </div>
</main>

<!-- Inline JS: simple counter animation (animates integer part on viewport) -->
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