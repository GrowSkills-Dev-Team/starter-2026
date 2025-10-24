<?php
/**
 * The template for displaying comments
 */

get_header();

if ( post_password_required() ) {
    return;
}
?>

<div id="comments" class="comments-area">

    <?php if ( have_comments() ) : ?>
        <h2 class="comments-title">
            <?php
            printf(
                /* translators: %s: number of comments */
                _nx( '%s reactie', '%s reacties', get_comments_number(), 'reactie telling', 'textdomain' ),
                number_format_i18n( get_comments_number() )
            );
            ?>
        </h2>

        <ol class="comment-list">
            <?php
            wp_list_comments( [
                'style'      => 'ol',
                'short_ping' => true,
                'avatar_size'=> 48,
            ] );
            ?>
        </ol>

    <?php endif; ?>

    <?php
    if ( comments_open() ) :

        comment_form( [
            'title_reply'         => __( 'Laat een reactie achter', 'textdomain' ),
            'title_reply_before'  => '<h2 id="reply-title" class="comment-reply-title">',
            'title_reply_after'   => '</h2>',
            'comment_field'       => '<p class="comment-form-comment">
                    <label for="comment" class="screen-reader-text">' . __( 'Reactie', 'textdomain' ) . '</label>
                    <textarea id="comment" name="comment" cols="45" rows="8" required aria-required="true"></textarea>
                </p>',
        ] );

    endif;
    ?>

</div>

<?php get_footer();