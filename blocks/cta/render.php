<?php
$title = get_field('title');
$text = get_field('text');
$button = get_field('button');
$image = get_field('image');
$name_spacing = 'margin';
$spacing = get_field('spacing');

if($title || $image) : ?>
<section class="cta<?= ($spacing === 'none' ? ' no-' . $name_spacing : ' with-' . $name_spacing); ?><?= ($spacing ? ' with-' . $name_spacing . '-' . $spacing : ''); ?>">
  <div class="wrapper">
    <div class="cta-text">
      <?= ($title ? '<h3>' . $title . '</h3>' : null); ?>
      <?= ($text ? $text : null); ?>
      <?= ($button ? '<div><a class="btn" href="'. $button['url'] .'">'. $button['title'] .'</a></div>' : null); ?>
    </div>
    <div class="cta-image cover-image">
      <img src="<?= $image; ?>" <?= theme_image_attrs($image, 'content', ''); ?> />
    </div>
  </div>
</section>
<?php endif;