<?php
/**
 * Template Name: Lavora con noi
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$post_id = get_the_ID();

/* Enqueue page-specific CSS */
$css_path = get_stylesheet_directory() . '/assets/css/lavora-con-noi.css';
if ( file_exists( $css_path ) ) {
    wp_enqueue_style( 'ab-lavora-con-noi', get_stylesheet_directory_uri() . '/assets/css/lavora-con-noi.css', array( 'abcontact-main' ), filemtime( $css_path ) );
}

get_header();

/* hero-default/news-hero */
set_query_var( 'args', array(
    'eyebrow' => __( 'Lavora con noi', 'abcontact' ),
    'title'   => get_the_title( $post_id ),
    'subtitle' => '',
    'bg_url'  => get_the_post_thumbnail_url( $post_id, 'full' )
) );
get_template_part( 'template-parts/news-hero' );
set_query_var( 'args', null );
?>

<?php
// inserisco la sezione "Perché scegliere Abcontact" sotto l'hero
if ( locate_template( 'template-parts/lavora-why.php' ) ) {
    get_template_part( 'template-parts/lavora-why' );
}

/**
 * Helper: normalize possible escaped newline sequences to real newlines.
 * - Converts literal backslash+n (two chars) to actual "\n"
 * - Converts literal backslash+r\n as well
 */
function lc_normalize_newlines( $str ) {
    if ( ! is_string( $str ) || $str === '' ) {
        return $str;
    }
    // Convert escaped sequences (from JSON double-escaping etc) to real newlines
    $str = str_replace( array( '\\r\\n', '\\n', '\\r' ), array( "\r\n", "\n", "\r" ), $str );
    // Normalize Windows CRLF -> \n
    $str = str_replace( array( "\r\n", "\r" ), "\n", $str );
    return $str;
}

/* ---------------------------
   Posizioni Aperte (render from page meta _lc_positions)
   Robust handling: accept both array (new) and JSON string (legacy)
   --------------------------- */
$positions_meta = get_post_meta( $post_id, '_lc_positions', true );
$positions = array();

if ( is_array( $positions_meta ) && ! empty( $positions_meta ) ) {
    // already stored as array (preferred)
    $positions = $positions_meta;
} elseif ( is_string( $positions_meta ) && $positions_meta !== '' ) {
    // Try JSON decode first
    $decoded = json_decode( $positions_meta, true );
    if ( is_array( $decoded ) ) {
        $positions = $decoded;
    } else {
        // Maybe it's a serialized PHP value (older WP storage) -> try maybe_unserialize
        $maybe = maybe_unserialize( $positions_meta );
        if ( is_array( $maybe ) ) {
            $positions = $maybe;
        } else {
            // Otherwise leave empty (defensive)
            $positions = array();
        }
    }
}
?>

<?php if ( ! empty( $positions ) ) : ?>
<section class="lc-positions" aria-label="<?php esc_attr_e( 'Posizioni Aperte', 'abcontact' ); ?>">
  <div class="lc-positions-inner container">
    <header class="lc-positions-header">
      <h2 class="lc-positions-title"><?php esc_html_e( 'Posizioni Aperte', 'abcontact' ); ?></h2>
      <p class="lc-positions-leadin"><?php esc_html_e( "Scopri le opportunità di carriera disponibili e trova quella più adatta a te", "abcontact" ); ?></p>
    </header>

    <div class="lc-positions-grid">
      <?php foreach ( $positions as $pos ) :
        // Defensive normalization & sanitization
        $title = isset( $pos['title'] ) ? (string) $pos['title'] : '';
        $title = wp_strip_all_tags( $title );

        $excerpt = isset( $pos['excerpt'] ) ? $pos['excerpt'] : '';
        $excerpt = is_string( $excerpt ) ? lc_normalize_newlines( $excerpt ) : '';

        $image_id = isset( $pos['image_id'] ) ? intval( $pos['image_id'] ) : 0;

        $requirements = isset( $pos['requirements'] ) ? $pos['requirements'] : '';
        $requirements = is_string( $requirements ) ? lc_normalize_newlines( $requirements ) : '';

        $offer = isset( $pos['offer'] ) ? $pos['offer'] : '';
        $offer = is_string( $offer ) ? lc_normalize_newlines( $offer ) : '';

        $apply_url = isset( $pos['apply_url'] ) ? $pos['apply_url'] : '';
        if ( is_string( $apply_url ) && $apply_url !== '' ) {
            $raw_link = trim( $apply_url );
            if ( ! preg_match( '#^https?://#i', $raw_link ) ) {
                $apply_url = home_url( '/' . ltrim( $raw_link, '/' ) );
            } else {
                $apply_url = $raw_link;
            }
        } else {
            $apply_url = '';
        }
      ?>
        <article class="lc-position-card">
          <?php if ( $image_id ) : ?>
            <div class="lc-position-media">
              <?php echo wp_get_attachment_image( $image_id, 'large' ); ?>
            </div>
          <?php endif; ?>

          <div class="lc-position-body">
            <div class="lc-position-head">
              <h3 class="lc-position-title">
                <?php if ( $apply_url ) : ?>
                  <a href="<?php echo esc_url( $apply_url ); ?>"><?php echo esc_html( $title ); ?></a>
                <?php else : ?>
                  <?php echo esc_html( $title ); ?>
                <?php endif; ?>
              </h3>
            </div>

            <?php if ( $excerpt ) : ?>
              <div class="lc-position-excerpt">
                <?php
                // Preserve line breaks and allow limited HTML
                echo wp_kses_post( wpautop( $excerpt ) );
                ?>
              </div>
            <?php endif; ?>

            <?php if ( $requirements ) : ?>
              <div class="lc-position-reqs">
                <h4><?php esc_html_e( 'Requisiti', 'abcontact' ); ?></h4>
                <?php
                // If the text contains HTML we print sanitized HTML, otherwise split on newlines
                if ( strpos( $requirements, '<' ) === false ) {
                  $lines = preg_split( '/\n/', trim( $requirements ) );
                  echo '<ul class="lc-position-list">';
                  foreach ( $lines as $l ) {
                    $l = trim( $l );
                    if ( $l === '' ) continue;
                    echo '<li>' . esc_html( $l ) . '</li>';
                  }
                  echo '</ul>';
                } else {
                  echo wp_kses_post( $requirements );
                }
                ?>
              </div>
            <?php endif; ?>

            <?php if ( $offer ) : ?>
              <div class="lc-position-offer">
                <h4><?php esc_html_e( 'Cosa Offriamo', 'abcontact' ); ?></h4>
                <?php
                if ( strpos( $offer, '<' ) === false ) {
                  $lines = preg_split( '/\n/', trim( $offer ) );
                  echo '<ul class="lc-position-list">';
                  foreach ( $lines as $l ) {
                    $l = trim( $l );
                    if ( $l === '' ) continue;
                    echo '<li>' . esc_html( $l ) . '</li>';
                  }
                  echo '</ul>';
                } else {
                  echo wp_kses_post( $offer );
                }
                ?>
              </div>
            <?php endif; ?>

            <div class="lc-position-action">
              <?php if ( $apply_url ) : ?>
                <a class="button lc-position-cta" href="<?php echo esc_url( $apply_url ); ?>"><?php esc_html_e( 'Invia Candidatura', 'abcontact' ); ?></a>
              <?php else : ?>
                <a class="button lc-position-cta" href="<?php echo esc_url( home_url( '/contatti' ) ); ?>"><?php esc_html_e( 'Invia Candidatura', 'abcontact' ); ?></a>
              <?php endif; ?>
            </div>

          </div>
        </article>
      <?php endforeach; ?>
    </div>

  </div>
</section>
<?php else : ?>
  <section class="lc-positions lc-positions-empty" aria-label="<?php esc_attr_e( 'Posizioni Aperte', 'abcontact' ); ?>">
    <div class="container">
      <header class="lc-positions-header">
        <h2 class="lc-positions-title"><?php esc_html_e( 'Posizioni Aperte', 'abcontact' ); ?></h2>
        <p class="lc-positions-leadin"><?php esc_html_e( "Al momento non ci sono posizioni aperte. Puoi comunque inviarci una candidatura spontanea.", "abcontact" ); ?></p>
      </header>
      <p><a class="button" href="<?php echo esc_url( home_url( '/contatti' ) ); ?>"><?php esc_html_e( 'Invia Candidatura Spontanea', 'abcontact' ); ?></a></p>
    </div>
  </section>
<?php endif; ?>

<main class="lavora-main" role="main" aria-label="<?php echo esc_attr__( 'Lavora con noi', 'abcontact' ); ?>">
  <div class="container lavora-content">

    <?php
    // === CTA: unified partial, read values from cta_* meta (preferred).
    // Build args robustly and pass them explicitly to the partial.

    $page_id = isset( $post_id ) ? (int) $post_id : get_the_ID();

    // Preferred keys (central metabox)
    $cta_title        = get_post_meta( $page_id, 'cta_title', true );
    $cta_subtitle     = get_post_meta( $page_id, 'cta_subtitle', true );
    $cta_button_label = get_post_meta( $page_id, 'cta_button_label', true );
    $cta_button_link  = get_post_meta( $page_id, 'cta_button_link', true );
    $cta_button_color = get_post_meta( $page_id, 'cta_button_color', true );
    $cta_modal_raw    = get_post_meta( $page_id, 'cta_modal', true );
    $cta_modal        = $cta_modal_raw ? true : false;

    // Fallbacks (read-only): legacy meta keys used only if cta_* are empty
    if ( empty( $cta_title ) ) {
        $cta_title = get_post_meta( $page_id, 'lc_cta_title', true ) ?: get_post_meta( $page_id, 'service_final_cta_title', true ) ?: get_post_meta( $page_id, 'cs_cta_title', true );
    }
    if ( empty( $cta_subtitle ) ) {
        $cta_subtitle = get_post_meta( $page_id, 'lc_cta_text', true ) ?: get_post_meta( $page_id, 'service_final_cta_text', true ) ?: get_post_meta( $page_id, 'cs_cta_text', true );
    }
    if ( empty( $cta_button_label ) ) {
        $cta_button_label = get_post_meta( $page_id, 'lc_cta_button_label', true ) ?: get_post_meta( $page_id, 'service_final_cta_button_label', true ) ?: get_post_meta( $page_id, 'cs_cta_button_label', true );
    }
    if ( empty( $cta_button_link ) ) {
        $cta_button_link = get_post_meta( $page_id, 'lc_cta_button_link', true ) ?: get_post_meta( $page_id, 'service_final_cta_link', true ) ?: get_post_meta( $page_id, 'cs_cta_button_link', true );
    }
    if ( empty( $cta_button_color ) ) {
        $cta_button_color = get_post_meta( $page_id, 'lc_cta_button_color', true ) ?: get_post_meta( $page_id, 'service_final_cta_button_color', true ) ?: get_post_meta( $page_id, 'cs_cta_button_color', true );
    }

    // Normalize link: accept absolute URL or path
    if ( ! empty( $cta_button_link ) ) {
        $raw_link = trim( $cta_button_link );
        if ( ! preg_match( '#^https?://#i', $raw_link ) ) {
            $cta_button_link = home_url( '/' . ltrim( $raw_link, '/' ) );
        } else {
            $cta_button_link = $raw_link;
        }
    }

    $cta_args = array(
      'title'        => $cta_title,
      'subtitle'     => $cta_subtitle,
      'button_label' => $cta_button_label,
      'button_link'  => $cta_button_link,
      'button_color' => $cta_button_color,
      'modal'        => $cta_modal,
    );

    // Render the central CTA partial — it will render only if there are meaningful values.
    set_query_var( 'args', $cta_args );
    get_template_part( 'template-parts/cta', null, $cta_args );
    set_query_var( 'args', null );
    ?>

  </div>
</main>

<?php get_footer(); ?>