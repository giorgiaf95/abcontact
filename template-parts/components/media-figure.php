<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = isset( $args ) && is_array( $args ) ? $args : array();

$defaults = array(
    'attachment_id' => 0,
    'src'           => '',
    'size'          => 'full',
    'class'         => '',
    'alt'           => '',
    'link'          => '', 
);

$data = wp_parse_args( $args, $defaults );

$wrapper_classes = array( 'media-figure' );
if ( $data['class'] ) {
    $wrapper_classes[] = sanitize_html_class( $data['class'] );
}
$wrapper = implode( ' ', $wrapper_classes );

echo '<figure class="' . esc_attr( $wrapper ) . '">';

if ( $data['attachment_id'] && is_numeric( $data['attachment_id'] ) ) {
    $id = (int) $data['attachment_id'];
    $img = wp_get_attachment_image( $id, $data['size'], false, array(
        'class' => 'media-figure__img',
        'alt'   => $data['alt'] ? esc_attr( $data['alt'] ) : esc_attr( get_post_meta( $id, '_wp_attachment_image_alt', true ) ),
        'loading' => 'lazy',
        'decoding' => 'async',
    ) );
    if ( $data['link'] ) {
        echo '<a href="' . esc_url( $data['link'] ) . '">' . $img . '</a>';
    } else {
        echo $img;
    }
} elseif ( $data['src'] ) {
    $alt = $data['alt'] ? esc_attr( $data['alt'] ) : '';
    if ( $data['link'] ) {
        echo '<a href="' . esc_url( $data['link'] ) . '"><img class="media-figure__img" src="' . esc_url( $data['src'] ) . '" alt="' . $alt . '" loading="lazy" decoding="async" /></a>';
    } else {
        echo '<img class="media-figure__img" src="' . esc_url( $data['src'] ) . '" alt="' . $alt . '" loading="lazy" decoding="async" />';
    }
}

echo '</figure>';