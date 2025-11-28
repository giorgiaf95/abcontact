<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<section class="front-cta" aria-label="<?php esc_attr_e( 'Call to action', 'theme-abcontact' ); ?>">
  <div class="container">
    <div class="cta-inner" role="region" aria-labelledby="cta-title">
      <h2 id="cta-title" class="cta-title"><?php esc_html_e( 'Hai un progetto?', 'theme-abcontact' ); ?></h2>
      <p class="cta-sub"><?php esc_html_e( 'Contattaci per una consulenza gratuita e una proposta su misura.', 'theme-abcontact' ); ?></p>

      <div class="cta-actions" style="text-align:center;">
        <?php
        // Use the component button (renders a <button> element to be targeted by your JS modal)
        get_template_part( 'template-parts/components/button', null, array(
            'label' => __( 'Richiedi un preventivo', 'theme-abcontact' ),
            'type'  => 'button',
            'class' => 'cta-button',
            'attrs' => array( 'id' => 'open-cta-modal', 'data-abcontact-modal-open' => '' )
        ) );
        ?>
      </div>
    </div>
  </div>
</section>

<!-- Modal: empty container where the form shortcode will be inserted later -->
<div id="abcontact-cta-modal" class="abcontact-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="abcontact-cta-modal-title" tabindex="-1">
  <div class="abcontact-modal__overlay" data-abcontact-modal-close></div>

  <div class="abcontact-modal__panel" role="document">
    <button class="abcontact-modal__close" aria-label="<?php esc_attr_e( 'Chiudi', 'abcontact' ); ?>" data-abcontact-modal-close>&times;</button>

    <header class="abcontact-modal__header">
      <h3 id="abcontact-cta-modal-title"><?php esc_html_e( 'Richiedi una consulenza', 'abcontact' ); ?></h3>
    </header>

    <div class="abcontact-modal__body">
      <!-- placeholder per shortcode -->
      <div id="abcontact-cta-shortcode-placeholder" data-shortcode=""></div>
    </div>
  </div>
</div>