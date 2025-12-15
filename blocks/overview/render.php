<?php
$choose = get_field('choose_overview');
$overview_title = get_field('title');
$posts_per_page = 6;
$name_spacing = 'margin';
$spacing = get_field('spacing');

if ($choose) :
    $args = array(
        'post_type' => $choose,
        'posts_per_page' => $posts_per_page
    );

    $query = new WP_Query($args);
    $items = $query->posts;

    if ($items) : ?>
        <section class="overview overview-<?= esc_attr($choose); ?><?= ($spacing === 'none' ? ' no-' . esc_attr($name_spacing) : ' with-' . esc_attr($name_spacing)); ?><?= ($spacing ? ' with-' . esc_attr($name_spacing) . '-' . esc_attr($spacing) : ''); ?>">
            <div class="wrapper">
                <?php if($overview_title) : ?>
                    <div class="overview-text text-container">
                        <h2><?= esc_html($overview_title); ?></h2>
                    </div>
                <?php endif; ?>
                <div class="overview-items ajax-container">
                    <?php foreach ($items as $item) : ?>
                        <?php get_template_part('includes/overview-item', $choose, ['item' => $item]) ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php get_template_part('includes/overview-more', null, [
                'query' => $query,
                'post_type' => $choose,
                'posts_per_page' => $posts_per_page
            ]) ?>
        </section>    
    <?php endif;
endif;