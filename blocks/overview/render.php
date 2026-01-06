<?php
$choose = get_field('choose_overview');
$overview_title = get_field('title');
$amount = get_field('amount');
$button = get_field('button');

if($amount === 'latest'){
    $posts_per_page = 3;
} else{
    $posts_per_page = 6;
}

$block_data = get_block_classes();

if ($choose) :
    $args = array(
        'post_type' => $choose,
        'posts_per_page' => $posts_per_page
    );

    $query = new WP_Query($args);
    $items = $query->posts;

    if ($items) : ?>
        <section class="overview overview<?= esc_attr($choose); ?><?= esc_attr($block_data['classes']); ?>"<?= ($block_data['bg_color']) ? ' style="' . $block_data['bg_color'] . '"' : ''; ?>>
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

            <?php if($amount === 'latest' && $button) : ?>
                <div class="overview-more-btn">
                    <div class="btn-center">
                        <a class="btn" href="<?= esc_url($button['url']); ?>"><?= esc_html($button['title']); ?></a>
                    </div>
                </div>
            <?php else : ?>
                <?php get_template_part('includes/overview-more', null, [
                    'query' => $query,
                    'post_type' => $choose,
                    'posts_per_page' => $posts_per_page
                ]) ?>
            <?php endif; ?>
        </section>    
    <?php endif;
endif;