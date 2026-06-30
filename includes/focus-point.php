<?php
/**
 * Focus Point
 * Allows editors to set an image focus point (x%, y%) in the Media Library.
 * The focus point is stored as post meta and applied as object-position CSS.
 */

// ─── 1. SAVE FOCUS POINT VIA AJAX ────────────────────────────────────────────

add_action('wp_ajax_gs_save_focus_point', function () {
    check_ajax_referer('gs_focus_point_nonce', 'nonce');

    if (!current_user_can('upload_files')) {
        wp_send_json_error(__('No access.', 'gs'));
    }

    $attachment_id = intval($_POST['attachment_id'] ?? 0);
    $x = floatval($_POST['x'] ?? 50);
    $y = floatval($_POST['y'] ?? 50);

    $x = max(0, min(100, $x));
    $y = max(0, min(100, $y));

    update_post_meta($attachment_id, '_gs_focus_point_x', $x);
    update_post_meta($attachment_id, '_gs_focus_point_y', $y);

    wp_send_json_success(['x' => $x, 'y' => $y]);
});

// ─── 2. GET FOCUS POINT VIA AJAX ─────────────────────────────────────────────

add_action('wp_ajax_gs_get_focus_point', function () {
    check_ajax_referer('gs_focus_point_nonce', 'nonce');

    $attachment_id = intval($_POST['attachment_id'] ?? 0);
    $x = get_post_meta($attachment_id, '_gs_focus_point_x', true);
    $y = get_post_meta($attachment_id, '_gs_focus_point_y', true);

    wp_send_json_success([
        'x' => ($x !== '') ? floatval($x) : 50,
        'y' => ($y !== '') ? floatval($y) : 50,
    ]);
});

// ─── 3. ENQUEUE ADMIN JS + CSS ───────────────────────────────────────────────

add_action('admin_enqueue_scripts', function () {

    wp_enqueue_script(
        'gs-focus-point',
        THEME_URI . '/js/focus-point.js',
        ['jquery'],
        filemtime(THEME_PATH . '/js/focus-point.js'),
        true
    );

    // All UI strings come from PHP so they follow the WP language setting
    wp_localize_script('gs-focus-point', 'gsFocusPoint', [
        'ajaxurl'    => admin_url('admin-ajax.php'),
        'nonce'      => wp_create_nonce('gs_focus_point_nonce'),
        'saved'      => __('Saved!',                                          'gs'),
        'saving'     => __('Saving...',                                       'gs'),
        'save'       => __('Save',                                            'gs'),
        'reset'      => __('Center (default)',                                'gs'),
        'label'      => __('Image focus point',                               'gs'),
        'btnText'    => __('Set focus point',                                 'gs'),
        'modalTitle' => __('Click on the image to set the focus point',       'gs'),
        'close'      => __('Close',                                           'gs'),
    ]);

    wp_enqueue_style(
        'gs-focus-point-admin',
        THEME_URI . '/css/focus-point-admin.css',
        [],
        filemtime(THEME_PATH . '/css/focus-point-admin.css')
    );
});

// ─── 4. HELPER: GET FOCUS POINT FOR AN ATTACHMENT ────────────────────────────

function gs_get_focus_point(int $attachment_id): array
{
    $x = get_post_meta($attachment_id, '_gs_focus_point_x', true);
    $y = get_post_meta($attachment_id, '_gs_focus_point_y', true);

    return [
        'x' => ($x !== '') ? floatval($x) : 50,
        'y' => ($y !== '') ? floatval($y) : 50,
    ];
}

// ─── 5. HELPER: GET object-position CSS VALUE ────────────────────────────────

function gs_focus_point_css(int $attachment_id): string
{
    $point = gs_get_focus_point($attachment_id);
    if ($point['x'] === 50.0 && $point['y'] === 50.0) {
        return 'center center';
    }
    return $point['x'] . '% ' . $point['y'] . '%';
}

// ─── 6. APPLY object-position VIA wp_get_attachment_image ────────────────────

add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment) {
    $x = get_post_meta($attachment->ID, '_gs_focus_point_x', true);
    $y = get_post_meta($attachment->ID, '_gs_focus_point_y', true);

    if ($x === '' && $y === '') {
        return $attr;
    }


    $attr['data-focus-x'] = floatval($x !== '' ? $x : 50);
    $attr['data-focus-y'] = floatval($y !== '' ? $y : 50);

    return $attr;
}, 10, 2);
