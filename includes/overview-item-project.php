<?php
global $post;
$item = $args['item'] ?? $post;
$overview_text = get_field('overview_text', $item);
?>

<a href="<?= get_the_permalink($item) ?>" class="overview-item">
    <div class="overview-item-image cover-image">
       <?= get_the_post_thumbnail($item) ?>
    </div>
    <div class="overview-item-text">
        <h3><?= get_the_title($item) ?></h3>
        <?= ($overview_text ? '<div class="overview-item-text-summary"><p>' . wp_trim_words( $overview_text, 20, '...' ) . '</p></div>' : null); ?>
        <span class="btn">MEER INFORMATIE</span>
    </div>
</a>