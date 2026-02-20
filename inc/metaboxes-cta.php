<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Metabox per la CTA
 * Meta keys:
 *  - cta_title
 *  - cta_subtitle
 *  - cta_button_label
 *  - cta_button_link
 *  - cta_button_color
 *  - cta_modal
 *
 * Visibile su 'page' e 'post' (estendibile).
 */

add_action( 'add_meta_boxes', function() {
    $screens = array( 'page', 'post' );
    foreach ( $screens as $screen ) {
        add_meta_box(
            'ab_cta_metabox',
            __( 'Front CTA', 'abcontact' ),
            'abcontact_render_cta_metabox',
            $screen,
            'normal',
            'high'
        );
    }
} );

function abcontact_render_cta_metabox( $post ) {
    wp_nonce_field( 'abcontact_cta_save', 'abcontact_cta_nonce' );

    $fields = array(
        'cta_title'        => get_post_meta( $post->ID, 'cta_title', true ),
        'cta_subtitle'     => get_post_meta( $post->ID, 'cta_subtitle', true ),
        'cta_button_label' => get_post_meta( $post->ID, 'cta_button_label', true ),
        'cta_button_link'  => get_post_meta( $post->ID, 'cta_button_link', true ),
        'cta_button_color' => get_post_meta( $post->ID, 'cta_button_color', true ),
        'cta_modal'        => get_post_meta( $post->ID, 'cta_modal', true ),
    );
    ?>
    <p>
      <label for="cta_title"><strong><?php esc_html_e( 'Titolo CTA', 'abcontact' ); ?></strong></label><br>
      <input type="text" id="cta_title" name="cta_title" value="<?php echo esc_attr( $fields['cta_title'] ); ?>" style="width:100%;">
    </p>

    <p>
      <label for="cta_subtitle"><strong><?php esc_html_e( 'Sottotitolo / descrizione', 'abcontact' ); ?></strong></label><br>
      <textarea id="cta_subtitle" name="cta_subtitle" rows="3" style="width:100%;"><?php echo esc_textarea( $fields['cta_subtitle'] ); ?></textarea>
    </p>

    <p>
      <label for="cta_button_label"><strong><?php esc_html_e( 'Testo bottone', 'abcontact' ); ?></strong></label><br>
      <input type="text" id="cta_button_label" name="cta_button_label" value="<?php echo esc_attr( $fields['cta_button_label'] ); ?>" style="width:60%;">
    </p>

    <p>
      <label for="cta_button_link"><strong><?php esc_html_e( 'Link bottone', 'abcontact' ); ?></strong></label><br>
      <input type="url" id="cta_button_link" name="cta_button_link" value="<?php echo esc_attr( $fields['cta_button_link'] ); ?>" style="width:100%;">
      <small class="description"><?php esc_html_e( 'Inserisci path relativo (/pagina) o URL assoluto (https://...)', 'abcontact' ); ?></small>
    </p>

    <p>
      <label for="cta_button_color"><strong><?php esc_html_e( 'Colore pulsante (hex o css color)', 'abcontact' ); ?></strong></label><br>
      <input type="text" id="cta_button_color" name="cta_button_color" value="<?php echo esc_attr( $fields['cta_button_color'] ); ?>" placeholder="#0b5fff" style="width:30%;">
      <small class="description"><?php esc_html_e( 'Accetta #hex, nomi colore, rgb/rgba/hsl', 'abcontact' ); ?></small>
    </p>

    <p>
      <label for="cta_modal">
        <input type="checkbox" id="cta_modal" name="cta_modal" value="1" <?php checked( $fields['cta_modal'], '1' ); ?>>
        <?php esc_html_e( 'Apri il form in modal (se disponibile)', 'abcontact' ); ?>
      </label>
    </p>

    <p class="description" style="margin-top:10px;color:#666;">
      <?php esc_html_e( 'Questi campi saranno letti dalla partial CTA. Se li lasci vuoti la CTA non verrà mostrata in frontend.', 'abcontact' ); ?>
    </p>
    <?php
}

add_action( 'save_post', function( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['abcontact_cta_nonce'] ) || ! wp_verify_nonce( $_POST['abcontact_cta_nonce'], 'abcontact_cta_save' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $map = array(
        'cta_title'        => 'sanitize_text_field',
        'cta_subtitle'     => 'wp_kses_post',
        'cta_button_label' => 'sanitize_text_field',
        'cta_button_link'  => 'esc_url_raw',
        'cta_button_color' => 'sanitize_text_field',
    );

    foreach ( $map as $key => $sanitize ) {
        if ( isset( $_POST[ $key ] ) && $_POST[ $key ] !== '' ) {
            $val = $_POST[ $key ];
            $clean = call_user_func( $sanitize, $val );
            update_post_meta( $post_id, $key, $clean );
        } else {
            delete_post_meta( $post_id, $key );
        }
    }

    $modal = isset( $_POST['cta_modal'] ) && $_POST['cta_modal'] ? '1' : '';
    if ( $modal ) {
        update_post_meta( $post_id, 'cta_modal', '1' );
    } else {
        delete_post_meta( $post_id, 'cta_modal' );
    }
}, 20 );