<form role="search" method="get" class="search-form" action="<?= esc_url(home_url('/')); ?>">
    <label for="search-field" class="search-label">
        <span class="screen-reader-text"><?= esc_html__('Search for:', 'starter-theme'); ?></span>
    </label>
    <input type="search" id="search-field" class="search-field" placeholder="<?= esc_attr__('Search...', 'starter-theme'); ?>" value="<?= get_search_query(); ?>" name="s" aria-label="<?= esc_attr__('Search', 'starter-theme'); ?>" />
    <button type="submit" class="btn search-submit" aria-label="<?= esc_attr__('Submit search', 'starter-theme'); ?>">
        <span class="screen-reader-text"><?= esc_html__('Search', 'starter-theme'); ?></span>
    </button>
</form>