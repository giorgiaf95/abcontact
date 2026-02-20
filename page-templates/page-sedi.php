<?php
/**
 * Template Name: Sedi
 *
 * Front-end template: usa i meta impostati nel metabox del tema
 * per mostrare:
 *  - mappa (shortcode)
 *  - blocchi ripetibili (left)
 *  - CTA blu (right)
 *  - elenco sedi (dal CPT 'sede', non modificato)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$post_id = get_the_ID();

$theme_css_path = get_stylesheet_directory() . '/assets/css/service-template.css';
if ( file_exists( $theme_css_path ) ) {
    wp_enqueue_style( 'ab-service-template', get_stylesheet_directory_uri() . '/assets/css/service-template.css', array(), filemtime( $theme_css_path ) );
}
$local_css = get_stylesheet_directory() . '/assets/css/page-sedi.css';
if ( file_exists( $local_css ) ) {
    wp_enqueue_style( 'ab-page-sedi', get_stylesheet_directory_uri() . '/assets/css/page-sedi.css', array( 'ab-service-template' ), filemtime( $local_css ) );
}

get_header();

/* Hero partial (unchanged) */
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

/* Query CPT 'sede' (unchanged) */
$sedi = get_posts( array(
    'post_type'      => 'sede',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'title',
    'order'          => 'ASC',
) );

$payload = array();
foreach ( $sedi as $s ) {
    $city    = get_post_meta( $s->ID, 'sede_city', true );
    $address = get_post_meta( $s->ID, 'sede_address', true );
    $phone   = get_post_meta( $s->ID, 'sede_phone', true );
    $email   = get_post_meta( $s->ID, 'sede_email', true );
    $maps    = get_post_meta( $s->ID, 'sede_maps_url', true );
    $lat     = get_post_meta( $s->ID, 'sede_lat', true );
    $lng     = get_post_meta( $s->ID, 'sede_lng', true );

    $maps_link = $maps ? $maps : 'https://www.google.com/maps/search/' . rawurlencode( trim( $address . ' ' . $city ) );

    $payload[] = array(
        'id'      => $s->ID,
        'title'   => get_the_title( $s ),
        'city'    => $city,
        'address' => $address,
        'phone'   => $phone,
        'email'   => $email,
        'maps'    => $maps_link,
        'lat'     => $lat,
        'lng'     => $lng,
    );
}

/* CTA / meta */
$cta_link_meta       = get_post_meta( $post_id, 'sedi_cta_button_link', true );
$cta_label_meta      = get_post_meta( $post_id, 'sedi_cta_button_label', true );
$cta_title_meta      = get_post_meta( $post_id, 'sedi_cta_title', true );
$cta_text_meta       = get_post_meta( $post_id, 'sedi_cta_text', true );
$support_phone_meta  = get_post_meta( $post_id, 'sedi_cta_support_phone', true );
$support_email_meta  = get_post_meta( $post_id, 'sedi_cta_support_email', true );
$map_shortcode_meta  = get_post_meta( $post_id, 'sedi_map_shortcode', true );

$cta_link      = $cta_link_meta ?: ( get_post_meta( $post_id, 'service_form_link', true ) ?: home_url( '/contattaci' ) );
$cta_label     = $cta_label_meta ?: ( get_post_meta( $post_id, 'service_cta_label', true ) ?: __( 'Richiedi Consulenza', 'abcontact' ) );
$cta_title     = $cta_title_meta ?: __( 'Contattaci Subito', 'abcontact' );
$cta_text      = $cta_text_meta ?: '';
$support_phone = $support_phone_meta ?: get_option( 'abcontact_support_phone', '800 123 456' );
$support_email = $support_email_meta ?: get_option( 'abcontact_support_email', get_option( 'admin_email' ) );
$map_shortcode = $map_shortcode_meta ?: '';

/* Map title: use metabox "Testo introduttivo (hero / lead)" if set, otherwise fallback */
$map_title_meta = get_post_meta( $post_id, 'sedi_intro_text', true );
$map_title = $map_title_meta ? wp_strip_all_tags( $map_title_meta ) : __( 'Mappa interattiva delle sedi', 'abcontact' );

/* Page blocks (left column) */
$sedi_blocks = get_post_meta( $post_id, 'sedi_blocks', true );
if ( is_string( $sedi_blocks ) && $sedi_blocks !== '' ) {
    $sedi_blocks = maybe_unserialize( $sedi_blocks );
}
if ( ! is_array( $sedi_blocks ) ) {
    $sedi_blocks = array();
}
?>

<main class="sedi-main container" role="main">
  <div class="sedi-inner">

    <div class="sedi-map-hero">
      <div id="sedi-map" class="sedi-map-placeholder" role="region" aria-label="<?php echo esc_attr__( 'Mappa Sedi', 'abcontact' ); ?>">
        <h2 class="map-title"><?php echo esc_html( $map_title ); ?></h2>

        <?php
        if ( ! empty( $map_shortcode ) ) {
            echo '<div class="sedi-map-shortcode-inner">';
            echo do_shortcode( $map_shortcode );
            echo '</div>';
        }
        ?>
      </div>
    </div>

    <!-- CTA: left dynamic groups + right CTA card -->
    <section class="sedi-cta" aria-hidden="false">
      <div class="sedi-cta-inner">

        <div class="sedi-cta-left" role="region" aria-label="<?php echo esc_attr__( 'Non trovi la tua città - testo', 'abcontact' ); ?>">
          <?php if ( ! empty( $sedi_blocks ) ) : ?>
            <?php foreach ( $sedi_blocks as $blk ) : ?>
              <?php
                $b_title = isset( $blk['title'] ) ? $blk['title'] : '';
                $b_body  = isset( $blk['body'] ) ? $blk['body'] : '';
                $b_bullets = isset( $blk['bullets'] ) && is_array( $blk['bullets'] ) ? $blk['bullets'] : array();
              ?>
              <?php if ( $b_title ) : ?>
                <h3 class="sedi-cta-left-title"><?php echo esc_html( $b_title ); ?></h3>
              <?php endif; ?>

              <?php if ( $b_body ) : ?>
                <div class="sedi-cta-left-desc"><?php echo wp_kses_post( wpautop( $b_body ) ); ?></div>
              <?php endif; ?>

              <?php if ( ! empty( $b_bullets ) ) : ?>
                <ul class="sedi-cta-features" aria-hidden="false">
                  <?php foreach ( $b_bullets as $bt ) : ?>
                    <?php if ( trim( $bt ) === '' ) continue; ?>
                    <li><?php echo esc_html( $bt ); ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>

            <?php endforeach; ?>
          <?php else : ?>
            <h3 class="sedi-cta-left-title"><?php echo esc_html( get_post_meta( $post_id, 'sedi_cta_title', true ) ?: __( 'Non trovi la tua città?', 'abcontact' ) ); ?></h3>

            <?php if ( ! empty( $cta_text ) ) : ?>
              <div class="sedi-cta-left-desc"><?php echo wp_kses_post( wpautop( $cta_text ) ); ?></div>
            <?php else : ?>
              <p class="sedi-cta-left-desc"><?php esc_html_e( 'Non preoccuparti! Operiamo in tutta Italia e possiamo raggiungerti ovunque tu sia. I nostri consulenti sono disponibili per appuntamenti anche presso la tua sede o abitazione.', 'abcontact' ); ?></p>
            <?php endif; ?>

            <ul class="sedi-cta-features" aria-hidden="false">
              <li><?php esc_html_e( 'Consulenza gratuita in tutta Italia', 'abcontact' ); ?></li>
              <li><?php esc_html_e( 'Sopralluoghi tecnici a domicilio', 'abcontact' ); ?></li>
              <li><?php esc_html_e( 'Supporto da remoto per pratiche amministrative', 'abcontact' ); ?></li>
              <li><?php esc_html_e( 'Installazioni certificate in tutta la penisola', 'abcontact' ); ?></li>
            </ul>
          <?php endif; ?>
        </div>

        <aside class="sedi-cta-right" role="region" aria-label="<?php echo esc_attr__( 'Call to action Sedi', 'abcontact' ); ?>">
          <div class="sedi-cta-card">
            <div class="sedi-cta-card-inner">
              <h4 class="sedi-cta-card-title"><?php echo esc_html( $cta_title ); ?></h4>

              <?php if ( ! empty( $cta_text ) ) : ?>
                <div class="sedi-cta-card-text--summary"><?php echo wp_kses_post( wpautop( $cta_text ) ); ?></div>
              <?php else : ?>
                <p class="sedi-cta-card-text--summary"><?php esc_html_e( 'Compila il form di richiesta consulenza e ti ricontatteremo entro 24 ore per fissare un appuntamento nella sede più comoda per te o direttamente a casa tua.', 'abcontact' ); ?></p>
              <?php endif; ?>

              <div class="sedi-cta-card-action">
                <a class="sedi-cta-button" href="<?php echo esc_url( $cta_link ); ?>">
                  <?php echo esc_html( $cta_label ); ?>
                </a>
              </div>

              <hr class="sedi-cta-sep" aria-hidden="true">

              <div class="sedi-cta-contact">
                <p class="contact-line"><strong><?php esc_html_e( 'Numero Verde:', 'abcontact' ); ?></strong> <span class="contact-val"><?php echo esc_html( $support_phone ); ?></span></p>
                <p class="contact-line"><strong><?php esc_html_e( 'Email:', 'abcontact' ); ?></strong> <a href="mailto:<?php echo esc_attr( $support_email ); ?>"><?php echo esc_html( $support_email ); ?></a></p>
              </div>
            </div>
          </div>
        </aside>

      </div>
    </section>

    <!-- Grid delle sedi -->
    <section class="sedi-grid" aria-label="<?php esc_attr_e( 'Elenco sedi', 'abcontact' ); ?>">
      <div class="sedi-grid-inner">
        <?php if ( empty( $sedi ) ) : ?>
          <div class="sede-empty"><?php esc_html_e( 'Non sono state ancora aggiunte sedi.', 'abcontact' ); ?></div>
        <?php else : ?>
          <?php foreach ( $sedi as $s ) :
            $city    = get_post_meta( $s->ID, 'sede_city', true );
            $address = get_post_meta( $s->ID, 'sede_address', true );
            $phone   = get_post_meta( $s->ID, 'sede_phone', true );
            $email   = get_post_meta( $s->ID, 'sede_email', true );
            $maps    = get_post_meta( $s->ID, 'sede_maps_url', true );
            $maps_link = $maps ? $maps : 'https://www.google.com/maps/search/' . rawurlencode( trim( $address . ' ' . $city ) );
          ?>
            <article class="sede-card" role="group" aria-labelledby="sede-title-<?php echo esc_attr( $s->ID ); ?>">
              <h4 id="sede-title-<?php echo esc_attr( $s->ID ); ?>"><?php echo esc_html( $city ?: get_the_title( $s ) ); ?></h4>
              <ul class="sede-contacts" aria-hidden="false">
                <?php if ( $address ) : ?><li class="sede-address"><?php echo esc_html( $address ); ?></li><?php endif; ?>
                <?php if ( $phone ) : ?><li class="sede-phone"><?php echo esc_html( $phone ); ?></li><?php endif; ?>
                <?php if ( $email ) : ?><li class="sede-email"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li><?php endif; ?>
              </ul>
              <p class="sede-actions"><a class="button" target="_blank" rel="noopener" href="<?php echo esc_url( $maps_link ); ?>"><?php esc_html_e( 'Apri in Google Maps', 'abcontact' ); ?></a></p>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

  </div>
</main>

<script id="abcontact-sedi-data" type="application/json">
<?php echo wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>
</script>

<?php
get_footer();
?>