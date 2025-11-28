<?php
define('THEME_PATH', get_template_directory());
define('THEME_URI', get_template_directory_uri());

require_once('includes/default-settings.php');
require_once('includes/post-types.php');
require_once('includes/image-optim.php');
require_once('includes/wcag.php');

add_action('after_setup_theme', function () {
    register_nav_menus([
        'headermenu' => 'Headermenu',
        'footermenu' => 'Footer menu',
    ]);
});

add_filter('show_admin_bar', '__return_false');

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script( 'wp-a11y' );
    wp_dequeue_style('wp-block-library');
    wp_enqueue_style('stylesheet', get_stylesheet_uri(), [], filemtime(THEME_PATH . '/style.css'));
    wp_enqueue_script('script', THEME_URI . '/js/script.js', [], filemtime(THEME_PATH . '/js/script.js'), true);
    // wp_localize_script('script', 'ajax', ['url' => admin_url('admin-ajax.php')]);
});

add_action('admin_menu', function () {
    remove_menu_page('edit.php');
});

add_filter('wpseo_metabox_prio', function () {
    return 'low';
});

function svg(string $filename, string $class = '')
{
    $svg = file_get_contents(get_template_directory() . '/images/' . str_replace('.svg', '', $filename) . '.svg');
    if ($class) {
        $svg = str_replace('<svg', '<svg class="' . $class . '"', $svg);
    }
    return $svg;
}

add_filter('post_thumbnail_html', function ($html) {
    return preg_replace('/(width|height)=\"\d*\"\s/', "", $html);
});

function get_thumbnail_or_fallback($size = 'full', $class = null, $post = null)
{
    $post = get_post($post);
    $image_id = get_post_thumbnail_id($post) ?: get_field('fallback_image', 'option');
    return wp_get_attachment_image($image_id, $size, false, $class ? ['class' => $class] : '');
}

add_action('save_post', function ($post_id, $post) {
    if (has_blocks($post)) {
        $parsed_blocks = parse_blocks($post->post_content);
        $rendered_blocks = array_reduce($parsed_blocks, function ($prev_blocks, $current_block) {
            if (strpos($current_block['blockName'], 'acf/') !== false) {
                return $prev_blocks . acf_rendered_block($current_block['attrs']);
            } elseif (strpos($current_block['blockName'], 'core/') !== false) {
                return $prev_blocks . render_block($current_block);
            } else {
                return $prev_blocks;
            }
        });
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $rendered_blocks);
        $p_tags = $dom->getElementsByTagName('p');
        if ($p_tags) {
            $text = '';
            foreach ($p_tags as $p_tag) {
                $text .= ' ' . $p_tag->nodeValue;
            }
            update_post_meta($post->ID, 'growskills_blocks_excerpt', $text);
        }
    }
}, 10, 2);

add_filter('get_the_excerpt', function ($excerpt, $post) {
    if ($excerpt) return $excerpt;
    if ($growskills_blocks_excerpt = get_post_meta($post->ID, 'growskills_blocks_excerpt', true)) {
        $length = apply_filters('growskills_blocks_excerpt_length', apply_filters('excerpt_length', 55), $post);
        $more = apply_filters('growskills_blocks_excerpt_more', apply_filters('excerpt_more', '...'), $post);
        return wp_trim_words($growskills_blocks_excerpt, $length, $more);
    }
    return '';
}, 20, 2);

function render_pagination($query, $posts_per_page, $paged)
{
    if (isset($query) && $query->found_posts > $posts_per_page): ?>
        <div class="pagination">
            <div class="wrapper">
                <?php if ($paged > 1): ?>
                    <a href="<?= get_pagenum_link($paged - 1) ?>" class="pagination-link prev"><i class="fas fa-arrow-left"></i><span>Vorige pagina</span></a>
                <?php else: ?>
                    <span class="pagination-link prev disabled"><i class="fas fa-arrow-left"></i><span>Vorige pagina</span></span>
                <?php endif ?>
                <?php if ($paged < $query->max_num_pages): ?>
                    <a href="<?= get_pagenum_link($paged + 1) ?>" class="pagination-link next"><span>Volgende pagina</span><i class="fas fa-arrow-right"></i></a>
                <?php else: ?>
                    <span class="pagination-link next disabled"><span>Volgende pagina</span><i class="fas fa-arrow-right"></i></span>
                <?php endif ?>
            </div>
        </div>
    <?php else: ?>
        <div class="pagination no-pagnation"><h3>Geen paginas</h3></div>
    <?php endif;
}

add_action('wp_ajax_load_more', 'ajax_load_more');
add_action('wp_ajax_nopriv_load_more', 'ajax_load_more');

function ajax_load_more()
{
    $post_type = $_POST['post_type'];
    $posts_per_page = $_POST['posts_per_page'];
    $offset = $_POST['offset'];
    $post_status = 'publish';

    $query = new WP_Query(compact('post_type', 'posts_per_page', 'meta_query', 'offset', 'post_status'));
    foreach ($query->posts as $post) {
        get_template_part('includes/overview-item', $post_type, ['item' => $post]);
    }
    if ($query->found_posts <= $posts_per_page + $offset) {
        echo '<div class="hide-ajax-button"></div>';
    }
    wp_die();
}

add_filter('allowed_block_types_all', function ($allowed_blocks, $editor_context) {
    $blocks_directory = __DIR__ . '/blocks/';
    $single_blocks_directory = glob($blocks_directory . '*/', GLOB_ONLYDIR);
    $allowed_block_list = ['core/block'];
    foreach ($single_blocks_directory as $directory) {
        $allowed_block_list[] = 'acf/' . basename($directory);
    }
    return $allowed_block_list;
}, 25, 2);

add_action('init', function () {
    $blocks_dir = get_template_directory() . '/blocks/';
    foreach (glob($blocks_dir . '*', GLOB_ONLYDIR) as $block_folder) {
        $block_json = $block_folder . '/block.json';
        if (file_exists($block_json)) {
            register_block_type($block_folder);
        }
    }
});

//image alt
add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment, $size) {
    if (!empty($attr['alt'])) {
        return $attr;
    }

    global $post;

    if (function_exists('get_field_objects') && $post) {
        $fields = get_field_objects($post->ID);

        if ($fields) {
            foreach ($fields as $field_key => $field) {
                if (
                    isset($field['value']) &&
                    (
                        $field['value'] === $attachment->ID ||
                        (is_array($field['value']) && in_array($attachment->ID, $field['value']))
                    )
                ) {
                    $section_name = ucwords(str_replace(['_', '-'], ' ', $field['name']));
                    $section_name = str_replace('Image', 'Section', $section_name);

                    $alt_text = "{$section_name} - " . get_the_title($post->ID);
                    $attr['alt'] = esc_attr($alt_text);
                    return $attr;
                }
            }
        }
    }
    $attr['alt'] = esc_attr(get_the_title($attachment->ID));

    return $attr;
}, 10, 3);

