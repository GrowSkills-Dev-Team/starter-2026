<?php

add_filter('intermediate_image_sizes', function($sizes) {
    return array_diff($sizes, [
        'thumbnail',
        'medium',
        'medium_large',
        'large',
        '1536x1536',
        '2048x2048'
    ]);
});

add_action('after_setup_theme', function () {
    add_image_size('hero-xl', 2200, 0, false); 
    add_image_size('content-l', 1250, 0, false);
    add_image_size('content-m', 900, 0, false);
    add_image_size('content-s', 600, 0, false);
});

// delete original image after scaling
add_filter('wp_generate_attachment_metadata', function ($metadata, $attachment_id) {

    $abs_scaled = get_attached_file($attachment_id);
    if (!$abs_scaled || !file_exists($abs_scaled)) {
        return $metadata;
    }

    $ext = strtolower(pathinfo($abs_scaled, PATHINFO_EXTENSION));
    if ($ext === 'png') {
        return $metadata;
    }

    $uploads = wp_get_upload_dir();
    $basedir = trailingslashit($uploads['basedir']);

    $abs_original = null;
    if (!empty($metadata['original_image'])) {
        $rel_dir     = trailingslashit(dirname($metadata['file']));
        $rel_original = $rel_dir . $metadata['original_image'];
        $abs_original = $basedir . $rel_original;
    } else {
        $pathinfo   = pathinfo($abs_scaled);
        $maybe_orig = preg_replace('/-scaled(?=\.[^.]+$)/i', '', $pathinfo['basename']);
        $candidate  = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $maybe_orig;

        if ($maybe_orig !== $pathinfo['basename'] && file_exists($candidate)) {
            $abs_original = $candidate;
        }
    }

    if (!$abs_original || !file_exists($abs_original)) {
        return $metadata;
    }

    if (filesize($abs_scaled) > 0 && filesize($abs_original) > filesize($abs_scaled)) {
        if (!@unlink($abs_original)) {
            error_log('[big-image-cleanup] Kon origineel niet verwijderen: ' . $abs_original);
        }
    }

    return $metadata;

}, 100, 2);

// Set image compression quality.
add_filter('wp_editor_set_quality', function($quality) {
    return 80;
});

function theme_image($image_id, $context = 'content', $class = '') {

    if (!$image_id) return '';
    
    $attr = [
        'class'    => $class,
        'decoding' => 'async'
    ];

    switch ($context) {

        case 'hero':
            $size  = 'hero-xl';
            $attr['loading']        = 'eager';
            $attr['fetchpriority']  = 'high';
            $attr['sizes'] = '(max-width: 768px) 100vw, (max-width: 1280px) 90vw, 2200px';
            break;

        case 'medium':
            $size = 'content-m';
            $attr['loading'] = 'lazy';
            $attr['sizes'] = '(max-width: 768px) 100vw, 50vw';
            break;

        case 'small':
            $size = 'content-s';
            $attr['loading'] = 'lazy';
            $attr['sizes'] = '(max-width: 768px) 50vw, 33vw';
            break;

        default:
            $size = 'content-l';
            $attr['loading'] = 'lazy';
            $attr['sizes'] = '(max-width: 768px) 100vw, (max-width: 1280px) 90vw, 1250px';
            break;
    }

    return wp_get_attachment_image($image_id, $size, false, $attr);
}

/**
 * Optimaliseer afbeelding gedrag in de editor (ACF / WYSIWYG):
 * - Gebruik 'content-m' als standaard
 * - Verwijder 'full' als keuze
 * - Zorg dat WP in de admin ook echt de kleinere versie rendert
 */
add_action('after_setup_theme', function () {

    // 1) Standaard insert size = content-m
    add_filter('pre_option_image_default_size', function () {
        return 'content-m';
    });

    // 2) Full verwijderen en content-m tonen in dropdown
    add_filter('image_size_names_choose', function ($sizes) {
        unset($sizes['full']); // users kunnen geen huge image kiezen
        $sizes['content-m'] = __('Content Medium (custom)', 'textdomain');
        return $sizes;
    });

    // 3) Zorg dat WordPress weet dat content-m een editor-beeldgrootte is
    add_filter('intermediate_image_sizes_advanced', function ($sizes) {
        $sizes['content-m'] = true;
        return $sizes;
    });

    // 4) Forceer render in admin met content-m i.p.v. full
    add_filter('wp_get_attachment_image_src', function ($image, $attachment_id, $size, $icon) {
        if (is_admin() && $size === 'content-m') {
            $src = wp_get_attachment_image_src($attachment_id, 'content-m');
            if ($src) return $src;
        }
        return $image;
    }, 10, 4);

    // 5) Pas sizes="" in admin aan zodat layout klopt
    add_filter('wp_calculate_image_sizes', function ($sizes, $size, $image_src, $attachment_id) {
        if (is_admin()) {
            return '(max-width: 768px) 100vw, 50vw';
        }
        return $sizes;
    }, 10, 4);

});

/**
 * Forceer inline WYSIWYG images (ACF / the_content) naar onze 'content-m' size
 * -- Veilig tegen recursion & memory leaks
 */
add_filter('the_content', function($content) {

    // 1) Niet in admin en niet in REST requests (Gutenberg preview etc.)
    if (is_admin() || defined('REST_REQUEST')) {
        return $content;
    }

    // 2) Als er niet eens een IMG in zit → skip
    if (strpos($content, '<img') === false) {
        return $content;
    }

    // 3) recursion guard → voorkomt opnieuw filteren
    static $in_filter = false;
    if ($in_filter) return $content;
    $in_filter = true;

    // 4) vervang inline images naar onze content-m helper
    $content = preg_replace_callback(
        '/<img[^>]+wp-image-([0-9]+)[^>]*>/i',
        function($matches) {
            $attachment_id = intval($matches[1]);
            if (!$attachment_id) return $matches[0];
            return theme_image($attachment_id, 'medium', 'wysiwyg-image');
        },
        $content
    );

    $in_filter = false;
    return $content;

}, 20);
