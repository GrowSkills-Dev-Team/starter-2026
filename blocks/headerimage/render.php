<?php
$header_image = get_field('header_image');
$align_image = get_field('align_image');

if($header_image) : ?>
    <section class="header-image<?= ($align_image ? ' align-image-' . $align_image : ''); ?>">
        <div class="header-image-image cover-image">
            <?= theme_image($header_image, 'hero', ''); ?>
        </div>
    </section>
<?php endif;