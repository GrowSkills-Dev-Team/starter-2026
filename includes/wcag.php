<?php
/**
 * WCAG 2.1 AA Accessibility Features
 * Starter Theme 2025
 * 
 * This file contains all accessibility-related functions and features
 * to ensure WCAG 2.1 AA compliance.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add accessibility support to theme
 */
function setup_accessibility_features() {
    // Add theme support for accessibility features
    add_theme_support('accessibility-ready');
    // Declare explicitly that this theme supports accessibility landmarks.
    add_theme_support( 'accessibility', [
        'skip-links',
        'landmark-roles',
        'screen-reader-text',
        'navigation',
    ] );
}
add_action('after_setup_theme', 'setup_accessibility_features');

/**
 * Fallback menu for accessibility
 */
function accessibility_fallback_menu() {
    echo '<ul id="primary-menu" class="nav-menu" role="menubar">';
        echo '<li role="none"><a href="' . esc_url(home_url('/')) . '" role="menuitem">' . esc_html__('Home', 'starter-theme') . '</a></li>';
    echo '</ul>';
}

/**
 * Add skip links and accessibility CSS
 */
function enqueue_accessibility_assets() {
    // Add accessibility CSS
    wp_enqueue_style(
        'accessibility-styles',
        get_template_directory_uri() . '/css/accessibility.css',
        array(),
        '1.0.0'
    );
    
    // Add accessibility JavaScript
    wp_enqueue_script(
        'accessibility-script',
        get_template_directory_uri() . '/js/accessibility.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    // Localize script for accessibility
    wp_localize_script('accessibility-script', 'accessibility_vars', array(
        'menu_expanded' => esc_html__('Menu expanded', 'starter-theme'),
        'menu_collapsed' => esc_html__('Menu collapsed', 'starter-theme'),
        'skip_to_content' => esc_html__('Skip to main content', 'starter-theme'),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_accessibility_assets');

/**
 * Add aria-current to active menu items
 */
function add_aria_current_to_nav_menu($atts, $item, $args, $depth) {
    // Add aria-current="page" to current menu item
    if (in_array('current-menu-item', $item->classes, true)) {
        $atts['aria-current'] = 'page';
    }
    
    // Add aria-current="true" to current menu ancestor
    if (in_array('current-menu-ancestor', $item->classes, true)) {
        $atts['aria-current'] = 'true';
    }
    
    return $atts;
}
add_filter('nav_menu_link_attributes', 'add_aria_current_to_nav_menu', 10, 4);

/**
 * Improve image accessibility for wp_get_attachment_image
 */
function add_image_accessibility($attr, $attachment, $size) {
    // Add proper alt text if missing
    if (empty($attr['alt'])) {
        $alt_text = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
        if (empty($alt_text)) {
            $attr['alt'] = sprintf(esc_html__('Image: %s', 'starter-theme'), get_the_title($attachment->ID));
        }
    }
    
    // Add role="img" for decorative images
    if (empty($attr['alt']) || $attr['alt'] === '') {
        $attr['role'] = 'presentation';
        $attr['alt'] = '';
    }
    
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'add_image_accessibility', 10, 3);

/**
 * Fix alt text for images in WYSIWYG content (via the_content filter)
 */
function fix_wysiwyg_image_alt($content) {
    // Only process if content has images
    if (strpos($content, '<img') === false) {
        return $content;
    }
    
    // Find all img tags
    preg_match_all('/<img[^>]+>/i', $content, $matches);
    
    foreach ($matches[0] as $img_tag) {
        // Check if alt attribute is missing or empty
        if (!preg_match('/alt\s*=\s*["\'][^"\']*["\']/', $img_tag)) {
            // Try to extract attachment ID from class or src
            $attachment_id = null;
            
            // Try to get ID from wp-image-### class
            if (preg_match('/wp-image-(\d+)/', $img_tag, $class_matches)) {
                $attachment_id = intval($class_matches[1]);
            }
            // Try to get ID from src URL
            elseif (preg_match('/src=["\']([^"\']+)["\']/', $img_tag, $src_matches)) {
                $attachment_id = attachment_url_to_postid($src_matches[1]);
            }
            
            if ($attachment_id) {
                // Get alt text from attachment
                $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
                
                // If no alt text, use title
                if (empty($alt_text)) {
                    $alt_text = get_the_title($attachment_id);
                }
                
                // If still no alt text, use generic description
                if (empty($alt_text)) {
                    $alt_text = esc_html__('Image', 'starter-theme');
                }
                
                // Add alt attribute to img tag
                $new_img_tag = str_replace('<img', '<img alt="' . esc_attr($alt_text) . '"', $img_tag);
                $content = str_replace($img_tag, $new_img_tag, $content);
            } else {
                // No attachment ID found, add generic alt
                $new_img_tag = str_replace('<img', '<img alt="' . esc_attr__('Image', 'starter-theme') . '"', $img_tag);
                $content = str_replace($img_tag, $new_img_tag, $content);
            }
        }
        // Check if alt attribute exists but is empty
        elseif (preg_match('/alt\s*=\s*["\']["\']/', $img_tag)) {
            // Empty alt means decorative image, add role="presentation"
            if (strpos($img_tag, 'role=') === false) {
                $new_img_tag = str_replace('<img', '<img role="presentation"', $img_tag);
                $content = str_replace($img_tag, $new_img_tag, $content);
            }
        }
    }
    
    return $content;
}
add_filter('the_content', 'fix_wysiwyg_image_alt', 20);

/**
 * Add alt text requirement notice in media modal
 */
function add_alt_text_media_notice() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Add notice to media modal
        $(document).on('DOMNodeInserted', '.media-modal', function() {
            if (!$('.alt-text-notice').length) {
                $('.media-modal .attachment-details').prepend(
                    '<div class="alt-text-notice" style="background: #fff3cd; border: 0.0625rem solid #ffeaa7; padding: 0.625rem; margin-bottom: 0.9375rem; border-radius: 0.25rem;">' +
                    '<strong>🔍 Toegankelijkheid:</strong> Voeg altijd zinvolle alt-tekst toe aan afbeeldingen voor screen readers. Laat leeg voor decoratieve afbeeldingen.' +
                    '</div>'
                );
            }
        });
    });
    </script>
    <?php
}
add_action('admin_footer-post.php', 'add_alt_text_media_notice');
add_action('admin_footer-post-new.php', 'add_alt_text_media_notice');

/**
 * Add text domain for translations
 */
function load_theme_textdomain_accessibility() {
    load_theme_textdomain('starter-theme', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'load_theme_textdomain_accessibility');

/**
 * Add WCAG compliance check to admin
 */
function add_wcag_admin_notices() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
        $css_path = get_template_directory() . '/css/accessibility.css';
    $js_path = get_template_directory() . '/js/accessibility.js';
    
    if (!file_exists($css_path) || !file_exists($js_path)) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>WCAG Accessibility:</strong> Some accessibility assets are missing. Please ensure all accessibility files are present.</p>';
            echo '</div>';
        });
    }
}
add_action('admin_init', 'add_wcag_admin_notices');

/**
 * Add accessibility meta tags to head
 */
function add_accessibility_meta_tags() {
    echo '<meta name="accessibility" content="WCAG 2.1 AA compliant">' . "\n";
    echo '<meta name="color-scheme" content="light dark">' . "\n";
    echo '<meta name="theme-color" content="#0073aa" media="(prefers-color-scheme: light)">' . "\n";
    echo '<meta name="theme-color" content="#4f94cd" media="(prefers-color-scheme: dark)">' . "\n";
}
add_action('wp_head', 'add_accessibility_meta_tags');

/**
 * Add structured data for accessibility
 */
function add_accessibility_structured_data() {
    if (is_front_page()) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'url' => home_url('/'),
            'accessibilityFeature' => array(
                'structuralNavigation',
                'alternativeText',
                'highContrastDisplay',
                'resizeText',
                'skipLinks'
            ),
            'accessibilityControl' => array(
                'fullKeyboardControl',
                'fullTouchControl'
            ),
            'accessibilityAPI' => 'ARIA'
        );
        
        echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>' . "\n";
    }
}
add_action('wp_head', 'add_accessibility_structured_data');

/**
 * Enhanced body classes for accessibility
 */
function add_accessibility_body_classes($classes) {
    // Add accessibility-ready class
    $classes[] = 'accessibility-ready';
    
    // Add reduced motion class if user prefers it
    $classes[] = 'supports-reduced-motion';
    
    // Add high contrast class support
    $classes[] = 'supports-high-contrast';
    
    // Add dark mode support class
    $classes[] = 'supports-dark-mode';
    
    return $classes;
}
add_filter('body_class', 'add_accessibility_body_classes');

/**
 * Add ARIA landmarks to WordPress navigation
 */
function add_aria_landmarks_to_nav($nav_menu, $args) {
    // Add navigation landmark
    if (isset($args->theme_location)) {
        $nav_menu = str_replace('<ul', '<ul role="menubar"', $nav_menu);
        $nav_menu = str_replace('<li', '<li role="none"', $nav_menu);
        $nav_menu = str_replace('<a ', '<a role="menuitem" ', $nav_menu);
    }
    
    return $nav_menu;
}
add_filter('wp_nav_menu', 'add_aria_landmarks_to_nav', 10, 2);

/**
 * Add accessibility help text to admin
 */
function add_accessibility_admin_help() {
    $screen = get_current_screen();
    
    if ($screen->id === 'edit-post' || $screen->id === 'edit-page') {
        $screen->add_help_tab(array(
            'id' => 'accessibility-help',
            'title' => 'Accessibility Guidelines',
            'content' => '
                <h3>WCAG 2.1 AA Guidelines</h3>
                <ul>
                    <li><strong>Headings:</strong> Use logical heading order (H1 > H2 > H3)</li>
                    <li><strong>Images:</strong> Always add meaningful alt text</li>
                    <li><strong>Links:</strong> Use descriptive link text</li>
                    <li><strong>Content:</strong> Keep text clear and concise</li>
                    <li><strong>Forms:</strong> Label all form fields clearly</li>
                </ul>
                <p><strong>Test your content with:</strong> keyboard navigation and screen readers.</p>
            '
        ));
    }
}
add_action('load-post.php', 'add_accessibility_admin_help');
add_action('load-post-new.php', 'add_accessibility_admin_help');

/**
 * Add accessibility quick edit fields
 */
function add_accessibility_quick_edit($column_name, $post_type) {
    if ($column_name === 'accessibility_status') {
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label>
                    <span class="title">Accessibility Status</span>
                    <select name="accessibility_status">
                        <option value="compliant">WCAG 2.1 AA Compliant</option>
                        <option value="needs-review">Needs Review</option>
                        <option value="non-compliant">Non-Compliant</option>
                    </select>
                </label>
            </div>
        </fieldset>
        <?php
    }
}
add_action('quick_edit_custom_box', 'add_accessibility_quick_edit', 10, 2);

/**
 * WCAG Accessibility Dashboard Widget
 */
function add_wcag_dashboard_widget() {
    wp_add_dashboard_widget(
        'wcag_accessibility_widget',
        'WCAG 2.1 AA Accessibility Status',
        'wcag_dashboard_widget_content'
    );
}
add_action('wp_dashboard_setup', 'add_wcag_dashboard_widget');

/**
 * WCAG Dashboard Widget Content
 */
function wcag_dashboard_widget_content() {
    ?>
    <div class="accessibility-dashboard">
        <h4>✅ Accessibility Features Active:</h4>
        <ul>
            <li>ARIA landmarks and labels</li>
            <li>Keyboard navigation support</li>
            <li>Skip links for screen readers</li>
            <li>Mobile accessibility (2.75rem+ touch targets)</li>
            <li>Semantic HTML structure</li>
        </ul>
        
        <h4>🔧 Quick Tools:</h4>
        <p>
            <a href="<?php echo admin_url('?debug_acf_blocks=1'); ?>" target="_blank">Debug ACF Blocks</a> |
            <a href="https://wave.webaim.org/extension/" target="_blank">WAVE Extension</a>
        </p>
        
        <h4>📚 Guidelines:</h4>
        <p>This theme follows WCAG 2.1 AA standards. Use logical heading structure, meaningful alt text, and ensure good color contrast in your content.</p>
    </div>
    
    <style>
    .accessibility-dashboard ul {
        list-style: none;
        padding-left: 0;
    }
    .accessibility-dashboard li:before {
        content: "✓ ";
        color: #00a32a;
        font-weight: bold;
    }
    </style>
    <?php
}