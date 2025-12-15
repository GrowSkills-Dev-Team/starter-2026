<?php
$show_title = get_field('show_title');
$body = get_field('body');
$name_spacing = 'margin';
$spacing = get_field('spacing');

$button_label = get_field('button_label');
$button_link = get_field('button_link');

if($show_title || !empty($body)) : ?>
    <section class="body-text<?= ($spacing === 'none' ? ' no-' . $name_spacing : ' with-' . $name_spacing); ?><?= ($spacing ? ' with-' . $name_spacing . '-' . $spacing : ''); ?>">
        <div class="wrapper">
            <div class="body-text-text text-container">
                <?= ($show_title ? '<h1>' . get_the_title() . '</h1>' : null); ?>
                <?= $body; ?>
                <?= ($button_label ? '<a class="btn" href="#">' . $button_label . '</a>' : null); ?>
            </div>
        </div>
    </section>
<?php endif;