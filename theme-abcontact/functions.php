<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ============================ Config ============================ */
if ( ! defined( 'SERVICE_GROUP_IMG_WIDTH' ) ) {
    define( 'SERVICE_GROUP_IMG_WIDTH', 700 );
}
if ( ! defined( 'SERVICE_GROUP_IMG_HEIGHT' ) ) {
    define( 'SERVICE_GROUP_IMG_HEIGHT', 450 );
}
if ( ! defined( 'SERVICE_GROUP_IMG_NAME' ) ) {
    define( 'SERVICE_GROUP_IMG_NAME', 'service_group_fixed' );
}

/* ============================ Includes ============================ */
if ( ! defined( 'THEME_INC_DIR' ) ) {
    define( 'THEME_INC_DIR', get_stylesheet_directory() . '/inc' );
}

$inc_files = array(
    get_stylesheet_directory() . '/inc/setup.php',
    get_stylesheet_directory() . '/inc/enqueue.php',
    get_stylesheet_directory() . '/inc/news.php',
);

foreach ( $inc_files as $file ) {
    if ( file_exists( $file ) ) {
        require_once $file;
    }
}

$news_ajax_path = get_stylesheet_directory() . '/inc/news-ajax.php';
if ( file_exists( $news_ajax_path ) ) {
    require_once $news_ajax_path;
}

/* ============================ Image sizes ============================ */
function abcontact_register_image_sizes() {
    add_image_size( SERVICE_GROUP_IMG_NAME, (int) SERVICE_GROUP_IMG_WIDTH, (int) SERVICE_GROUP_IMG_HEIGHT, true );

    add_image_size( 'chi_siamo_image', 760, 500, true ); 
    add_image_size( 'chi_siamo_icon', 120, 120, true );  

    add_image_size( 'footer-logo', 300, 80, false ); 
}
add_action( 'after_setup_theme', 'abcontact_register_image_sizes' );

function abcontact_print_image_size_css_var() {
    if ( is_admin() ) {
        return;
    }
    $w = (int) SERVICE_GROUP_IMG_WIDTH;
    $h = (int) SERVICE_GROUP_IMG_HEIGHT;
    echo "<style id=\"abcontact-service-image-size-vars\" type=\"text/css\">:root{ --service-group-image-max: {$w}px; --service-group-image-height: {$h}px; }</style>\n";
}
add_action( 'wp_head', 'abcontact_print_image_size_css_var', 1 );

/* ============================ Light Enqueue helpers (header) ============================ */
function abcontact_enqueue_header_assets() {
    if ( is_admin() ) {
        return;
    }
    $theme_dir  = get_stylesheet_directory();
    $theme_uri  = get_stylesheet_directory_uri();
    $header_js_path = $theme_dir . '/assets/js/header.js';

    if ( file_exists( $header_js_path ) ) {
        wp_enqueue_script( 'theme-header', $theme_uri . '/assets/js/header.js', array(), filemtime( $header_js_path ), true );
    }
}
add_action( 'wp_enqueue_scripts', 'abcontact_enqueue_header_assets', 20 );

/* Optional header color toggles */
function abcontact_enqueue_header_color_switch() {
    if ( is_admin() ) {
        return;
    }
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    $css_rel = '/assets/css/header-colors.css';
    $js_rel  = '/assets/js/header-toggle.js';
    $css_path = $theme_dir . $css_rel;
    $js_path  = $theme_dir . $js_rel;

    $possible_main_handles = array( 'theme-main','main-style','theme-style','main-css','style','styles','app','bundle' );
    $dependency = array();
    foreach ( $possible_main_handles as $h ) {
        if ( wp_style_is( $h, 'registered' ) ) {
            $dependency = array( $h );
            break;
        }
    }

    if ( file_exists( $css_path ) ) {
        wp_enqueue_style( 'theme-header-colors', $theme_uri . $css_rel, $dependency, filemtime( $css_path ) );
    }
    if ( file_exists( $js_path ) ) {
        wp_enqueue_script( 'theme-header-toggle', $theme_uri . $js_rel, array(), filemtime( $js_path ), true );
    }
}
add_action( 'wp_enqueue_scripts', 'abcontact_enqueue_header_color_switch', 25 );

/* ======================== Accessibility helpers / menus / sidebars ======================== */
function abcontact_safe_menu_heading_handler( $atts, $item, $args ) {
    if ( is_admin() ) {
        return $atts;
    }
    $classes = (array) $item->classes;
    if ( ! in_array( 'menu-heading', $classes, true ) && ! in_array( 'column-title', $classes, true ) ) {
        return $atts;
    }

    $href = '';
    if ( isset( $item->url ) ) {
        $href = trim( (string) $item->url );
    } elseif ( isset( $atts['href'] ) ) {
        $href = trim( (string) $atts['href'] );
    }

    $is_placeholder = ( $href === '#' || $href === '' || stripos( $href, 'javascript:void(0)' ) !== false );

    if ( $is_placeholder ) {
        $atts['data-menu-heading'] = '1';
        $atts['aria-disabled']     = 'true';
        $atts['tabindex']          = '-1';
        $atts['role']              = 'presentation';
        if ( isset( $atts['class'] ) ) {
            if ( strpos( $atts['class'], 'menu-heading__anchor' ) === false ) {
                $atts['class'] = $atts['class'] . ' menu-heading__anchor';
            }
        } else {
            $atts['class'] = 'menu-heading__anchor';
        }
    } else {
        if ( isset( $atts['aria-disabled'] ) ) {
            unset( $atts['aria-disabled'] );
        }
        if ( isset( $atts['tabindex'] ) && $atts['tabindex'] === '-1' ) {
            unset( $atts['tabindex'] );
        }
        if ( isset( $atts['role'] ) && $atts['role'] === 'presentation' ) {
            unset( $atts['role'] );
        }
        if ( isset( $atts['data-menu-heading'] ) ) {
            unset( $atts['data-menu-heading'] );
        }
    }

    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'abcontact_safe_menu_heading_handler', 10, 3 );

function abcontact_register_menus() {
    register_nav_menus( array(
        'primary'         => __( 'Primary Menu', 'abcontact' ),
        'footer'          => __( 'Footer Links', 'abcontact' ),
        'footer_services' => __( 'Footer Services', 'abcontact' ),
        'social'          => __( 'Social Menu', 'abcontact' ),
    ) );
}
add_action( 'after_setup_theme', 'abcontact_register_menus' );

function abcontact_register_sidebars() {
    $sidebars = array(
        'footer-1' => __( 'Footer Column 1', 'abcontact' ),
        'footer-2' => __( 'Footer Column 2', 'abcontact' ),
        'footer-3' => __( 'Footer Column 3', 'abcontact' ),
    );
    foreach ( $sidebars as $id => $name ) {
        register_sidebar( array(
            'name'          => $name,
            'id'            => $id,
            'before_widget' => '<div id="%1$s" class="widget %2$s footer-widget">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
    }
}
add_action( 'widgets_init', 'abcontact_register_sidebars' );

/* ============================ Customizer: Footer settings ============================ */
function abcontact_customize_register( $wp_customize ) {
    $wp_customize->add_section( 'abcontact_footer_section', array(
        'title'       => __( 'Footer settings', 'abcontact' ),
        'priority'    => 160,
        'description' => __( 'Contenuti e contatti nel footer', 'abcontact' ),
    ) );

    $wp_customize->add_setting( 'footer_about_text', array(
        'default'           => __( 'Testo breve con slogan o call to action inerente ai nostri servizi e/o obiettivi', 'abcontact' ),
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'footer_about_text', array(
        'label'    => __( 'About (testo)', 'abcontact' ),
        'section'  => 'abcontact_footer_section',
        'type'     => 'textarea',
    ) );

    $wp_customize->add_setting( 'footer_email', array(
        'default'           => 'info@example.com',
        'sanitize_callback' => 'sanitize_email',
    ) );
    $wp_customize->add_control( 'footer_email', array(
        'label'    => __( 'E-mail di contatto', 'abcontact' ),
        'section'  => 'abcontact_footer_section',
        'type'     => 'email',
    ) );

    $wp_customize->add_setting( 'footer_phone', array(
        'default'           => '+39 000 000 0000',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'footer_phone', array(
        'label'    => __( 'Telefono', 'abcontact' ),
        'section'  => 'abcontact_footer_section',
        'type'     => 'text',
    ) );

    $wp_customize->add_setting( 'footer_address', array(
        'default'           => __( 'Indirizzo, città', 'abcontact' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'footer_address', array(
        'label'    => __( 'Indirizzo', 'abcontact' ),
        'section'  => 'abcontact_footer_section',
        'type'     => 'text',
    ) );

    $wp_customize->add_setting( 'footer_copyright', array(
        'default'           => sprintf( '&copy; %1$s %2$s. %3$s', date_i18n( 'Y' ), get_bloginfo( 'name' ), __( 'Tutti i diritti riservati.', 'abcontact' ) ),
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'footer_copyright', array(
        'label'    => __( 'Copyright (HTML allowed)', 'abcontact' ),
        'section'  => 'abcontact_footer_section',
        'type'     => 'textarea',
    ) );

    // Social icons
    for ( $i = 1; $i <= 4; $i++ ) {
        $wp_customize->add_setting( "footer_social_{$i}_image", array(
            'default'           => '',
            'sanitize_callback' => 'absint',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );
        $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, "footer_social_{$i}_image", array(
            'label'    => sprintf( __( 'Social %d - Icona (carica immagine)', 'abcontact' ), $i ),
            'section'  => 'abcontact_footer_section',
            'settings' => "footer_social_{$i}_image",
            'mime_type' => 'image',
        ) ) );

        $wp_customize->add_setting( "footer_social_{$i}_url", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );
        $wp_customize->add_control( "footer_social_{$i}_url", array(
            'label'    => sprintf( __( 'Social %d - Link', 'abcontact' ), $i ),
            'section'  => 'abcontact_footer_section',
            'type'     => 'url',
        ) );
    }

    // Footer logo
    $wp_customize->add_setting( 'footer_logo', array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'footer_logo', array(
        'label'       => __( 'Footer Logo', 'abcontact' ),
        'section'     => 'abcontact_footer_section',
        'settings'    => 'footer_logo',
        'mime_type'   => 'image',
        'description' => __( 'Carica un logo da usare solo nel footer. Se vuoto verrà usato il logo principale del sito.', 'abcontact' ),
    ) ) );

}
add_action( 'customize_register', 'abcontact_customize_register' );

add_action( 'admin_init', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( get_option( 'abcontact_migrated_footer_images' ) ) {
        return;
    }

    $theme = get_stylesheet();
    $option = 'theme_mods_' . $theme;
    $mods = get_option( $option, array() );
    $updated = false;

    if ( ! empty( $mods['footer_logo'] ) && ! is_numeric( $mods['footer_logo'] ) ) {
        $id = attachment_url_to_postid( $mods['footer_logo'] );
        if ( $id ) { $mods['footer_logo'] = (int) $id; $updated = true; }
    }

    for ( $i = 1; $i <= 4; $i++ ) {
        $key = "footer_social_{$i}_image";
        if ( ! empty( $mods[ $key ] ) && ! is_numeric( $mods[ $key ] ) ) {
            $id = attachment_url_to_postid( $mods[ $key ] );
            if ( $id ) { $mods[ $key ] = (int) $id; $updated = true; }
        }
    }

    if ( $updated ) {
        update_option( $option, $mods );
        update_option( 'abcontact_migrated_footer_images', 1 );
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>abcontact: migrazione immagini footer completata (URL -> attachment ID).</p></div>';
        } );
    } else {
        update_option( 'abcontact_migrated_footer_images', 1 );
    }
}, 20 );

/* ============================ Service Page Metabox ============================ */
function abcontact_register_service_metabox() {
    add_meta_box( 'ab_service_fields', __( 'Campi Service Page', 'abcontact' ), 'ab_service_fields_render', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'abcontact_register_service_metabox' );

function ab_service_fields_render( $post ) {
    wp_nonce_field( 'ab_service_fields_save', 'ab_service_fields_nonce' );
    $post_id = isset( $post->ID ) ? (int) $post->ID : 0;

    // Prefer new keys, fallback to legacy keys to avoid losing existing content
    $service_box_heading1 = get_post_meta( $post_id, 'service_box_heading1', true );
    if ( ! $service_box_heading1 ) {
        $service_box_heading1 = get_post_meta( $post_id, 'service_group1_title', true );
    }

    $service_box_text1 = get_post_meta( $post_id, 'service_box_text1', true );
    if ( ! $service_box_text1 ) {
        $service_box_text1 = get_post_meta( $post_id, 'service_group1_text', true );
    }

    $service_box_heading2 = get_post_meta( $post_id, 'service_box_heading2', true );
    if ( ! $service_box_heading2 ) {
        $service_box_heading2 = get_post_meta( $post_id, 'service_group2_title', true );
    }

    $service_box_text2 = get_post_meta( $post_id, 'service_box_text2', true );
    if ( ! $service_box_text2 ) {
        $service_box_text2 = get_post_meta( $post_id, 'service_group2_text', true );
    }

    // New: phases section title/subtitle
    $service_phases_heading    = get_post_meta( $post_id, 'service_phases_heading', true );
    $service_phases_subheading = get_post_meta( $post_id, 'service_phases_subheading', true );

    $service_body_image_id         = intval( get_post_meta( $post_id, 'service_body_image_id', true ) );
    $preview_size                  = ( function_exists( 'wp_get_attachment_image_url' ) ? SERVICE_GROUP_IMG_NAME : 'medium' );
    $service_body_image_url        = $service_body_image_id ? wp_get_attachment_image_url( $service_body_image_id, $preview_size ) : '';
    $service_form_link             = get_post_meta( $post_id, 'service_form_link', true );
    $service_image_max_width       = get_post_meta( $post_id, 'service_image_max_width', true );
    $service_group_image_max_width = get_post_meta( $post_id, 'service_group_image_max_width', true );
    ?>
    <div style="max-width:900px;">
      <p><strong><?php esc_html_e( 'Gruppo unico (titoli/testi) — nuove chiavi: service_box_*', 'abcontact' ); ?></strong></p>

      <p>
        <label for="service_box_heading1"><strong><?php esc_html_e( 'Titolo 1 (service_box_heading1)', 'abcontact' ); ?></strong></label><br>
        <input id="service_box_heading1" style="width:100%" type="text" name="service_box_heading1" value="<?php echo esc_attr( $service_box_heading1 ); ?>">
      </p>

      <p>
        <label for="service_box_text1"><strong><?php esc_html_e( 'Testo 1 (service_box_text1)', 'abcontact' ); ?></strong></label><br>
        <textarea id="service_box_text1" style="width:100%;height:100px" name="service_box_text1"><?php echo esc_textarea( $service_box_text1 ); ?></textarea>
      </p>

      <p>
        <label for="service_box_heading2"><strong><?php esc_html_e( 'Titolo 2 (service_box_heading2)', 'abcontact' ); ?></strong></label><br>
        <input id="service_box_heading2" style="width:100%" type="text" name="service_box_heading2" value="<?php echo esc_attr( $service_box_heading2 ); ?>">
      </p>

      <p>
        <label for="service_box_text2"><strong><?php esc_html_e( 'Testo 2 (service_box_text2)', 'abcontact' ); ?></strong></label><br>
        <textarea id="service_box_text2" style="width:100%;height:100px" name="service_box_text2"><?php echo esc_textarea( $service_box_text2 ); ?></textarea>
      </p>

      <hr>

      <p><strong><?php esc_html_e( 'Immagine corpo pagina', 'abcontact' ); ?></strong></p>
      <div id="ab-body-image-preview" style="margin-bottom:8px;">
        <?php if ( $service_body_image_url ) : ?>
          <img src="<?php echo esc_url( $service_body_image_url ); ?>" style="max-width:220px;height:auto;border-radius:8px;display:block;margin-bottom:6px;" alt="<?php echo esc_attr__( 'Anteprima immagine corpo pagina', 'abcontact' ); ?>">
        <?php endif; ?>
      </div>

      <input type="hidden" id="service_body_image_id" name="service_body_image_id" value="<?php echo esc_attr( $service_body_image_id ); ?>">
      <button type="button" class="button" id="ab-select-body-image"><?php esc_html_e( 'Seleziona immagine', 'abcontact' ); ?></button>
      <button type="button" class="button" id="ab-remove-body-image"><?php esc_html_e( 'Rimuovi immagine', 'abcontact' ); ?></button>

      <hr>

      <p>
        <label for="service_form_link"><strong><?php esc_html_e( 'Link CTA (service_form_link)', 'abcontact' ); ?></strong></label><br>
        <input id="service_form_link" style="width:100%" type="text" name="service_form_link" value="<?php echo esc_attr( $service_form_link ); ?>">
      </p>

      <hr>

      <p><strong><?php esc_html_e( 'Sezione Fasi (titolo e sottotitolo)', 'abcontact' ); ?></strong></p>

      <p>
        <label for="service_phases_heading"><strong><?php esc_html_e( 'Titolo sezione fasi (service_phases_heading)', 'abcontact' ); ?></strong></label><br>
        <input id="service_phases_heading" style="width:100%" type="text" name="service_phases_heading" value="<?php echo esc_attr( $service_phases_heading ); ?>">
      </p>

      <p>
        <label for="service_phases_subheading"><strong><?php esc_html_e( 'Sottotitolo sezione fasi (service_phases_subheading)', 'abcontact' ); ?></strong></label><br>
        <textarea id="service_phases_subheading" style="width:100%;height:80px" name="service_phases_subheading"><?php echo esc_textarea( $service_phases_subheading ); ?></textarea>
      </p>

      <hr>
      <p><strong><?php esc_html_e( 'Fasi (4) — modificabili', 'abcontact' ); ?></strong></p>
      <?php
      for ( $i = 1; $i <= 4; $i++ ) {
          $t = get_post_meta( $post_id, "service_phase_{$i}_title", true );
          $x = get_post_meta( $post_id, "service_phase_{$i}_text", true );
          ?>
          <p>
            <label for="service_phase_<?php echo esc_attr( $i ); ?>_title"><?php echo sprintf( esc_html__( 'Fase %d titolo', 'abcontact' ), $i ); ?></label><br>
            <input id="service_phase_<?php echo esc_attr( $i ); ?>_title" style="width:100%" type="text" name="service_phase_<?php echo esc_attr( $i ); ?>_title" value="<?php echo esc_attr( $t ); ?>">
          </p>
          <p>
            <label for="service_phase_<?php echo esc_attr( $i ); ?>_text"><?php echo sprintf( esc_html__( 'Fase %d testo', 'abcontact' ), $i ); ?></label><br>
            <textarea id="service_phase_<?php echo esc_attr( $i ); ?>_text" style="width:100%;height:60px" name="service_phase_<?php echo esc_attr( $i ); ?>_text"><?php echo esc_textarea( $x ); ?></textarea>
          </p>
          <?php
      }
      ?>

      <hr>

      <p><strong><?php esc_html_e( 'Dimensioni immagine (opzionali)', 'abcontact' ); ?></strong></p>
      <p>
        <label for="service_image_max_width"><?php esc_html_e( 'Hero max width (service_image_max_width) — es. 540px o 75%', 'abcontact' ); ?></label><br>
        <input id="service_image_max_width" style="width:100%" type="text" name="service_image_max_width" value="<?php echo esc_attr( $service_image_max_width ); ?>">
      </p>
      <p>
        <label for="service_group_image_max_width"><?php esc_html_e( 'Group image max (service_group_image_max_width) — es. 700px', 'abcontact' ); ?></strong></label><br>
        <input id="service_group_image_max_width" style="width:100%" type="text" name="service_group_image_max_width" value="<?php echo esc_attr( $service_group_image_max_width ); ?>">
      </p>

    </div>
    <?php
}

function abcontact_service_metabox_admin_assets( $hook ) {
    if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
        return;
    }

    // determine post_type
    $post_type = '';
    if ( isset( $_GET['post_type'] ) ) {
        $post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
    } elseif ( isset( $_GET['post'] ) ) {
        $post_id = intval( $_GET['post'] );
        $post_type = get_post_type( $post_id );
    } elseif ( isset( $_POST['post_ID'] ) ) {
        $post_type = get_post_type( intval( $_POST['post_ID'] ) );
    } else {
        global $post;
        if ( $post ) {
            $post_type = get_post_type( $post );
        }
    }

    if ( $post_type !== 'page' ) {
        return;
    }

    // Ensure media JS/CSS are available
    wp_enqueue_media();

    $admin_js_path = get_stylesheet_directory() . '/assets/js/chi-siamo-admin.js';
    $admin_js_uri  = get_stylesheet_directory_uri() . '/assets/js/chi-siamo-admin.js';
    if ( file_exists( $admin_js_path ) ) {
        wp_enqueue_script( 'ab-chisiamo-admin', $admin_js_uri, array( 'jquery' ), filemtime( $admin_js_path ), true );
        wp_localize_script( 'ab-chisiamo-admin', 'abcontactChiSiamo', array(
            'l10n' => array(
                'title'  => __( 'Seleziona immagine', 'abcontact' ),
                'button' => __( 'Usa immagine', 'abcontact' ),
            ),
        ) );
    }
}
add_action( 'admin_enqueue_scripts', 'abcontact_service_metabox_admin_assets' );


function abcontact_save_service_metabox( $post_id ) {
    if ( get_post_type( $post_id ) !== 'page' ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! isset( $_POST['ab_service_fields_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ab_service_fields_nonce'] ), 'ab_service_fields_save' ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $fields = array(
        'service_body_image_id',
        'service_form_link',
        'service_box_heading1',
        'service_box_text1',
        'service_box_heading2',
        'service_box_text2',
        'service_phases_heading',
        'service_phases_subheading',
        'service_image_max_width',
        'service_group_image_max_width',
    );
    for ( $i = 1; $i <= 4; $i++ ) {
        $fields[] = "service_phase_{$i}_title";
        $fields[] = "service_phase_{$i}_text";
    }

    foreach ( $fields as $f ) {
        if ( isset( $_POST[ $f ] ) ) {
            $raw = wp_unslash( $_POST[ $f ] );
            if ( in_array( $f, array( 'service_box_text1', 'service_box_text2', 'service_phases_subheading' ), true ) ) {
                $san = sanitize_textarea_field( $raw );
            } else {
                $san = sanitize_text_field( $raw );
            }
            update_post_meta( $post_id, $f, $san );
        } else {
            delete_post_meta( $post_id, $f );
        }
    }
}
add_action( 'save_post', 'abcontact_save_service_metabox' );

/* ================== Chi Siamo: metabox registration + save handlers ================== */
function abcontact_render_chisiamo_metabox( $post ) {
    // show only when the page uses the chi-siamo template
    $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
    $tpl_base = $tpl ? basename( $tpl ) : '';
    if ( $tpl_base !== 'page-chi-siamo.php' ) {
        echo '<p>' . esc_html__( 'Questi campi sono disponibili solo se il template della pagina è impostato su "Chi Siamo".', 'abcontact' ) . '</p>';
        return;
    }

    wp_nonce_field( 'ab_chisiamo_save', 'ab_chisiamo_nonce' );

    // Load saved values
    $cs = array();
    $fields = array(
        'cs_section_a_title','cs_section_a_text','cs_section_a_image_id',
        'cs_values_title','cs_values_subtitle',
        'cs_section_c_title','cs_section_c_text','cs_section_c_image_id',
        'cs_stats_heading',
        'cs_stat_1_value','cs_stat_1_label',
        'cs_stat_2_value','cs_stat_2_label',
        'cs_stat_3_value','cs_stat_3_label',
        'cs_stat_4_value','cs_stat_4_label',
        'cs_cta_title','cs_cta_text','cs_cta_button_label','cs_cta_button_link'
    );
    for ( $i = 1; $i <= 4; $i++ ) {
        $fields[] = "cs_value_{$i}_icon_id";
        $fields[] = "cs_value_{$i}_title";
        $fields[] = "cs_value_{$i}_text";
    }
    foreach ( $fields as $f ) {
        $cs[ $f ] = get_post_meta( $post->ID, $f, true );
    }

    // Render HTML
    ?>
    <div style="max-width:920px;">
      <h4><?php esc_html_e( 'Sezione: testo a sinistra, foto a destra', 'abcontact' ); ?></h4>

      <p>
        <label for="cs_section_a_title"><?php esc_html_e( 'Titolo sezione (A)', 'abcontact' ); ?></label><br>
        <input type="text" id="cs_section_a_title" name="cs_section_a_title" value="<?php echo esc_attr( $cs['cs_section_a_title'] ); ?>" style="width:100%">
      </p>

      <p>
        <label for="cs_section_a_text"><?php esc_html_e( 'Testo sezione (A)', 'abcontact' ); ?></label><br>
        <textarea id="cs_section_a_text" name="cs_section_a_text" rows="4" style="width:100%"><?php echo esc_textarea( $cs['cs_section_a_text'] ); ?></textarea>
      </p>

      <p>
        <label><?php esc_html_e( 'Immagine sezione (A)', 'abcontact' ); ?></label><br>
        <input type="hidden" id="cs_section_a_image_id" name="cs_section_a_image_id" value="<?php echo esc_attr( $cs['cs_section_a_image_id'] ); ?>">
        <button type="button" class="button" id="cs_select_a_image"><?php esc_html_e( 'Seleziona immagine', 'abcontact' ); ?></button>
        <button type="button" class="button" id="cs_remove_a_image"><?php esc_html_e( 'Rimuovi immagine', 'abcontact' ); ?></button>
        <div id="cs_preview_a" style="margin-top:8px;">
          <?php if ( $cs['cs_section_a_image_id'] ) : ?>
            <?php echo wp_get_attachment_image( (int) $cs['cs_section_a_image_id'], 'chi_siamo_image' ); ?>
          <?php endif; ?>
        </div>
      </p>

      <hr>

      <h4><?php esc_html_e( 'I nostri valori (4 elementi)', 'abcontact' ); ?></h4>

      <p>
        <label for="cs_values_title"><?php esc_html_e( 'Titolo sezione valori', 'abcontact' ); ?></label><br>
        <input type="text" id="cs_values_title" name="cs_values_title" value="<?php echo esc_attr( $cs['cs_values_title'] ); ?>" style="width:100%">
      </p>

      <p>
        <label for="cs_values_subtitle"><?php esc_html_e( 'Sottotitolo sezione valori', 'abcontact' ); ?></label><br>
        <textarea id="cs_values_subtitle" name="cs_values_subtitle" rows="3" style="width:100%"><?php echo esc_textarea( $cs['cs_values_subtitle'] ); ?></textarea>
      </p>

      <?php for ( $i = 1; $i <= 4; $i++ ) :
          $icon_id = isset( $cs["cs_value_{$i}_icon_id"] ) ? (int) $cs["cs_value_{$i}_icon_id"] : '';
      ?>
        <fieldset style="margin:10px 0;padding:10px;border:1px solid #eee;border-radius:6px">
          <legend><?php echo sprintf( esc_html__( 'Valore %d', 'abcontact' ), $i ); ?></legend>

          <p>
            <label><?php esc_html_e( 'Icona (Media Library)', 'abcontact' ); ?></label><br>
            <input type="hidden" id="cs_value_<?php echo $i; ?>_icon_id" name="cs_value_<?php echo $i; ?>_icon_id" value="<?php echo esc_attr( $icon_id ); ?>">
            <button type="button" class="button cs-select-icon" data-target="<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Seleziona icona', 'abcontact' ); ?></button>
            <button type="button" class="button cs-remove-icon" data-target="<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Rimuovi icona', 'abcontact' ); ?></button>
            <div id="cs_preview_icon_<?php echo $i; ?>" style="margin-top:8px;">
              <?php if ( $icon_id ) { echo wp_get_attachment_image( $icon_id, 'chi_siamo_icon' ); } ?>
            </div>
          </p>

          <p>
            <label for="cs_value_<?php echo $i; ?>_title"><?php esc_html_e( 'Titolo valore', 'abcontact' ); ?></label><br>
            <input type="text" id="cs_value_<?php echo $i; ?>_title" name="cs_value_<?php echo $i; ?>_title" value="<?php echo esc_attr( $cs["cs_value_{$i}_title"] ); ?>" style="width:100%">
          </p>

          <p>
            <label for="cs_value_<?php echo $i; ?>_text"><?php esc_html_e( 'Testo valore', 'abcontact' ); ?></label><br>
            <textarea id="cs_value_<?php echo $i; ?>_text" name="cs_value_<?php echo $i; ?>_text" rows="3" style="width:100%"><?php echo esc_textarea( $cs["cs_value_{$i}_text"] ); ?></textarea>
          </p>
        </fieldset>
      <?php endfor; ?>

      <hr>

      <h4><?php esc_html_e( 'Sezione: foto a sinistra, testo a destra', 'abcontact' ); ?></h4>

      <p>
        <label for="cs_section_c_title"><?php esc_html_e( 'Titolo sezione (C)', 'abcontact' ); ?></label><br>
        <input type="text" id="cs_section_c_title" name="cs_section_c_title" value="<?php echo esc_attr( $cs['cs_section_c_title'] ); ?>" style="width:100%">
      </p>

      <p>
        <label for="cs_section_c_text"><?php esc_html_e( 'Testo sezione (C)', 'abcontact' ); ?></label><br>
        <textarea id="cs_section_c_text" name="cs_section_c_text" rows="4" style="width:100%"><?php echo esc_textarea( $cs['cs_section_c_text'] ); ?></textarea>
      </p>

      <p>
        <label><?php esc_html_e( 'Immagine sezione (C)', 'abcontact' ); ?></label><br>
        <input type="hidden" id="cs_section_c_image_id" name="cs_section_c_image_id" value="<?php echo esc_attr( $cs['cs_section_c_image_id'] ); ?>">
        <button type="button" class="button" id="cs_select_c_image"><?php esc_html_e( 'Seleziona immagine', 'abcontact' ); ?></button>
        <button type="button" class="button" id="cs_remove_c_image"><?php esc_html_e( 'Rimuovi immagine', 'abcontact' ); ?></button>
        <div id="cs_preview_c" style="margin-top:8px;">
          <?php if ( $cs['cs_section_c_image_id'] ) : ?>
            <?php echo wp_get_attachment_image( (int) $cs['cs_section_c_image_id'], 'chi_siamo_image' ); ?>
          <?php endif; ?>
        </div>
      </p>

      <hr>

      <h4><?php esc_html_e( 'Dati statistici (box blu)', 'abcontact' ); ?></h4>

      <p>
        <label for="cs_stats_heading"><?php esc_html_e( 'Titolo statistica (opzionale)', 'abcontact' ); ?></label><br>
        <input type="text" id="cs_stats_heading" name="cs_stats_heading" value="<?php echo esc_attr( $cs['cs_stats_heading'] ); ?>" style="width:100%">
      </p>

      <?php for ( $i=1; $i<=4; $i++ ) : ?>
        <p style="display:flex;gap:8px;margin-bottom:8px;">
          <input type="text" name="cs_stat_<?php echo $i; ?>_value" value="<?php echo esc_attr( $cs["cs_stat_{$i}_value"] ); ?>" placeholder="<?php echo esc_attr( '10+', 'abcontact' ); ?>" style="width:30%">
          <input type="text" name="cs_stat_<?php echo $i; ?>_label" value="<?php echo esc_attr( $cs["cs_stat_{$i}_label"] ); ?>" placeholder="<?php echo esc_attr( 'Anni di esperienza' ); ?>" style="width:70%">
        </p>
      <?php endfor; ?>

      <hr>

      <h4><?php esc_html_e( 'CTA finale', 'abcontact' ); ?></h4>

      <p>
        <label for="cs_cta_title"><?php esc_html_e( 'Titolo CTA', 'abcontact' ); ?></label><br>
        <input type="text" id="cs_cta_title" name="cs_cta_title" value="<?php echo esc_attr( $cs['cs_cta_title'] ); ?>" style="width:100%">
      </p>

      <p>
        <label for="cs_cta_text"><?php esc_html_e( 'Testo CTA', 'abcontact' ); ?></label><br>
        <textarea id="cs_cta_text" name="cs_cta_text" rows="3" style="width:100%"><?php echo esc_textarea( $cs['cs_cta_text'] ); ?></textarea>
      </p>

      <p>
        <label for="cs_cta_button_label"><?php esc_html_e( 'Etichetta bottone CTA', 'abcontact' ); ?></label><br>
        <input type="text" id="cs_cta_button_label" name="cs_cta_button_label" value="<?php echo esc_attr( $cs['cs_cta_button_label'] ); ?>" style="width:100%">
      </p>

      <p>
        <label for="cs_cta_button_link"><?php esc_html_e( 'Link bottone CTA', 'abcontact' ); ?></label><br>
        <input type="text" id="cs_cta_button_link" name="cs_cta_button_link" value="<?php echo esc_attr( $cs['cs_cta_button_link'] ); ?>" style="width:100%">
      </p>

    </div>
    <?php
}

if ( ! function_exists( 'abcontact_service_metabox_admin_assets' ) ) {
    function abcontact_service_metabox_admin_assets( $hook ) {
        if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
            return;
        }

        // determine post_type
        $post_type = '';
        if ( isset( $_GET['post_type'] ) ) {
            $post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
        } elseif ( isset( $_GET['post'] ) ) {
            $post_id = intval( $_GET['post'] );
            $post_type = get_post_type( $post_id );
        } elseif ( isset( $_POST['post_ID'] ) ) {
            $post_type = get_post_type( intval( $_POST['post_ID'] ) );
        } else {
            global $post;
            if ( $post ) {
                $post_type = get_post_type( $post );
            }
        }

        if ( $post_type !== 'page' ) {
            return;
        }

        wp_enqueue_media();

        $admin_js_path = get_stylesheet_directory() . '/assets/js/chi-siamo-admin.js';
        $admin_js_uri  = get_stylesheet_directory_uri() . '/assets/js/chi-siamo-admin.js';
        if ( file_exists( $admin_js_path ) ) {
            wp_enqueue_script( 'ab-chisiamo-admin', $admin_js_uri, array( 'jquery' ), filemtime( $admin_js_path ), true );
            wp_localize_script( 'ab-chisiamo-admin', 'abcontactChiSiamo', array(
                'l10n' => array(
                    'title'  => __( 'Seleziona immagine', 'abcontact' ),
                    'button' => __( 'Usa immagine', 'abcontact' ),
                ),
            ) );
        }
    }
}
add_action( 'admin_enqueue_scripts', 'abcontact_service_metabox_admin_assets' );