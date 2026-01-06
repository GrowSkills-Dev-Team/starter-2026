<?php

$query = $args['query'];
$post_type = $args['post_type'];
$posts_per_page = $args['posts_per_page'];

if ($query->found_posts > $query->post_count): ?>
    <div class="overview-more-btn">
	    <div class="btn-center">
            <button class="btn ajax-load-more" data-post-type="<?= $post_type ?>" data-posts-per-page="<?= $posts_per_page ?>">Laad meer</button>
        </div>
    </div>
<?php endif;