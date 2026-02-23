<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$opt = get_option( 'abcontact_home_services_settings', array() );
if ( ! is_array( $opt ) ) $opt = array();

/**
 * Section texts (prototype):
 * - eyebrow: "I NOSTRI SERVIZI"
 * - title: "Soluzioni per ogni esigenza"
 * - subtitle/lead: long description
 *
 * Back-compat:
 * - section_title => eyebrow
 * - section_subtitle => title
 */
$eyebrow = $opt['eyebrow'] ?? ( $opt['section_title'] ?? __( 'I NOSTRI SERVIZI', 'theme-abcontact' ) );
$title   = $opt['title'] ?? ( $opt['section_subtitle'] ?? __( 'Soluzioni per ogni esigenza', 'theme-abcontact' ) );
$lead    = $opt['subtitle'] ?? __( "Dall'analisi delle tue bollette all'installazione di impianti fotovoltaici, ti accompagniamo verso l'efficienza energetica.", 'theme-abcontact' );

$groups  = isset( $opt['groups'] ) && is_array( $opt['groups'] ) ? $opt['groups'] : array();
$g_list  = array(
    'privati' => $groups['privati'] ?? array(),
    'aziende' => $groups['aziende'] ?? array(),
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
      <div class="home-services__eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
      <h2 class="home-services__title"><?php echo esc_html( $title ); ?></h2>
      <?php if ( $lead ) : ?>
        <p class="home-services__lead"><?php echo esc_html( $lead ); ?></p>
      <?php endif; ?>
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

          <div class="services-box__list" role="list">
            <?php foreach ( $items as $row ) :
              $it_title = $row['title'] ?? '';
              $it_sub   = $row['subtitle'] ?? '';
              $it_url   = $row['url'] ?? '';
              $it_icon  = ! empty( $row['icon_id'] ) ? (int) $row['icon_id'] : 0;

              // URL is mandatory in our model
              if ( ! $it_url ) continue;
              if ( ! $it_title && ! $it_sub ) continue;
            ?>
              <a class="services-item" role="listitem" href="<?php echo esc_url( $it_url ); ?>">
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
                  <div class="services-item__title"><?php echo esc_html( $it_title ); ?></div>
                  <?php if ( $it_sub ) : ?><div class="services-item__subtitle"><?php echo esc_html( $it_sub ); ?></div><?php endif; ?>
                </div>

                <span class="services-item__arrow" aria-hidden="true">›</span>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endforeach; ?>
    </div>
  </div>
</section>