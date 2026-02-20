<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* Register image sizes for the news preview */
function abcontact_news_image_sizes() {
    add_image_size( 'news-large', 900, 600, true ); 
    add_image_size( 'news-thumb', 300, 200, true );
}
add_action( 'after_setup_theme', 'abcontact_news_image_sizes' );

/* Register post meta for reading time */
function abcontact_register_news_meta() {
    register_post_meta( 'post', 'reading_time', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'integer',
        'auth_callback'     => function() { return current_user_can( 'edit_posts' ); },
    ) );
}
add_action( 'init', 'abcontact_register_news_meta' );

/* Add meta box for reading time */
function abcontact_add_reading_time_meta_box() {
    add_meta_box(
        'abcontact_reading_time',
        __( 'Tempo di lettura (minuti)', 'theme-abcontact' ),
        'abcontact_render_reading_time_meta_box',
        'post',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'abcontact_add_reading_time_meta_box' );

function abcontact_render_reading_time_meta_box( $post ) {
    wp_nonce_field( 'abcontact_save_reading_time', 'abcontact_reading_time_nonce' );
    $value = get_post_meta( $post->ID, 'reading_time', true );
    ?>
    <label for="abcontact_reading_time_field"><?php esc_html_e( 'Inserisci il tempo di lettura in minuti (lascia vuoto per calcolo automatico)', 'theme-abcontact' ); ?></label>
    <input type="number" min="1" step="1" id="abcontact_reading_time_field" name="abcontact_reading_time_field" value="<?php echo esc_attr( $value ); ?>" style="width:100%; margin-top:6px;">
    <?php
}

/* Save meta when post is saved */
function abcontact_save_reading_time_meta( $post_id, $post ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! isset( $_POST['abcontact_reading_time_nonce'] ) || ! wp_verify_nonce( $_POST['abcontact_reading_time_nonce'], 'abcontact_save_reading_time' ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['abcontact_reading_time_field'] ) && $_POST['abcontact_reading_time_field'] !== '' ) {
        $val = intval( $_POST['abcontact_reading_time_field'] );
        if ( $val > 0 ) {
            update_post_meta( $post_id, 'reading_time', $val );
        } else {
            delete_post_meta( $post_id, 'reading_time' );
        }
    } else {
        delete_post_meta( $post_id, 'reading_time' );
    }
}
add_action( 'save_post_post', 'abcontact_save_reading_time_meta', 10, 2 );

function abcontact_compute_reading_time( $content, $wpm = 200 ) {
    $text = strip_shortcodes( $content );
    $text = wp_strip_all_tags( $text );
    $words = str_word_count( $text );
    if ( $words <= 0 ) {
        return 1;
    }
    return max( 1, (int) round( $words / $wpm ) );
}

function abcontact_get_reading_time( $post_id = 0 ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    $meta = get_post_meta( $post_id, 'reading_time', true );
    if ( $meta && intval( $meta ) > 0 ) {
        return intval( $meta );
    }
    $post = get_post( $post_id );
    if ( ! $post ) {
        return 1;
    }
    return abcontact_compute_reading_time( $post->post_content );
}

function abcontact_get_contrast_color( $hex ) {
    if ( ! $hex ) {
        return '#ffffff';
    }

    $hex = trim( $hex );
    if ( strpos( $hex, '#' ) === 0 ) {
        $hex = substr( $hex, 1 );
    }
    if ( strlen( $hex ) === 3 ) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if ( strlen( $hex ) !== 6 ) {
        return '#ffffff';
    }

    $r = hexdec( substr( $hex, 0, 2 ) );
    $g = hexdec( substr( $hex, 2, 2 ) );
    $b = hexdec( substr( $hex, 4, 2 ) );

    $r_srgb = $r / 255;
    $g_srgb = $g / 255;
    $b_srgb = $b / 255;

    $r_lin = ($r_srgb <= 0.03928) ? ($r_srgb / 12.92) : pow((($r_srgb + 0.055) / 1.055), 2.4);
    $g_lin = ($g_srgb <= 0.03928) ? ($g_srgb / 12.92) : pow((($g_srgb + 0.055) / 1.055), 2.4);
    $b_lin = ($b_srgb <= 0.03928) ? ($b_srgb / 12.92) : pow((($b_srgb + 0.055) / 1.055), 2.4);

    $luminance = 0.2126 * $r_lin + 0.7152 * $g_lin + 0.0722 * $b_lin;

    return ( $luminance < 0.5 ) ? '#ffffff' : '#000000';
}

/* ============================ Category Term Meta ============================ */
function abcontact_category_color_field() {
    ?>
    <div class="form-field term-group">
        <label for="category_color"><?php esc_html_e( 'Colore etichetta', 'theme-abcontact' ); ?></label>
        <input type="text" id="category_color" name="category_color" value="" class="category-color-field" />
        <p class="description"><?php esc_html_e( 'Scegli il colore per la label di categoria. Es. #0b5fff', 'theme-abcontact' ); ?></p>
    </div>
    <?php
}
add_action( 'category_add_form_fields', 'abcontact_category_color_field', 10, 2 );

function abcontact_edit_category_color_field( $term ) {
    $color = get_term_meta( $term->term_id, 'category_color', true );
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="category_color"><?php esc_html_e( 'Colore etichetta', 'theme-abcontact' ); ?></label></th>
        <td>
            <input type="text" id="category_color" name="category_color" value="<?php echo esc_attr( $color ); ?>" class="category-color-field" />
            <p class="description"><?php esc_html_e( 'Scegli il colore per la label di categoria. Es. #0b5fff', 'theme-abcontact' ); ?></p>
        </td>
    </tr>
    <?php
}
add_action( 'category_edit_form_fields', 'abcontact_edit_category_color_field', 10, 2 );

function abcontact_save_category_color( $term_id ) {
    if ( isset( $_POST['category_color'] ) ) {
        $color = sanitize_text_field( $_POST['category_color'] );
        if ( $color === '' ) {
            delete_term_meta( (int) $term_id, 'category_color' );
        } else {
            if ( strpos( $color, '#' ) !== 0 ) {
                $color = '#' . $color;
            }
            update_term_meta( (int) $term_id, 'category_color', $color );
        }
    }
}
add_action( 'created_category', 'abcontact_save_category_color', 10, 2 );
add_action( 'edited_category', 'abcontact_save_category_color', 10, 2 );

function abcontact_enqueue_cat_color_picker( $hook_suffix ) {
    if ( 'edit-tags.php' !== $hook_suffix && 'term.php' !== $hook_suffix ) {
        return;
    }
    $screen = get_current_screen();
    if ( ! $screen || $screen->taxonomy !== 'category' ) {
        return;
    }

    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'abcontact-cat-color', get_stylesheet_directory_uri() . '/assets/js/admin-cat-color.js', array( 'wp-color-picker', 'jquery' ), filemtime( get_stylesheet_directory() . '/assets/js/admin-cat-color.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'abcontact_enqueue_cat_color_picker' );