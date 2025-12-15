<?php
$show_title = get_field('show_title');
$body = get_field('body');
$name_spacing = 'margin';
$spacing = get_field('spacing');
$button = get_field('button');

if($show_title || !empty($body)) : ?>
    <section class="body-text<?= ($spacing === 'none' ? ' no-' . esc_attr($name_spacing) : ' with-' . esc_attr($name_spacing)); ?><?= ($spacing ? ' with-' . esc_attr($name_spacing) . '-' . esc_attr($spacing) : ''); ?>">
        <div class="wrapper">
            <div class="body-text-text text-container">
                <?= ($show_title ? '<h1>' . esc_html(get_the_title()) . '</h1>' : null); ?>
                <?= wp_kses_post($body); ?>
                <?= ($button ? '<div><a class="btn" href="' . esc_url($button['url']) . '">' . esc_html($button['title']) . '</a></div>' : null); ?>
            </div>
        </div>
    </section>
<?php endif;