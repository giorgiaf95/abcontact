<?php
// Template part: template-parts/components/button.php
// Usage: get_template_part( 'template-parts/components/button', null, array( 'label' => 'Test', 'href' => '#', 'variant' => 'primary', 'size' => 'md', 'type' => 'a', 'attrs' => array() ) );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = isset( $args ) && is_array( $args ) ? $args : array();

$defaults = array(
    'label'   => '',
    'href'    => '#',
    'variant' => 'primary', // primary, ghost, white, outline
    'size'    => 'md',      // sm, md, lg
    'type'    => 'a',       // 'a' (link) or 'button' (button element)
    'class'   => '',        // extra classes
    'attrs'   => array(),   // additional attributes as key => value
);

$data = wp_parse_args( $args, $defaults );

$classes = array( 'btn', 'btn--' . sanitize_html_class( $data['variant'] ), 'btn--' . esc_attr( $data['size'] ) );
if ( $data['class'] ) {
    $classes[] = esc_attr( $data['class'] );
}
$class_attr = implode( ' ', array_map( 'sanitize_html_class', $classes ) );

// build attributes
$attr_html = '';
if ( ! empty( $data['attrs'] ) && is_array( $data['attrs'] ) ) {
    foreach ( $data['attrs'] as $k => $v ) {
        $attr_html .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
    }
}

$label = wp_kses_post( $data['label'] );

if ( $data['type'] === 'button' ) {
    echo '<button type="button" class="' . esc_attr( $class_attr ) . '"' . $attr_html . '>' . $label . '</button>';
    return;
}

// default: anchor link
$href = esc_url( $data['href'] );
echo '<a class="' . esc_attr( $class_attr ) . '" href="' . $href . '"' . $attr_html . '>' . $label . '</a>';