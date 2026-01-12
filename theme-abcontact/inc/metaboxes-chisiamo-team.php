<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Metabox: "Il nostro team" per la pagina Chi Siamo
 * - salva meta _cs_team (JSON array)
 * - enqueues admin script for repeater/media
 */

/* Register metabox */
add_action( 'add_meta_boxes', 'ab_chisiamo_team_register_metabox' );
function ab_chisiamo_team_register_metabox() {
    add_meta_box(
        'ab_chisiamo_team',
        __( 'Chi Siamo — Il nostro team', 'abcontact' ),
        'ab_chisiamo_team_render',
        'page',
        'normal',
        'high'
    );
}

function ab_chisiamo_team_render( $post ) {
    // show only for page-chi-siamo template or slug 'chi-siamo'
    $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
    $tpl_base = $tpl ? basename( $tpl ) : '';
    $slug = $post->post_name;
    if ( $tpl_base !== 'page-chi-siamo.php' && $slug !== 'chi-siamo' ) {
        echo '<p>' . esc_html__( 'Questo metabox è disponibile solo per la pagina "Chi Siamo".', 'abcontact' ) . '</p>';
        return;
    }

    wp_nonce_field( 'ab_chisiamo_team_save', 'ab_chisiamo_team_nonce' );

    // load existing data
    $raw = get_post_meta( $post->ID, '_cs_team', true );
    $items = array();
    if ( $raw ) {
        $decoded = json_decode( $raw, true );
        if ( is_array( $decoded ) ) $items = $decoded;
    }

    // minimal inline styles for admin readability
    ?>
    <style>
      .ab-team-item{ border:1px solid #eee; padding:10px; margin-bottom:10px; border-radius:6px; background:#fff; }
      .ab-team-handle{ display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; cursor:move; color:#0b5fff; font-weight:600; }
      .ab-team-field{ width:100%; box-sizing:border-box; padding:6px 8px; margin-bottom:8px; }
      .ab-team-preview img{ max-width:140px; height:auto; border-radius:999px; display:block; margin-top:8px; }
    </style>

    <p class="description"><?php esc_html_e( 'Aggiungi i membri del team. Puoi riordinare gli elementi trascinando la handle.', 'abcontact' ); ?></p>

    <div class="ab-team-repeater" data-repeater>
      <?php if ( ! empty( $items ) ) :
        foreach ( $items as $i => $it ) :
          $name = isset( $it['name'] ) ? $it['name'] : '';
          $role = isset( $it['role'] ) ? $it['role'] : '';
          $place = isset( $it['place'] ) ? $it['place'] : '';
          $img_id = isset( $it['image_id'] ) ? intval( $it['image_id'] ) : 0;
      ?>
        <div class="ab-team-item" data-index="<?php echo esc_attr( $i ); ?>">
          <div class="ab-team-handle">
            <span class="label"><?php echo esc_html( $name ? $name : sprintf( __( 'Membro %d', 'abcontact' ), $i+1 ) ); ?></span>
            <div>
              <button type="button" class="button ab-team-remove"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
            </div>
          </div>

          <input class="ab-team-field" type="text" name="cs_team[<?php echo esc_attr( $i ); ?>][name]" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Nome Cognome', 'abcontact' ); ?>">

          <input class="ab-team-field" type="text" name="cs_team[<?php echo esc_attr( $i ); ?>][role]" value="<?php echo esc_attr( $role ); ?>" placeholder="<?php esc_attr_e( 'Ruolo', 'abcontact' ); ?>">

          <input class="ab-team-field" type="text" name="cs_team[<?php echo esc_attr( $i ); ?>][place]" value="<?php echo esc_attr( $place ); ?>" placeholder="<?php esc_attr_e( 'Sede', 'abcontact' ); ?>">

          <input type="hidden" class="ab-team-image-id" name="cs_team[<?php echo esc_attr( $i ); ?>][image_id]" value="<?php echo esc_attr( $img_id ); ?>">
          <p>
            <button type="button" class="button ab-team-image-select"><?php esc_html_e( 'Seleziona foto', 'abcontact' ); ?></button>
            <button type="button" class="button ab-team-image-remove"><?php esc_html_e( 'Rimuovi foto', 'abcontact' ); ?></button>
          </p>
          <div class="ab-team-preview">
            <?php if ( $img_id ) echo wp_get_attachment_image( $img_id, 'thumbnail' ); ?>
          </div>
        </div>
      <?php endforeach; endif; ?>

      <!-- template -->
      <template id="ab-team-template">
        <div class="ab-team-item" data-index="__index__">
          <div class="ab-team-handle">
            <span class="label"><?php esc_html_e( 'Nuovo membro', 'abcontact' ); ?></span>
            <div>
              <button type="button" class="button ab-team-remove"><?php esc_html_e( 'Rimuovi', 'abcontact' ); ?></button>
            </div>
          </div>

          <input class="ab-team-field" type="text" name="cs_team[__index__][name]" value="" placeholder="<?php esc_attr_e( 'Nome Cognome', 'abcontact' ); ?>">
          <input class="ab-team-field" type="text" name="cs_team[__index__][role]" value="" placeholder="<?php esc_attr_e( 'Ruolo', 'abcontact' ); ?>">
          <input class="ab-team-field" type="text" name="cs_team[__index__][place]" value="" placeholder="<?php esc_attr_e( 'Sede', 'abcontact' ); ?>">

          <input type="hidden" class="ab-team-image-id" name="cs_team[__index__][image_id]" value="">
          <p>
            <button type="button" class="button ab-team-image-select"><?php esc_html_e( 'Seleziona foto', 'abcontact' ); ?></button>
            <button type="button" class="button ab-team-image-remove"><?php esc_html_e( 'Rimuovi foto', 'abcontact' ); ?></button>
          </p>
          <div class="ab-team-preview"></div>
        </div>
      </template>

      <p><button type="button" class="button button-primary" id="ab-team-add"><?php esc_html_e( 'Aggiungi membro', 'abcontact' ); ?></button></p>
    </div>

    <?php
}

/* Save handler */
add_action( 'save_post', 'ab_chisiamo_team_save' );
function ab_chisiamo_team_save( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['ab_chisiamo_team_nonce'] ) || ! wp_verify_nonce( $_POST['ab_chisiamo_team_nonce'], 'ab_chisiamo_team_save' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['cs_team'] ) && is_array( $_POST['cs_team'] ) ) {
        $raw = $_POST['cs_team'];
        $clean = array();
        foreach ( $raw as $item ) {
            $name = isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '';
            $role = isset( $item['role'] ) ? sanitize_text_field( $item['role'] ) : '';
            $place = isset( $item['place'] ) ? sanitize_text_field( $item['place'] ) : '';
            $image_id = isset( $item['image_id'] ) ? intval( $item['image_id'] ) : 0;
            if ( $name === '' && $role === '' && $place === '' && $image_id === 0 ) {
                continue;
            }
            $clean[] = array(
                'name' => $name,
                'role' => $role,
                'place' => $place,
                'image_id' => $image_id,
            );
        }
        update_post_meta( $post_id, '_cs_team', wp_json_encode( $clean ) );
    } else {
        // If no team posted, leave existing data untouched OR delete depending on preference
        // delete_post_meta( $post_id, '_cs_team' );
    }
}

/* Enqueue admin script for the repeater + media */
add_action( 'admin_enqueue_scripts', 'ab_chisiamo_team_admin_assets' );
function ab_chisiamo_team_admin_assets( $hook ) {
    global $post;
    if ( ( $hook !== 'post.php' && $hook !== 'post-new.php' ) || ! $post || $post->post_type !== 'page' ) {
        return;
    }
    // limit to Chi Siamo page/template
    $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
    $tpl_base = $tpl ? basename( $tpl ) : '';
    if ( $tpl_base !== 'page-chi-siamo.php' && $post->post_name !== 'chi-siamo' ) {
        return;
    }

    wp_enqueue_media();
    $theme_uri = get_stylesheet_directory_uri();
    $js = get_stylesheet_directory() . '/assets/js/admin-chisiamo-team.js';
    if ( file_exists( $js ) ) {
        wp_enqueue_script( 'ab-chisiamo-team-admin', $theme_uri . '/assets/js/admin-chisiamo-team.js', array( 'jquery' ), filemtime( $js ), true );
    }
    wp_localize_script( 'ab-chisiamo-team-admin', 'abChiSiamoTeam', array(
        'removeConfirm' => esc_html__( 'Rimuovere questo membro?', 'abcontact' ),
    ));
}

/* Enqueue front-end assets (only on Chi Siamo page) */
add_action( 'wp_enqueue_scripts', 'ab_chisiamo_team_front_assets' );
function ab_chisiamo_team_front_assets() {
    if ( is_admin() ) return;
    if ( ! is_page_template( 'page-chi-siamo.php' ) && ! is_page( 'chi-siamo' ) ) return;

    $theme_uri = get_stylesheet_directory_uri();
    $css = get_stylesheet_directory() . '/assets/css/team-carousel.css';
    if ( file_exists( $css ) ) {
        wp_enqueue_style( 'ab-team-carousel', $theme_uri . '/assets/css/team-carousel.css', array(), filemtime( $css ) );
    }
    $js = get_stylesheet_directory() . '/assets/js/team-carousel.js';
    if ( file_exists( $js ) ) {
        wp_enqueue_script( 'ab-team-carousel', $theme_uri . '/assets/js/team-carousel.js', array(), filemtime( $js ), true );
    }
}