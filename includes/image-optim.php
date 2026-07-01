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

function theme_image($image_id_or_url, $context = 'content', $class = '', $context_suffix = '') {

    if (!$image_id_or_url) return '';
    
    // Support voor ACF inline editor: als er een URL wordt doorgegeven, converteer naar ID
    $image_id = $image_id_or_url;
    if (is_string($image_id_or_url) && filter_var($image_id_or_url, FILTER_VALIDATE_URL)) {
        $image_id = attachment_url_to_postid($image_id_or_url);
        
        // Als attachment_url_to_postid() geen ID vindt, probeer dan de originele URL te gebruiken
        if (!$image_id) {
            // Fallback: render een simpele img tag met de URL
            $class_attr = $class ? ' class="' . esc_attr($class) . '"' : '';
            return '<img src="' . esc_url($image_id_or_url) . '"' . $class_attr . ' decoding="async" loading="lazy" />';
        }
    }
    
    $attr = [
        'class'    => $class,
        'decoding' => 'async'
    ];

    // Focus point (aplicado via wp_get_attachment_image_attributes filter em
    // includes/focus-point.php, que já usa gs_get_focus_point() com contexto
    // e injeta object-position diretamente no style — não é preciso repetir aqui)

    switch ($context) {

        case 'hero':
            $size  = 'hero-xl';
            $attr['loading']        = 'eager';
            $attr['fetchpriority']  = 'high';
            $attr['sizes'] = '(max-width: 48rem) 100vw, (max-width: 80rem) 90vw, 137.5rem';
            break;

        case 'medium':
            $size = 'content-m';
            $attr['loading'] = 'lazy';
            $attr['sizes'] = '(max-width: 48rem) 100vw, 50vw';
            break;

        case 'small':
            $size = 'content-s';
            $attr['loading'] = 'lazy';
            $attr['sizes'] = '(max-width: 48rem) 50vw, 33vw';
            break;

        default:
            $size = 'content-l';
            $attr['loading'] = 'lazy';
            $attr['sizes'] = '(max-width: 48rem) 100vw, (max-width: 80rem) 90vw, 78.125rem';
            break;
    }

    global $gs_focus_context_suffix;
    $gs_focus_context_suffix = $context_suffix;
    $html = wp_get_attachment_image($image_id, $size, false, $attr);
    $gs_focus_context_suffix = '';

    return $html;
}

/**
 * Genereer geoptimaliseerde image attributen voor ACF inline editor
 * Gebruik: <img src="<?= $image; ?>" <?= theme_image_attrs($image, 'content', 'my-class'); ?> />
 * 
 * Dit behoudt de originele <img> tag waar ACF zijn data-attributen aan toevoegt,
 * maar voegt wel srcset, sizes, alt, width, height etc. toe voor optimalisatie.
 *
 * Focus point: deze functie bouwt de <img> tag handmatig op (buiten
 * wp_get_attachment_image() om), dus het wp_get_attachment_image_attributes
 * filter in includes/focus-point.php wordt hier NIET aangeroepen. Daarom
 * halen we het focuspunt hier expliciet op via gs_get_focus_point() (met
 * context) en zetten we het rechtstreeks in de inline style.
 */
function theme_image_attrs($image_id_or_url, $context = 'content', $class = '', $context_suffix = '') {
    
    if (!$image_id_or_url) return '';
    
    // Converteer URL naar ID indien nodig
    $image_id = $image_id_or_url;
    if (is_string($image_id_or_url) && filter_var($image_id_or_url, FILTER_VALIDATE_URL)) {
        $image_id = attachment_url_to_postid($image_id_or_url);
        if (!$image_id) {
            // Geen ID gevonden, return alleen basis attributen
            $attrs = 'decoding="async" loading="lazy"';
            if ($class) $attrs .= ' class="' . esc_attr($class) . '"';
            return $attrs;
        }
    }
    
    // Bepaal size en attrs op basis van context
    switch ($context) {
        case 'hero':
            $size = 'hero-xl';
            $loading = 'eager';
            $fetchpriority = 'high';
            $sizes = '(max-width: 48rem) 100vw, (max-width: 80rem) 90vw, 137.5rem';
            break;
        case 'medium':
            $size = 'content-m';
            $loading = 'lazy';
            $fetchpriority = null;
            $sizes = '(max-width: 48rem) 100vw, 50vw';
            break;
        case 'small':
            $size = 'content-s';
            $loading = 'lazy';
            $fetchpriority = null;
            $sizes = '(max-width: 48rem) 50vw, 33vw';
            break;
        default:
            $size = 'content-l';
            $loading = 'lazy';
            $fetchpriority = null;
            $sizes = '(max-width: 48rem) 100vw, (max-width: 80rem) 90vw, 78.125rem';
            break;
    }
    
    // Haal srcset op
    $srcset = wp_get_attachment_image_srcset($image_id, $size);
    $image_meta = wp_get_attachment_metadata($image_id);
    $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
    
    // Focus point — via het gecentraliseerde systeem in includes/focus-point.php,
    // met context (zelfde afbeelding kan een ander focuspunt hebben per post/pagina).
    $style_attr = '';
    if (is_numeric($image_id) && function_exists('gs_get_focus_point') && function_exists('gs_current_focus_context')) {
        $context_id = gs_current_focus_context();
        if ($context_suffix) {
            $context_id .= '_' . sanitize_key($context_suffix);
        }
        $point = gs_get_focus_point((int) $image_id, $context_id);

        if (!($point['x'] === 50.0 && $point['y'] === 50.0)) {
            $position = esc_attr($point['x'] . '% ' . $point['y'] . '%');
            $style_attr = 'style="object-position: ' . $position . ';"';
        }
    }

    // Bouw attributen string
    $attrs = [];
    if ($style_attr) $attrs[] = $style_attr;
    if ($srcset) $attrs[] = 'srcset="' . esc_attr($srcset) . '"';
    if ($sizes) $attrs[] = 'sizes="' . esc_attr($sizes) . '"';
    if ($alt) $attrs[] = 'alt="' . esc_attr($alt) . '"';
    if ($class) $attrs[] = 'class="' . esc_attr($class) . '"';
    $attrs[] = 'decoding="async"';
    $attrs[] = 'loading="' . esc_attr($loading) . '"';
    if ($fetchpriority) $attrs[] = 'fetchpriority="' . esc_attr($fetchpriority) . '"';
    
    // Voeg width en height toe voor betere CLS (Cumulative Layout Shift)
    if ($image_meta && isset($image_meta['width']) && isset($image_meta['height'])) {
        $attrs[] = 'width="' . esc_attr($image_meta['width']) . '"';
        $attrs[] = 'height="' . esc_attr($image_meta['height']) . '"';
    }
    
    return implode(' ', $attrs);
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
            return '(max-width: 48rem) 100vw, 50vw';
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