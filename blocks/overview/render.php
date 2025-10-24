<?php
$choose = get_field('choose_overview');
$overview_title = get_field('title');
$posts_per_page = 6;

if ($choose) :
    $args = array(
        'post_type' => $choose,
        'posts_per_page' => $posts_per_page
    );

    $query = new WP_Query($args);
    $items = $query->posts;

    if ($items) : ?>
        <section class="overview overview-<?= $choose; ?> with-padding">
            <div class="wrapper">
                <?php if($overview_title) : ?>
                    <div class="overview-text text-container">
                        <h2><?= $overview_title; ?></h2>
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