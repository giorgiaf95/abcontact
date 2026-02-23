<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( function_exists( 'abcontact_should_render_cta_prototype' ) && ! abcontact_should_render_cta_prototype() ) {
    // Safety: if partial is included manually somewhere, still respect the toggle.
    return;
}

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

?>
<section class="cta-proto" aria-label="<?php echo esc_attr__( 'Richiedi una consulenza', 'theme-abcontact' ); ?>">
  <div class="cta-proto__container container">
    <div class="cta-proto__grid">
      <div class="cta-proto__left">
        <h2 class="cta-proto__title">
          <?php echo esc_html( $title ); ?>
        </h2>

        <?php if ( $sub ) : ?>
          <p class="cta-proto__subtitle"><?php echo esc_html( $sub ); ?></p>
        <?php endif; ?>

        <ul class="cta-proto__contacts" role="list">
          <?php if ( $phone && $phone_href ) : ?>
            <li class="cta-proto__contact">
              <span class="cta-proto__icon" aria-hidden="true">
                <!-- phone -->
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
                <!-- mail -->
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
                <!-- location -->
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

          <form class="cta-proto__form" method="post" action="#">
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

            <!-- NEW: privato/azienda required -->
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

            <!-- Qui poi agganciamo la logica invio reale (email, CRM, etc.) -->
          </form>
        </div>
      </div>
    </div>
  </div>
</section>