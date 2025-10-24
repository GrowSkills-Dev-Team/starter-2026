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
        <p><?= ($text ? $text : null); ?></p>
        <?= ($button ? '<div><a class="btn" href="'. $button['url'] .'">'. $button['title'] .'</a></div>' : null); ?>
      </div>
      <div class="cta-image cover-image">
      <?= ($image ? wp_get_attachment_image($image, 'full') : null); ?>
      </div>
  </div>
</section>
<?php endif;
  

