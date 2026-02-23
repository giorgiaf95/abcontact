<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$opt = get_option( 'abcontact_home_services_settings', array() );
if ( ! is_array( $opt ) ) {
    $opt = array();
}

$section_title    = ! empty( $opt['section_title'] ) ? $opt['section_title'] : __( 'I Nostri Servizi', 'theme-abcontact' );
$section_subtitle = ! empty( $opt['section_subtitle'] ) ? $opt['section_subtitle'] : __( "Soluzioni complete per l'efficienza energetica di privati e aziende", 'theme-abcontact' );
$items            = ( ! empty( $opt['items'] ) && is_array( $opt['items'] ) ) ? $opt['items'] : array();

if ( empty( $items ) ) {
    return;
}
?>

<section class="theme-services-section" aria-label="<?php esc_attr_e( 'I nostri servizi', 'theme-abcontact' ); ?>">
  <div class="container">
    <header class="theme-services__header">
      <h2 class="theme-services__title"><?php echo esc_html( $section_title ); ?></h2>
      <p class="theme-services__subtitle"><?php echo esc_html( $section_subtitle ); ?></p>
    </header>

    <div class="theme-services__list">
      <?php
      foreach ( $items as $row ) :
        $title    = isset( $row['title'] ) ? $row['title'] : '';
        $subtitle = isset( $row['subtitle'] ) ? $row['subtitle'] : '';
        $icon_id  = ! empty( $row['icon_id'] ) ? (int) $row['icon_id'] : 0;
        $pills    = ( ! empty( $row['pills'] ) && is_array( $row['pills'] ) ) ? $row['pills'] : array();

        if ( $title === '' && $subtitle === '' && ! $icon_id && empty( $pills ) ) {
            continue;
        }
      ?>
        <article class="service-row service-row--no-media">
          <div class="service-row__content">
            <div class="service-row__meta">
              <div class="service-row__icon-circle" aria-hidden="true">
                <?php
                  if ( $icon_id ) {
                    echo wp_get_attachment_image( $icon_id, 'thumbnail', false, array( 'class' => 'service-row__icon', 'alt' => '' ) );
                  } else {
                    echo '<svg class="service-row__icon-fallback" width="40" height="40" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg>';
                  }
                ?>
              </div>

              <div class="service-row__text">
                <?php if ( $title ) : ?>
                  <h3 class="service-row__title"><?php echo esc_html( $title ); ?></h3>
                <?php endif; ?>
                <?php if ( $subtitle ) : ?>
                  <div class="service-row__desc"><?php echo esc_html( $subtitle ); ?></div>
                <?php endif; ?>

                <?php if ( ! empty( $pills ) ) : ?>
                  <p class="service-row__pills">
                    <?php foreach ( $pills as $p ) :
                        $pl = $p['label'] ?? '';
                        $pu = $p['url'] ?? '';
                        if ( ! $pu ) continue;
                    ?>
                      <a class="pill" href="<?php echo esc_url( $pu ); ?>">
                        <?php echo esc_html( $pl ? $pl : __( 'Link', 'theme-abcontact' ) ); ?>
                      </a>
                    <?php endforeach; ?>
                  </p>
                <?php endif; ?>

              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>