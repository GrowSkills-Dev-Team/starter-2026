<?php
$header_image = get_field('header_image');
$align_image = get_field('align_image');

if($header_image) : ?>
    <section class="header-image<?= ($align_image ? ' align-image-' . $align_image : ''); ?>">
        <div class="header-image-image cover-image">
            <?= ($header_image ? wp_get_attachment_image($header_image, 'full') : null); ?>
        </div>
    </section>
<?php endif;
