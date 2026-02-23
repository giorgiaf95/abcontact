<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$opt = get_option( 'abcontact_home_services_settings', array() );
if ( ! is_array( $opt ) ) $opt = array();

$section_title    = $opt['section_title'] ?? __( 'I Nostri Servizi', 'theme-abcontact' );
$section_subtitle = $opt['section_subtitle'] ?? __( "Soluzioni complete per l'efficienza energetica di privati e aziende", 'theme-abcontact' );

$groups  = isset( $opt['groups'] ) && is_array( $opt['groups'] ) ? $opt['groups'] : array();
$privati = $groups['privati'] ?? array();
$aziende = $groups['aziende'] ?? array();

$g_list = array(
    'privati' => $privati,
    'aziende' => $aziende,
);

$has_any = false;
foreach ( $g_list as $g ) {
    if ( ! empty( $g['items'] ) && is_array( $g['items'] ) ) { $has_any = true; break; }
}
if ( ! $has_any ) return;
?>

<section class="home-services" aria-label="<?php esc_attr_e( 'I nostri servizi', 'theme-abcontact' ); ?>">
  <div class="container">
    <header class="home-services__header">
      <h2 class="home-services__eyebrow"><?php echo esc_html( $section_title ); ?></h2>
      <p class="home-services__title"><?php echo esc_html( $section_subtitle ); ?></p>
    </header>

    <div class="home-services__grid">
      <?php foreach ( array( 'privati', 'aziende' ) as $key ) :
        $g = $g_list[ $key ];
        $g_title   = $g['title'] ?? '';
        $g_sub     = $g['subtitle'] ?? '';
        $g_icon_id = ! empty( $g['icon_id'] ) ? (int) $g['icon_id'] : 0;

        $items = ( ! empty( $g['items'] ) && is_array( $g['items'] ) ) ? $g['items'] : array();
        if ( empty( $items ) ) continue;
      ?>
        <section class="services-box services-box--<?php echo esc_attr( $key ); ?>">
          <header class="services-box__head">
            <div class="services-box__badge" aria-hidden="true">
              <?php
              if ( $g_icon_id ) {
                  echo wp_get_attachment_image( $g_icon_id, 'thumbnail', false, array(
                      'class' => 'services-box__badge-img',
                      'alt'   => '',
                  ) );
              }
              ?>
            </div>
            <div class="services-box__titles">
              <h3 class="services-box__title"><?php echo esc_html( $g_title ); ?></h3>
              <?php if ( $g_sub ) : ?><p class="services-box__subtitle"><?php echo esc_html( $g_sub ); ?></p><?php endif; ?>
            </div>
          </header>

          <div class="services-box__list">
            <?php foreach ( $items as $row ) :
              $it_title = $row['title'] ?? '';
              $it_sub   = $row['subtitle'] ?? '';
              $it_url   = $row['url'] ?? '';
              $it_icon  = ! empty( $row['icon_id'] ) ? (int) $row['icon_id'] : 0;

              if ( ! $it_url ) continue; // link obbligatorio
              if ( ! $it_title && ! $it_sub ) continue;
            ?>
              <article class="services-item">
                <div class="services-item__row">
                  <div class="services-item__icon" aria-hidden="true">
                    <?php
                    if ( $it_icon ) {
                        echo wp_get_attachment_image( $it_icon, 'thumbnail', false, array(
                            'class' => 'services-item__icon-img',
                            'alt'   => '',
                        ) );
                    }
                    ?>
                  </div>

                  <div class="services-item__text">
                    <h4 class="services-item__title"><?php echo esc_html( $it_title ); ?></h4>
                    <?php if ( $it_sub ) : ?><p class="services-item__subtitle"><?php echo esc_html( $it_sub ); ?></p><?php endif; ?>
                  </div>

                  <a class="services-pill" href="<?php echo esc_url( $it_url ); ?>">
                    <?php echo esc_html( $it_title ?: __( 'Scopri', 'theme-abcontact' ) ); ?>
                  </a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endforeach; ?>
    </div>
  </div>
</section>