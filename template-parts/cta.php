<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CTA (Footer) with inline form submit + PRG redirect (?cta_sent=1 / ?cta_error=1)
 * Recipient: get_option('admin_email')
 */

/* Prevent double render */
if ( did_action( 'abcontact_cta_prototype_rendered' ) ) {
    return;
}
do_action( 'abcontact_cta_prototype_rendered' );

/* Respect toggle / home always-on logic */
if ( function_exists( 'abcontact_should_render_cta_prototype' ) && ! abcontact_should_render_cta_prototype() ) {
    return;
}

/* Settings (title/subtitle/contacts) */
$opt = function_exists('abcontact_cta_prototype_get_settings')
    ? abcontact_cta_prototype_get_settings()
    : array();

$title = $opt['title'] ?? 'Pronto a risparmiare sulla tua bolletta?';
$sub   = $opt['subtitle'] ?? '';
$phone = $opt['phone'] ?? '';
$email = $opt['email'] ?? '';
$loc   = $opt['locations_url'] ?? home_url('/sedi/');

$phone_href = $phone ? 'tel:' . preg_replace('/\s+/', '', $phone) : '';
$email_href = $email ? 'mailto:' . $email : '';

/* ------------------------ Form handler (inline, with redirect) ------------------------ */
$cta_errors = array();
$cta_sent = false;

/* Show status from redirect GET params */
if ( isset($_GET['cta_sent']) && (string)$_GET['cta_sent'] === '1' ) {
    $cta_sent = true;
}
if ( isset($_GET['cta_error']) && (string)$_GET['cta_error'] === '1' ) {
    $cta_errors[] = __( 'Invio non riuscito. Controlla i campi e riprova più tardi.', 'theme-abcontact' );
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset( $_POST['abcontact_cta_form'] )
    && (string) $_POST['abcontact_cta_form'] === '1'
) {
    $back_url = wp_get_referer() ? wp_get_referer() : home_url('/');

    $nonce_ok = isset( $_POST['abcontact_cta_nonce'] ) && wp_verify_nonce( $_POST['abcontact_cta_nonce'], 'abcontact_cta_submit' );
    if ( ! $nonce_ok ) {
        wp_safe_redirect( add_query_arg( 'cta_error', '1', $back_url ) );
        exit;
    }

    // sanitize
    $first_name    = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name     = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $u_email       = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $u_phone       = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $customer_type = isset($_POST['customer_type']) ? sanitize_text_field($_POST['customer_type']) : '';
    $interest      = isset($_POST['interest']) ? sanitize_text_field($_POST['interest']) : '';
    $message       = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

    // validate required
    $has_error = false;
    if ( $first_name === '' ) $has_error = true;
    if ( $last_name === '' ) $has_error = true;
    if ( $u_email === '' || ! is_email( $u_email ) ) $has_error = true;
    if ( $u_phone === '' ) $has_error = true;
    if ( $customer_type === '' ) $has_error = true;
    if ( $interest === '' ) $has_error = true;

    if ( $has_error ) {
        wp_safe_redirect( add_query_arg( 'cta_error', '1', $back_url ) );
        exit;
    }

    // mail payload
    $to = get_option( 'admin_email' );
    $site = wp_parse_url( home_url(), PHP_URL_HOST );
    $subject = sprintf( '[%s] Nuova richiesta CTA', $site ? $site : 'Sito' );

    $body = implode("\n", array(
        "Nuova richiesta dalla CTA",
        "Pagina: " . ( is_singular() ? get_permalink() : home_url('/') ),
        "----",
        "Nome: {$first_name}",
        "Cognome: {$last_name}",
        "Email: {$u_email}",
        "Telefono: {$u_phone}",
        "Tipo: {$customer_type}",
        "Interesse: {$interest}",
        "Messaggio: " . ( $message ? $message : '-' ),
    ));

    $headers = array(
        'Reply-To: ' . $first_name . ' ' . $last_name . ' <' . $u_email . '>',
    );

    $sent = wp_mail( $to, $subject, $body, $headers );

    wp_safe_redirect( add_query_arg( $sent ? 'cta_sent' : 'cta_error', '1', $back_url ) );
    exit;
}
?>

<section id="cta-footer" class="cta-proto" aria-label="<?php echo esc_attr__( 'Richiedi una consulenza', 'theme-abcontact' ); ?>">
  <div class="cta-proto__bg">
    <div class="cta-proto__container container">
      <div class="cta-proto__grid">
        <div class="cta-proto__left">
          <h2 class="cta-proto__title"><?php echo esc_html( $title ); ?></h2>

          <?php if ( $sub ) : ?>
            <p class="cta-proto__subtitle"><?php echo esc_html( $sub ); ?></p>
          <?php endif; ?>

          <ul class="cta-proto__contacts" role="list">
            <?php if ( $phone && $phone_href ) : ?>
              <li class="cta-proto__contact">
                <span class="cta-proto__icon" aria-hidden="true">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M7.5 3.5l2.2 4.6-1.5 1.6c1.2 2.2 3 4 5.2 5.2l1.6-1.5 4.6 2.2-1 3.2c-.2.6-.8 1-1.4 1C10 20.9 3.1 14 3.5 5.6c0-.6.4-1.2 1-1.4l3-0.7z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                  </svg>
                </span>
                <a class="cta-proto__link" href="<?php echo esc_url( $phone_href ); ?>">
                  <?php echo esc_html( $phone ); ?>
                </a>
              </li>
            <?php endif; ?>

            <?php if ( $email && $email_href ) : ?>
              <li class="cta-proto__contact">
                <span class="cta-proto__icon" aria-hidden="true">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M4.5 7.5h15v9h-15v-9z" stroke="currentColor" stroke-width="1.7" />
                    <path d="M5 8l7 5 7-5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                  </svg>
                </span>
                <a class="cta-proto__link" href="<?php echo esc_url( $email_href ); ?>">
                  <?php echo esc_html( $email ); ?>
                </a>
              </li>
            <?php endif; ?>

            <?php if ( $loc ) : ?>
              <li class="cta-proto__contact">
                <span class="cta-proto__icon" aria-hidden="true">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M12 21s7-5.1 7-11a7 7 0 10-14 0c0 5.9 7 11 7 11z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                    <path d="M12 11.5a2 2 0 110-4 2 2 0 010 4z" stroke="currentColor" stroke-width="1.7"/>
                  </svg>
                </span>
                <a class="cta-proto__link" href="<?php echo esc_url( $loc ); ?>">
                  <?php echo esc_html__( 'Le nostre sedi', 'theme-abcontact' ); ?>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </div>

        <div class="cta-proto__right">
          <div class="cta-proto__card">
            <h3 class="cta-proto__form-title"><?php echo esc_html__( 'Richiedi una consulenza gratuita', 'theme-abcontact' ); ?></h3>

            <?php if ( $cta_sent ) : ?>
              <div class="cta-proto__notice cta-proto__notice--success" role="status">
                <?php echo esc_html__( 'Richiesta inviata! Ti ricontatteremo al più presto.', 'theme-abcontact' ); ?>
              </div>
            <?php endif; ?>

            <?php if ( ! empty( $cta_errors ) ) : ?>
              <div class="cta-proto__notice cta-proto__notice--error" role="alert">
                <?php echo esc_html( implode( ' ', $cta_errors ) ); ?>
              </div>
            <?php endif; ?>

            <form class="cta-proto__form" method="post" action="">
              <?php wp_nonce_field( 'abcontact_cta_submit', 'abcontact_cta_nonce' ); ?>
              <input type="hidden" name="abcontact_cta_form" value="1">

              <div class="cta-proto__row cta-proto__row--2">
                <input class="cta-proto__input" type="text" name="first_name" placeholder="Nome" required>
                <input class="cta-proto__input" type="text" name="last_name" placeholder="Cognome" required>
              </div>

              <div class="cta-proto__row">
                <input class="cta-proto__input" type="email" name="email" placeholder="Email" required>
              </div>

              <div class="cta-proto__row">
                <input class="cta-proto__input" type="tel" name="phone" placeholder="Telefono" required>
              </div>

              <div class="cta-proto__row">
                <select class="cta-proto__input" name="customer_type" required>
                  <option value="" selected disabled>Seleziona: Privato o Azienda</option>
                  <option value="privato">Privato</option>
                  <option value="azienda">Azienda</option>
                </select>
              </div>

              <div class="cta-proto__row">
                <select class="cta-proto__input" name="interest" required>
                  <option value="" selected disabled>Sono interessato a...</option>
                  <option value="luce">Luce</option>
                  <option value="gas">Gas</option>
                  <option value="internet">Internet</option>
                  <option value="fotovoltaico">Fotovoltaico</option>
                  <option value="efficientamento">Efficientamento Energetico</option>
                  <option value="altro">Altro</option>
                </select>
              </div>

              <div class="cta-proto__row">
                <textarea class="cta-proto__input cta-proto__textarea" name="message" placeholder="Messaggio (facoltativo)" rows="4"></textarea>
              </div>

              <button class="cta-proto__submit" type="submit">
                <span><?php echo esc_html__( 'Invia richiesta', 'theme-abcontact' ); ?></span>
                <span class="cta-proto__submit-arrow" aria-hidden="true">→</span>
              </button>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>