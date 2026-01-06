<?php
function get_block_classes() {
    $classes = '';
    $bg_color = '';
    $name_spacing = 'margin';
    
    $spacing = get_field('spacing');
    $bg_color_field = get_field('bg_color');
    $text_color = get_field('text_color');
    
    if($bg_color_field) {
        $bg_color = 'background-color: ' . $bg_color_field . ';';
    }
    
    if($text_color === 'light') {
        $classes .= ' light-text';
    }
    
    if($spacing) {
        if($bg_color_field) {
            $name_spacing = 'padding';
        }
        $classes .= ($spacing === 'none' ? ' no-' . esc_attr($name_spacing) : ' with-' . esc_attr($name_spacing));
        $classes .= ($spacing ? ' with-' . esc_attr($name_spacing) . '-' . esc_attr($spacing) : '');
    }
    
    return array(
        'classes' => $classes,
        'bg_color' => $bg_color
    );
}