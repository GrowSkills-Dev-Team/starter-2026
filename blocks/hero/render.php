<?php
$image = get_field('image');
$align_image = get_field('align_image');
$title = get_field('title');
$button = get_field('button');
$text = get_field('text');

if (!empty($image) && !empty($title)) :

?>

<section class="hero with-shape">
    <div class="hero-image cover-image" style="--valign: <?php echo esc_attr($align_image ?: '50%'); ?>;">
        <img src="<?= esc_url($image); ?>" <?= theme_image_attrs($image, 'hero', 'hero-image'); ?> />
    </div>
    <div class="wrapper">
        <div class="hero-text">
            <h1><?= esc_html(strtoupper($title)); ?></h1>
            <?= $text ? '<div class="text">' . wp_kses_post($text) . '</div>' : null; ?>
            <?= ($button ? '<div><a class="btn" href="' . esc_url($button['url']) . '">' . esc_html($button['title']) . '</a></div>' : null); ?>
        </div>
    </div>
</section>

<?php endif;