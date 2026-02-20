<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Query services
$args = array(
    'post_type'      => 'ab_service',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
);
$q = new WP_Query( $args );
if ( ! $q->have_posts() ) {
    return;
}

$i = 0;
?>
<section class="theme-services-section" aria-label="<?php esc_attr_e( 'I nostri servizi', 'theme-abcontact' ); ?>">
  <div class="container">
    <header class="theme-services__header">
      <h2 class="theme-services__title"><?php esc_html_e( 'I Nostri Servizi', 'theme-abcontact' ); ?></h2>
      <p class="theme-services__subtitle"><?php esc_html_e( "Soluzioni complete per l'efficienza energetica di privati e aziende", 'theme-abcontact' ); ?></p>
    </header>

    <div class="theme-services__list">
      <?php while ( $q->have_posts() ) : $q->the_post(); $i++; $is_rev = ( $i % 2 === 0 ); ?>
        <article class="service-row <?php echo $is_rev ? 'service-row--rev' : ''; ?>" id="service-<?php the_ID(); ?>">
          <div class="service-row__media" aria-hidden="true">
            <?php
              if ( has_post_thumbnail() ) {
                the_post_thumbnail( 'ab-service-large', array( 'class' => 'service-row__img' ) );
              } else {
                echo '<div class="service-row__img service-row__img--placeholder"></div>';
              }
            ?>
          </div>

          <div class="service-row__content">
            <div class="service-row__meta">
              <div class="service-row__icon-circle" aria-hidden="true">
                <?php
                  $icon_id = get_post_meta( get_the_ID(), '_ab_icon_id', true );
                  if ( $icon_id ) {
                    echo wp_get_attachment_image( intval( $icon_id ), 'ab-service-icon', false, array( 'class' => 'service-row__icon' ) );
                  } else {
                    echo '<svg class="service-row__icon-fallback" width="40" height="40" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg>';
                  }
                ?>
              </div>

              <div class="service-row__text">
                <h3 class="service-row__title"><?php the_title(); ?></h3>
                <div class="service-row__desc"><?php the_excerpt(); ?></div>
              </div>
            </div>

            <?php
              $links_json = get_post_meta( get_the_ID(), '_ab_links', true );
              $links = $links_json ? json_decode( $links_json, true ) : array();
              if ( ! empty( $links ) && is_array( $links ) ) :
            ?>
              <p class="service-row__pills">
                <?php foreach ( $links as $ln ) : if ( empty( $ln['url'] ) ) continue; ?>
                  <a class="pill" href="<?php echo esc_url( $ln['url'] ); ?>"><?php echo esc_html( $ln['label'] ?: $ln['url'] ); ?></a>
                <?php endforeach; ?>
              </p>
            <?php endif; ?>

            <?php
              // CTA
              $cta = get_post_meta( get_the_ID(), '_ab_cta_url', true );
              $cta_label = get_post_meta( get_the_ID(), '_ab_cta_label', true );
              if ( $cta ) :
            ?>
              <p class="service-row__cta"><a class="button" href="<?php echo esc_url( $cta ); ?>"><?php echo esc_html( $cta_label ?: __( 'Scopri', 'theme-abcontact' ) ); ?></a></p>
            <?php endif; ?>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>