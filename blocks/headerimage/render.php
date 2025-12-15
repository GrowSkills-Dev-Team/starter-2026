<?php
$header_image = get_field('header_image');
$align_image = get_field('align_image');

if($header_image) : ?>
    <section class="header-image<?= ($align_image ? ' align-image-' . esc_attr($align_image) : ''); ?>">
        <div class="header-image-image cover-image">
            <img src="<?= esc_url($header_image); ?>" <?= theme_image_attrs($header_image, 'hero', ''); ?> />
        </div>
    </section>
<?php endif;