</main>
    <?php
    $adress = get_field('adress', 'option');

    ?>
    <footer role="contentinfo">
        <div class="footer-content with-padding">
            <div class="wrapper">
                <div class="adress">
                    <?= $adress; ?>
                </div>
                <?php if(have_rows('menus', 'option')) : ?>
                    <?php while(have_rows('menus', 'option')) : the_row();
                        $menu_title = get_sub_field('menu_title');
                        ?>
                        <div class="menu-link">
                            <?= ($menu_title ? '<h3>'. $menu_title .'</h3>' : ''); ?>
                            <?php if(have_rows('menu_items')) : ?>
                            <ul>
                                <?php while(have_rows('menu_items')) : the_row();
                                    $menu_link = get_sub_field('menu_link');
                                    ?>
                                <li><a href="<?= $menu_link['url'] ?>" target="<?= $menu_link['target'] ?>"><?= $menu_link['title'] ?></a></li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    
        <div class="footer-copyright">
            <div class="wrapper">
                <p>Copyright <?= date('Y'); ?> | <?= get_bloginfo('name'); ?></p>
            </div>
        </div>
    </footer>
    
    </div> <!-- #site-container -->

    <?php wp_footer(); ?>
</body>
</html>