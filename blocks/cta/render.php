<?php
$title = get_field('title');
$text = get_field('text');
$button = get_field('button');
$image = get_field('image');
$name_spacing = 'margin';
$spacing = get_field('spacing');

if($title || $image) : ?>
<section class="cta<?= ($spacing === 'none' ? ' no-' . esc_attr($name_spacing) : ' with-' . esc_attr($name_spacing)); ?><?= ($spacing ? ' with-' . esc_attr($name_spacing) . '-' . esc_attr($spacing) : ''); ?>">
  <div class="wrapper">
    <div class="cta-text">
      <?= ($title ? '<h3>' . esc_html($title) . '</h3>' : null); ?>
      <?= ($text ? wp_kses_post($text) : null); ?>
      <?= ($button ? '<div><a class="btn" href="' . esc_url($button['url']) . '">' . esc_html($button['title']) . '</a></div>' : null); ?>
    </div>
    <div class="cta-image cover-image">
      <img src="<?= esc_url($image); ?>" <?= theme_image_attrs($image, 'content', ''); ?> />
    </div>
  </div>
</section>
<?php endif;