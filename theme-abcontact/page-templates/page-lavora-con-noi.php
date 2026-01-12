<?php
/**
 * Template Name: Lavora con noi
 * Template Post Type: page
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$post_id = get_the_ID();

/* Enqueue page-specific CSS */
$css_path = get_stylesheet_directory() . '/assets/css/lavora-con-noi.css';
if ( file_exists( $css_path ) ) {
    wp_enqueue_style( 'ab-lavora-con-noi', get_stylesheet_directory_uri() . '/assets/css/lavora-con-noi.css', array( 'abcontact-main' ), filemtime( $css_path ) );
}

get_header();

/* hero-default/news-hero */
set_query_var( 'args', array(
    'eyebrow' => __( 'Lavora con noi', 'abcontact' ),
    'title'   => get_the_title( $post_id ),
    'subtitle' => '',
    'bg_url'  => get_the_post_thumbnail_url( $post_id, 'full' )
) );
get_template_part( 'template-parts/news-hero' );
set_query_var( 'args', null );
?>

<?php
// inserisco la sezione "Perché scegliere Abcontact" sotto l'hero
if ( locate_template( 'template-parts/lavora-why.php' ) ) {
    get_template_part( 'template-parts/lavora-why' );
}
?>

<?php
/* ---------------------------
   Posizioni Aperte (render from page meta _lc_positions)
   --------------------------- */
$positions_meta = get_post_meta( $post_id, '_lc_positions', true );
$positions = array();
if ( $positions_meta ) {
    $decoded = json_decode( $positions_meta, true );
    if ( is_array( $decoded ) ) {
        $positions = $decoded;
    }
}
?>

<?php if ( ! empty( $positions ) ) : ?>
<section class="lc-positions" aria-label="<?php esc_attr_e( 'Posizioni Aperte', 'abcontact' ); ?>">
  <div class="lc-positions-inner container">
    <header class="lc-positions-header">
      <h2 class="lc-positions-title"><?php esc_html_e( 'Posizioni Aperte', 'abcontact' ); ?></h2>
      <p class="lc-positions-leadin"><?php esc_html_e( "Scopri le opportunità di carriera disponibili e trova quella più adatta a te", "abcontact" ); ?></p>
    </header>

    <div class="lc-positions-grid">
      <?php foreach ( $positions as $pos ) :
        $title = isset( $pos['title'] ) ? $pos['title'] : '';
        $excerpt = isset( $pos['excerpt'] ) ? $pos['excerpt'] : '';
        $image_id = isset( $pos['image_id'] ) ? intval( $pos['image_id'] ) : 0;
        $requirements = isset( $pos['requirements'] ) ? $pos['requirements'] : '';
        $offer = isset( $pos['offer'] ) ? $pos['offer'] : '';
        $apply_url = isset( $pos['apply_url'] ) ? $pos['apply_url'] : '';
      ?>
        <article class="lc-position-card">
          <?php if ( $image_id ) : ?>
            <div class="lc-position-media">
              <?php echo wp_get_attachment_image( $image_id, 'large' ); ?>
            </div>
          <?php endif; ?>

          <div class="lc-position-body">
            <div class="lc-position-head">
              <h3 class="lc-position-title">
                <?php if ( $apply_url ) : ?>
                  <a href="<?php echo esc_url( $apply_url ); ?>"><?php echo esc_html( $title ); ?></a>
                <?php else : ?>
                  <?php echo esc_html( $title ); ?>
                <?php endif; ?>
              </h3>
            </div>

            <?php if ( $excerpt ) : ?>
              <div class="lc-position-excerpt"><?php echo wp_kses_post( wpautop( wp_trim_words( $excerpt, 40 ) ) ); ?></div>
            <?php endif; ?>

            <?php if ( $requirements ) : ?>
              <div class="lc-position-reqs">
                <h4><?php esc_html_e( 'Requisiti', 'abcontact' ); ?></h4>
                <?php
                if ( strpos( $requirements, '<' ) === false ) {
                  $lines = preg_split( '/\r\n|\r|\n/', trim( $requirements ) );
                  echo '<ul class="lc-position-list">';
                  foreach ( $lines as $l ) {
                    if ( trim( $l ) === '' ) continue;
                    echo '<li>' . esc_html( trim( $l ) ) . '</li>';
                  }
                  echo '</ul>';
                } else {
                  echo wp_kses_post( $requirements );
                }
                ?>
              </div>
            <?php endif; ?>

            <?php if ( $offer ) : ?>
              <div class="lc-position-offer">
                <h4><?php esc_html_e( 'Cosa Offriamo', 'abcontact' ); ?></h4>
                <?php
                if ( strpos( $offer, '<' ) === false ) {
                  $lines = preg_split( '/\r\n|\r|\n/', trim( $offer ) );
                  echo '<ul class="lc-position-list">';
                  foreach ( $lines as $l ) {
                    if ( trim( $l ) === '' ) continue;
                    echo '<li>' . esc_html( trim( $l ) ) . '</li>';
                  }
                  echo '</ul>';
                } else {
                  echo wp_kses_post( $offer );
                }
                ?>
              </div>
            <?php endif; ?>

            <div class="lc-position-action">
              <?php if ( $apply_url ) : ?>
                <a class="button lc-position-cta" href="<?php echo esc_url( $apply_url ); ?>"><?php esc_html_e( 'Invia Candidatura', 'abcontact' ); ?></a>
              <?php else : ?>
                <a class="button lc-position-cta" href="<?php echo esc_url( home_url( '/contatti' ) ); ?>"><?php esc_html_e( 'Invia Candidatura', 'abcontact' ); ?></a>
              <?php endif; ?>
            </div>

          </div>
        </article>
      <?php endforeach; ?>
    </div>

  </div>
</section>
<?php else : ?>
  <section class="lc-positions lc-positions-empty" aria-label="<?php esc_attr_e( 'Posizioni Aperte', 'abcontact' ); ?>">
    <div class="container">
      <header class="lc-positions-header">
        <h2 class="lc-positions-title"><?php esc_html_e( 'Posizioni Aperte', 'abcontact' ); ?></h2>
        <p class="lc-positions-leadin"><?php esc_html_e( "Al momento non ci sono posizioni aperte. Puoi comunque inviarci una candidatura spontanea.", "abcontact" ); ?></p>
      </header>
      <p><a class="button" href="<?php echo esc_url( home_url( '/contatti' ) ); ?>"><?php esc_html_e( 'Invia Candidatura Spontanea', 'abcontact' ); ?></a></p>
    </div>
  </section>
<?php endif; ?>

<main class="lavora-main" role="main" aria-label="<?php echo esc_attr__( 'Lavora con noi', 'abcontact' ); ?>">
  <div class="container lavora-content">

    <?php
    // CTA global (existing partial)
    if ( locate_template( 'template-parts/cta.php' ) ) {
        get_template_part( 'template-parts/cta' );
    }
    ?>

  </div>
</main>

<?php get_footer(); ?>