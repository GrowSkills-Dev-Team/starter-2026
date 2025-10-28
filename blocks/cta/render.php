<?php
$title = get_field('title');
$text = get_field('text');
$button = get_field('button');
$image = get_field('image');

if($title || $image) : ?>
<section class="cta with-margin">
  <div class="wrapper">
      <div class="cta-text">
         <?= ($title ? '<h3>' . $title . '</h3>' : null); ?>
        <?= ($text ? '<p>' . $text . '</p>' : null); ?>
        <?= ($button ? '<div><a class="btn" href="'. $button['url'] .'">'. $button['title'] .'</a></div>' : null); ?>
      </div>
      <div class="cta-image cover-image">
        <?= theme_image($image, 'content', ''); ?>
      </div>
  </div>
</section>
<?php endif;