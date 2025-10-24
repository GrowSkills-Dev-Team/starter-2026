<?php
$image = get_field('image');
$align_image = get_field('align_image');
$title = get_field('title');
$button = get_field('button');
$text = get_field('text');

if (empty($image) && empty($title)) {
    return;
}
?>

<section class="hero with-shape">
    <div class="hero-image cover-image" style="--valign: <?php echo esc_attr($align_image ?: '50%'); ?>;">
        <?= ($image ? wp_get_attachment_image($image, 'full') : null); ?>
    </div>
    <div class="wrapper">
        <div class="hero-text">
            <h1> <?= strtoupper($title); ?></h1>
            <?= $text ? '<div class="text">' . $text . '</div>' : null; ?>
            <?= ($button ? '<div><a class="btn" href="' . $button['url'] . '">' . $button['title'] . '</a></div>' : null); ?>
        </div>
    </div>
</section>