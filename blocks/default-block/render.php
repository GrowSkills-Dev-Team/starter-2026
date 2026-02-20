<?php
$show_title = get_field('show_title');
$body = get_field('body');
$button = get_field('button');

$block_data = get_block_classes();

if($show_title || !empty($body)) : ?>
    <section class="default-block<?= esc_attr($block_data['classes']); ?>"<?= ($block_data['bg_color']) ? ' style="' . $block_data['bg_color'] . '"' : ''; ?>>
        <div class="wrapper">
            <div class="body-text-text text-container">
                <?= ($show_title ? '<h1>' . esc_html(get_the_title()) . '</h1>' : null); ?>
                <?= $body; ?>
                <?= ($button ? '<div><a class="btn" href="' . esc_url($button['url']) . '">' . esc_html($button['title']) . '</a></div>' : null); ?>
            </div>
        </div>
    </section>
<?php endif;