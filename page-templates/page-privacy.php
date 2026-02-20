<?php
/**
 * Template Name: Privacy Policy
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<?php
// get featured image (full)
$bg_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
$has_bg = $bg_url ? true : false;
?>

<main id="main" class="site-main page-legal">
  <!-- Hero (news style) - uses featured image if present -->
  <section class="hero news-hero page-hero--news <?php echo $has_bg ? 'has-bg' : 'no-bg'; ?>" <?php if ( $has_bg ) : ?>style="background-image: url('<?php echo esc_url( $bg_url ); ?>');"<?php endif; ?> aria-hidden="false">
    <div class="hero-inner wrap">
      <div class="hero-content">
        <span class="hero__eyebrow"><?php esc_html_e( 'Informativa', 'abcontact' ); ?></span>
        <h1 class="hero-title"><?php echo esc_html( get_the_title() ); ?></h1>
        <?php if ( get_post_field( 'post_excerpt', get_the_ID() ) ) : ?>
          <p class="hero-subtitle"><?php echo wp_kses_post( get_post_field( 'post_excerpt', get_the_ID() ) ); ?></p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Small inline script: ensure header/menu is visible on load -->
  <script>
    (function(){
      try {
        var onReady = function(){
          var hdr = document.getElementById('site-header');
          if (!hdr) return;
          hdr.classList.add('is-sticky','visible');
          hdr.style.zIndex = 9999;
          hdr.style.top = hdr.style.top || '0px';
          hdr.classList.remove('header--transparent','transparent');
        };
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
          setTimeout(onReady, 20);
        } else {
          window.addEventListener('load', function(){ setTimeout(onReady, 20); });
        }
      } catch(e){ /* noop */ }
    })();
  </script>

<!-- spacer responsivo tra hero e contenuto -->
<div class="hero-content-spacer" aria-hidden="true" style="height:72px; width:100%; display:block;"></div>
<script>
(function(){
  // Imposta spacer responsive: 72px desktop, 36px su mobile
  function updateHeroSpacer() {
    var h = (window.innerWidth && window.innerWidth < 820) ? 36 : 72;
    Array.prototype.slice.call(document.querySelectorAll('.hero-content-spacer')).forEach(function(el){
      el.style.height = h + 'px';
    });
  }
  // applica subito e al resize
  updateHeroSpacer();
  window.addEventListener('resize', updateHeroSpacer);
})();
</script>

  <section class="page-content wrap" aria-labelledby="content">
    <div class="content-inner">
      <?php
while ( have_posts() ) :
  the_post();
  the_content();
endwhile;
?>
<!-- spacer garantito per separare contenuto e footer -->
<div class="legal-bottom-spacer" aria-hidden="true" style="height:220px;width:100%;display:block;"></div>
<?php
      ?>
    </div>
  </section>
</main>

<?php
get_footer();