<?php
$show_title = get_field('show_title');
$body = get_field('body');
$button = get_field('button');
$name_spacing = 'margin';
$spacing = get_field('spacing');

if($show_title || !empty($body)) : ?>
    <section class="body-text<?= ($spacing === 'none' ? ' no-' . $name_spacing : ' with-' . $name_spacing); ?><?= ($spacing ? ' with-' . $name_spacing . '-' . $spacing : ''); ?>">
        <div class="wrapper">
            <div class="body-text-text text-container">
                <?= ($show_title ? '<h1>' . get_the_title() . '</h1>' : null); ?>
                <?= $body; ?>
                <?= ($button ? '<a class="btn" href="'. $button['url'] .'">'. $button['title'] .'</a>' : null); ?>
            </div>
        </div>
    </section>
<?php endif;