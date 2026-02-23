<?php
if ( ! defined( 'ABSPATH' ) ) exit;

const ABCONTACT_CTA_OPTION = 'abcontact_cta_prototype_settings';

function abcontact_cta_prototype_defaults() {
    return array(
        'title'       => 'Pronto a risparmiare sulla tua bolletta?',
        'subtitle'    => "Contattaci per una consulenza gratuita e senza impegno. Analizziamo le tue utenze e troviamo la soluzione migliore per te.",
        'phone'       => '+39 000 000 0000',
        'email'       => 'info@abcontact.it',
        'locations_url' => home_url( '/sedi/' ),
    );
}

function abcontact_cta_prototype_get_settings() {
    $saved = get_option( ABCONTACT_CTA_OPTION, array() );
    if ( ! is_array( $saved ) ) $saved = array();
    return wp_parse_args( $saved, abcontact_cta_prototype_defaults() );
}

function abcontact_cta_prototype_sanitize( $input ) {
    $def = abcontact_cta_prototype_defaults();
    if ( ! is_array( $input ) ) return $def;

    return array(
        'title'         => isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : $def['title'],
        'subtitle'      => isset( $input['subtitle'] ) ? sanitize_text_field( $input['subtitle'] ) : $def['subtitle'],
        'phone'         => isset( $input['phone'] ) ? sanitize_text_field( $input['phone'] ) : $def['phone'],
        'email'         => isset( $input['email'] ) ? sanitize_email( $input['email'] ) : $def['email'],
        'locations_url' => isset( $input['locations_url'] ) ? esc_url_raw( $input['locations_url'] ) : $def['locations_url'],
    );
}

add_action( 'admin_menu', function () {
    add_options_page(
        __( 'CTA (Prototipo)', 'theme-abcontact' ),
        __( 'CTA (Prototipo)', 'theme-abcontact' ),
        'manage_options',
        'abcontact-cta-prototype',
        'abcontact_cta_prototype_render_page'
    );
} );

add_action( 'admin_init', function () {
    register_setting(
        'abcontact_cta_prototype_group',
        ABCONTACT_CTA_OPTION,
        array(
            'type'              => 'array',
            'sanitize_callback' => 'abcontact_cta_prototype_sanitize',
            'default'           => abcontact_cta_prototype_defaults(),
        )
    );
} );

function abcontact_cta_prototype_render_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $opt = abcontact_cta_prototype_get_settings();
    ?>
    <div class="wrap">
      <h1><?php esc_html_e( 'CTA (Prototipo) – Impostazioni', 'theme-abcontact' ); ?></h1>
      <form method="post" action="options.php">
        <?php settings_fields( 'abcontact_cta_prototype_group' ); ?>

        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><?php esc_html_e( 'Titolo', 'theme-abcontact' ); ?></th>
            <td>
              <input type="text" class="large-text"
                name="<?php echo esc_attr( ABCONTACT_CTA_OPTION ); ?>[title]"
                value="<?php echo esc_attr( $opt['title'] ); ?>">
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e( 'Sottotitolo', 'theme-abcontact' ); ?></th>
            <td>
              <textarea class="large-text" rows="3"
                name="<?php echo esc_attr( ABCONTACT_CTA_OPTION ); ?>[subtitle]"><?php echo esc_textarea( $opt['subtitle'] ); ?></textarea>
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e( 'Telefono', 'theme-abcontact' ); ?></th>
            <td>
              <input type="text" class="regular-text"
                name="<?php echo esc_attr( ABCONTACT_CTA_OPTION ); ?>[phone]"
                value="<?php echo esc_attr( $opt['phone'] ); ?>">
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e( 'Email', 'theme-abcontact' ); ?></th>
            <td>
              <input type="email" class="regular-text"
                name="<?php echo esc_attr( ABCONTACT_CTA_OPTION ); ?>[email]"
                value="<?php echo esc_attr( $opt['email'] ); ?>">
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e( 'Link “Le nostre sedi”', 'theme-abcontact' ); ?></th>
            <td>
              <input type="url" class="large-text"
                name="<?php echo esc_attr( ABCONTACT_CTA_OPTION ); ?>[locations_url]"
                value="<?php echo esc_attr( $opt['locations_url'] ); ?>">
            </td>
          </tr>
        </table>

        <?php submit_button( __( 'Salva', 'theme-abcontact' ) ); ?>
      </form>
    </div>
    <?php
}