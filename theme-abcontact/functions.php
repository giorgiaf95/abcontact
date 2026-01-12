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
    get_stylesheet_directory() . '/inc/metaboxes-lavora.php',
    get_stylesheet_directory() . '/inc/metaboxes-chisiamo-team.php',
    get_stylesheet_directory() . '/inc/metaboxes-services.php',
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

    $drawer_css_path = $theme_dir . '/assets/css/drawer-menu.css';
if ( file_exists( $drawer_css_path ) ) {
    wp_enqueue_style( 'theme-drawer-menu', $theme_uri . '/assets/css/drawer-menu.css', array(), filemtime( $drawer_css_path ) );
}
$drawer_js_path = $theme_dir . '/assets/js/drawer-menu.js';
if ( file_exists( $drawer_js_path ) ) {
    wp_enqueue_script( 'theme-drawer-menu', $theme_uri . '/assets/js/drawer-menu.js', array(), filemtime( $drawer_js_path ), true );
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

/* ================== Chi Siamo: metabox registration + save handlers ================== */

/**
 * Metabox for "Chi Siamo" page
 * - Register + render metabox
 * - Save handler for all fields
 * - Admin enqueue for media picker (chi-siamo-admin.js)
 */

/* ---------- Register metabox ---------- */
function abcontact_register_chisiamo_metabox() {
    add_meta_box(
        'ab_chisiamo_fields',
        __( 'Chi Siamo — Campi', 'abcontact' ),
        'abcontact_render_chisiamo_metabox',
        'page',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'abcontact_register_chisiamo_metabox' );

/* ---------- Render metabox ---------- */
function abcontact_render_chisiamo_metabox( $post ) {
    // Robust check: allow metabox when template is page-chi-siamo.php OR when page slug is 'chi-siamo'
    $use_metabox = false;
    $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
    $tpl_base = $tpl ? basename( $tpl ) : '';
    if ( $tpl_base === 'page-chi-siamo.php' ) {
        $use_metabox = true;
    } else {
        $page = get_post( $post->ID );
        if ( $page && $page->post_name === 'chi-siamo' ) {
            $use_metabox = true;
        }
    }

    if ( ! $use_metabox ) {
        echo '<p>' . esc_html__( 'Questi campi sono disponibili solo per la pagina "Chi Siamo".', 'abcontact' ) . '</p>';
        return;
    }

    // nonce
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

/* ================== Save handler ================== */

add_action( 'save_post', 'ab_chisiamo_save_fields' );
function ab_chisiamo_save_fields( $post_id ) {
    // basic checks
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['ab_chisiamo_nonce'] ) || ! wp_verify_nonce( $_POST['ab_chisiamo_nonce'], 'ab_chisiamo_save' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // fields to save (same keys used in your metabox render & template)
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
        if ( isset( $_POST[ $f ] ) ) {
            $val = $_POST[ $f ];
            if ( strpos( $f, '_image_id' ) !== false || strpos( $f, '_icon_id' ) !== false ) {
                update_post_meta( $post_id, $f, absint( $val ) );
            } elseif ( strpos( $f, '_text' ) !== false || stripos( $f, 'subtitle' ) !== false || $f === 'cs_cta_text' ) {
                update_post_meta( $post_id, $f, wp_kses_post( $val ) );
            } elseif ( stripos( $f, 'link' ) !== false ) {
                update_post_meta( $post_id, $f, esc_url_raw( $val ) );
            } else {
                update_post_meta( $post_id, $f, sanitize_text_field( $val ) );
            }
        } else {
            // keep existing meta if the field wasn't posted
        }
    }
}

/* ================== Admin enqueue (for media picker script) ================== */

add_action( 'admin_enqueue_scripts', 'ab_chisiamo_admin_enqueue' );
function ab_chisiamo_admin_enqueue( $hook ) {
    global $post;
    if ( ( $hook !== 'post.php' && $hook !== 'post-new.php' ) || ! $post || $post->post_type !== 'page' ) {
        return;
    }

    // only for the Chi Siamo page or template
    $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
    $tpl = $tpl ? basename( $tpl ) : '';
    if ( $tpl !== 'page-chi-siamo.php' && $post->post_name !== 'chi-siamo' ) {
        return;
    }

    // ensure media scripts
    wp_enqueue_media();

    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();
    $rel = '/assets/js/chi-siamo-admin.js';
    $full = $theme_dir . $rel;
    if ( file_exists( $full ) ) {
        wp_enqueue_script( 'ab-chisiamo-admin', $theme_uri . $rel, array( 'jquery' ), filemtime( $full ), true );

        wp_localize_script( 'ab-chisiamo-admin', 'abcontactChiSiamo', array(
            'l10n' => array(
                'title'  => esc_html__( 'Seleziona immagine', 'abcontact' ),
                'button' => esc_html__( 'Usa immagine', 'abcontact' ),
            ),
        ) );
    }
}

/* ================== NEW: Metabox per Shortcode Recensioni (Chi Siamo) ================== */

/**
 * Register metabox (only for page template page-chi-siamo.php or page slug 'chi-siamo')
 */
function abcontact_register_reviews_metabox() {
    add_meta_box(
        'ab_chisiamo_reviews',
        __( 'Shortcode Recensioni', 'abcontact' ),
        'abcontact_render_reviews_metabox',
        'page',
        'side',
        'low'
    );
}
add_action( 'add_meta_boxes', 'abcontact_register_reviews_metabox' );

/**
 * Render the reviews shortcode metabox
 *
 * Shown only when the page template is one of the allowed templates,
 * or when the page slug is 'chi-siamo' (legacy).
 */
function abcontact_render_reviews_metabox( $post ) {
    // Allowed templates where we want the metabox to appear
    $allowed_templates = array(
        'page-chi-siamo.php',
        'page-service-template.php',
    );

    $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
    $tpl = $tpl ? basename( $tpl ) : '';

    // Allow when page slug is 'chi-siamo' as backward compatibility
    $is_chisiamo_slug = ( isset( $post->post_name ) && $post->post_name === 'chi-siamo' );

    if ( ! in_array( $tpl, $allowed_templates, true ) && ! $is_chisiamo_slug ) {
        echo '<p>' . esc_html__( 'Questo campo è disponibile solo per le pagine "Chi Siamo" o "Servizi".', 'abcontact' ) . '</p>';
        return;
    }

    wp_nonce_field( 'ab_chisiamo_reviews_save', 'ab_chisiamo_reviews_nonce' );

    $val = get_post_meta( $post->ID, 'cs_reviews_shortcode', true );
    ?>
    <p>
      <label for="cs_reviews_shortcode"><?php esc_html_e( 'Incolla qui lo shortcode delle recensioni', 'abcontact' ); ?></label>
      <input type="text" id="cs_reviews_shortcode" name="cs_reviews_shortcode" value="<?php echo esc_attr( $val ); ?>" style="width:100%;" placeholder="[your_reviews_shortcode]">
      <small class="description"><?php esc_html_e( 'Esempio: [your_reviews id="123"] — il contenuto sarà renderizzato in pagina. Se vuoto, la sezione recensioni non verrà mostrata.', 'abcontact' ); ?></small>
    </p>
    <?php
}

/**
 * Save the reviews shortcode metabox value
 */
function abcontact_save_reviews_metabox( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['ab_chisiamo_reviews_nonce'] ) || ! wp_verify_nonce( $_POST['ab_chisiamo_reviews_nonce'], 'ab_chisiamo_reviews_save' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['cs_reviews_shortcode'] ) ) {
        // store as plain text (shortcode), sanitize minimal as it will be run through do_shortcode in frontend
        update_post_meta( $post_id, 'cs_reviews_shortcode', wp_strip_all_tags( trim( $_POST['cs_reviews_shortcode'] ) ) );
    } else {
        // If field removed, delete meta to avoid empty string variants
        delete_post_meta( $post_id, 'cs_reviews_shortcode' );
    }
}
add_action( 'save_post', 'abcontact_save_reviews_metabox', 20 );

/* ================== End of functions.php ================== */