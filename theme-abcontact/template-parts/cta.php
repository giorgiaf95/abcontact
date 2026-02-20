<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function _cta_read( $keys, $src ) {
    foreach ( (array) $keys as $k ) {
        if ( isset( $src[ $k ] ) && $src[ $k ] !== '' ) {
            return $src[ $k ];
        }
    }
    return '';
}

function abcontact_locate_cta_source_page() {
    $opt = intval( get_option( 'abcontact_cta_page_id', 0 ) );
    if ( $opt && get_post_status( $opt ) ) {
        return $opt;
    }

    $front = (int) get_option( 'page_on_front', 0 );
    if ( $front && get_post_status( $front ) ) {
        return $front;
    }

    $q = new WP_Query( array(
        'post_type'      => 'page',
        'posts_per_page' => 20,
        'meta_query'     => array(
            'relation' => 'OR',
            array( 'key' => 'cta_title', 'compare' => 'EXISTS' ),
            array( 'key' => 'cta_button_label', 'compare' => 'EXISTS' ),
            array( 'key' => 'cs_cta_title', 'compare' => 'EXISTS' ),
        ),
        'fields' => 'ids',
    ) );

    if ( $q->have_posts() ) {
        foreach ( $q->posts as $id ) {
            $tpl = get_post_meta( $id, '_wp_page_template', true );
            if ( $tpl === '' || $tpl === 'default' ) {
                wp_reset_postdata();
                return (int) $id;
            }
        }
    }
    wp_reset_postdata();

    return 0;
}

$passed = array();
if ( isset( $args ) && is_array( $args ) ) {
    $passed = $args;
} else {
    $passed = get_query_var( 'args', array() );
    if ( ! is_array( $passed ) ) {
        $passed = array();
    }
}

$need_load = true;
if ( ! empty( $passed ) ) {
    if ( ! empty( _cta_read( array( 'title', 'button_label', 'button_link' ), $passed ) ) ) {
        $need_load = false;
    }
}

$source_id = 0;
$meta_values = array();

if ( $need_load ) {
    $source_id = abcontact_locate_cta_source_page();
    if ( $source_id ) {
        $meta_values['title'] = get_post_meta( $source_id, 'cta_title', true );
        $meta_values['subtitle'] = get_post_meta( $source_id, 'cta_subtitle', true );
        $meta_values['button_label'] = get_post_meta( $source_id, 'cta_button_label', true );
        $meta_values['button_link'] = get_post_meta( $source_id, 'cta_button_link', true );
        $meta_values['button_color'] = get_post_meta( $source_id, 'cta_button_color', true );
        $meta_values['modal'] = get_post_meta( $source_id, 'cta_modal', true ) ? true : false;

        if ( empty( $meta_values['title'] ) ) {
            $meta_values['title'] = get_post_meta( $source_id, 'cs_cta_title', true ) ?: get_post_meta( $source_id, 'lc_cta_title', true ) ?: '';
        }
        if ( empty( $meta_values['subtitle'] ) ) {
            $meta_values['subtitle'] = get_post_meta( $source_id, 'cs_cta_text', true ) ?: get_post_meta( $source_id, 'lc_cta_text', true ) ?: '';
        }
        if ( empty( $meta_values['button_label'] ) ) {
            $meta_values['button_label'] = get_post_meta( $source_id, 'cs_cta_button_label', true ) ?: get_post_meta( $source_id, 'lc_cta_button_label', true ) ?: '';
        }
        if ( empty( $meta_values['button_link'] ) ) {
            $meta_values['button_link'] = get_post_meta( $source_id, 'cs_cta_button_link', true ) ?: get_post_meta( $source_id, 'lc_cta_button_link', true ) ?: '';
        }
        if ( empty( $meta_values['button_color'] ) ) {
            $meta_values['button_color'] = get_post_meta( $source_id, 'cs_cta_button_color', true ) ?: get_post_meta( $source_id, 'lc_cta_button_color', true ) ?: '';
        }
    }
}

$defaults = array(
    'title'        => __( 'Hai un progetto?', 'theme-abcontact' ),
    'subtitle'     => __( 'Contattaci per una consulenza gratuita e una proposta su misura.', 'theme-abcontact' ),
    'button_label' => __( 'Richiedi un preventivo', 'theme-abcontact' ),
    'button_link'  => home_url( '/carica-la-tua-bolletta/' ),
    'button_color' => '#0b5fff',
    'modal'        => true,
);

$combined = wp_parse_args( $passed, $meta_values );
$cta = wp_parse_args( $combined, $defaults );

if ( ! empty( $cta['button_link'] ) ) {
    $raw_link = trim( $cta['button_link'] );
    if ( ! preg_match( '#^https?://#i', $raw_link ) ) {
        $cta['button_link'] = home_url( '/' . ltrim( $raw_link, '/' ) );
    } else {
        $cta['button_link'] = $raw_link;
    }
} else {
    $cta['button_link'] = $defaults['button_link'];
}

$cta['modal'] = $cta['modal'] ? true : false;

if ( empty( $cta['title'] ) && empty( $cta['button_label'] ) ) {
    return;
}

$btn_classes = array( 'cta-button' );
$wrap_style = '';
if ( ! empty( $cta['button_color'] ) ) {
    $wrap_style = ' style="--cta-btn-color:' . esc_attr( $cta['button_color'] ) . ';"';
}
?>
<section class="site-cta front-cta" data-abcontact-cta="1" aria-label="<?php echo esc_attr__( 'Call to action', 'abcontact' ); ?>"<?php echo $wrap_style; ?>>
  <div class="container">
    <div class="cta-inner">
      <?php if ( $cta['title'] ) : ?>
        <h2 class="cta-title"><?php echo esc_html( $cta['title'] ); ?></h2>
      <?php endif; ?>

      <?php if ( $cta['subtitle'] ) : ?>
        <div class="cta-sub"><?php echo wp_kses_post( wpautop( $cta['subtitle'] ) ); ?></div>
      <?php endif; ?>

      <?php if ( $cta['button_label'] ) : 
            $btn_class = implode( ' ', $btn_classes );
            $btn_label_esc = esc_html( $cta['button_label'] );
            if ( $cta['modal'] ) : ?>
                <div class="cta-actions" style="text-align:center;">
                  <button class="<?php echo esc_attr( $btn_class ); ?>" type="button" data-cta-modal="1"><?php echo $btn_label_esc; ?></button>
                </div>
            <?php else : 
                if ( ! empty( $cta['button_link'] ) ) : ?>
                  <div class="cta-actions" style="text-align:center;">
                    <a class="<?php echo esc_attr( $btn_class ); ?>" href="<?php echo esc_url( $cta['button_link'] ); ?>"><?php echo $btn_label_esc; ?></a>
                  </div>
                <?php else : ?>
                  <div class="cta-actions" style="text-align:center;">
                    <span class="<?php echo esc_attr( $btn_class ); ?>" aria-disabled="true"><?php echo $btn_label_esc; ?></span>
                  </div>
                <?php endif;
            endif;
        endif; ?>
    </div>
  </div>
</section>