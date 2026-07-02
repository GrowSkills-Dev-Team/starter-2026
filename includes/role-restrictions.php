<?php
/* =========================================================
 * USER PAGE EDIT RESTRICTIONS — wp-admin (starter-2026)
 * Each listed user can edit only one (or multiple) specific page(s)
 * Uses native WordPress user meta (no ACF)
 * ========================================================= */

// 1. Options Page - Native WordPress
add_action( 'admin_menu', function() {
    add_options_page(
        __( 'Access Restrictions', 'starter-2026' ),
        __( 'Access Restrictions', 'starter-2026' ),
        'manage_options',
        'gs-role-restrictions',
        'gs_role_restrictions_page'
    );
});

// 2. Render the options page
function gs_role_restrictions_page() {
    if ( isset( $_POST['gs_save_restrictions'] ) ) {
        check_admin_referer( 'gs_restrictions_nonce' );

        // FIX: sempre inicializa como array vazio e sempre chama update_option,
        // mesmo quando $_POST['page_restrictions'] não existe (ex: última linha removida).
        // Antes, se a tabela ficasse vazia, o isset() falhava e update_option()
        // nunca era chamado — a opção antiga no banco permanecia intacta,
        // fazendo o usuário "removido" reaparecer após salvar.
        $restrictions = array();

        if ( isset( $_POST['page_restrictions'] ) && is_array( $_POST['page_restrictions'] ) ) {
            foreach ( $_POST['page_restrictions'] as $restriction ) {
                if ( ! empty( $restriction['page_id'] ) && ! empty( $restriction['user_id'] ) ) {
                    $restrictions[] = array(
                        'page_id' => intval( $restriction['page_id'] ),
                        'user_id' => intval( $restriction['user_id'] ),
                    );
                }
            }
        }

        update_option( 'gs_page_restrictions', $restrictions );

        echo '<div class="notice notice-success"><p>' .
            __( 'Restrictions saved successfully!', 'starter-2026' ) .
            '</p></div>';
    }

    $restrictions = get_option( 'gs_page_restrictions', array() );
    $pages        = get_pages();
    $users        = get_users();
    ?>

    <div class="wrap">
        <h1><?php echo esc_html( __( 'Access Restrictions', 'starter-2026' ) ); ?></h1>

        <p>
            <?php echo esc_html(
                __( 'Assign specific pages to users. Each user will only be able to edit their assigned pages.', 'starter-2026' )
            ); ?>
        </p>

        <form method="post" action="">
            <?php wp_nonce_field( 'gs_restrictions_nonce' ); ?>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php echo esc_html( __( 'Page', 'starter-2026' ) ); ?></th>
                        <th><?php echo esc_html( __( 'User', 'starter-2026' ) ); ?></th>
                        <th><?php echo esc_html( __( 'Actions', 'starter-2026' ) ); ?></th>
                    </tr>
                </thead>

                <tbody id="restrictions-table">
                    <?php foreach ( $restrictions as $index => $restriction ) : ?>
                        <tr>
                            <td>
                                <select name="page_restrictions[<?php echo $index; ?>][page_id]">
                                    <?php foreach ( $pages as $page ) : ?>
                                        <option
                                            value="<?php echo $page->ID; ?>"
                                            <?php selected( $restriction['page_id'], $page->ID ); ?>
                                        >
                                            <?php echo esc_html( $page->post_title ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>

                            <td>
                                <select name="page_restrictions[<?php echo $index; ?>][user_id]">
                                    <?php foreach ( $users as $user ) : ?>
                                        <option
                                            value="<?php echo $user->ID; ?>"
                                            <?php selected( $restriction['user_id'], $user->ID ); ?>
                                        >
                                            <?php
                                            echo esc_html( $user->user_login );
                                            ?> (
                                            <?php
                                            echo esc_html( $user->display_name );
                                            ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>

                            <td>
                                <button type="button" class="button button-small gs-remove-row">
                                    <?php echo esc_html( __( 'Remove', 'starter-2026' ) ); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p>
                <button type="button" class="button" id="gs-add-row">
                    <?php echo esc_html( __( 'Add Restriction', 'starter-2026' ) ); ?>
                </button>

                <input
                    type="submit"
                    name="gs_save_restrictions"
                    class="button button-primary"
                    value="<?php echo esc_attr( __( 'Save Restrictions', 'starter-2026' ) ); ?>"
                >
            </p>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {

        var rowIndex = <?php echo count( $restrictions ); ?>;

        $('#gs-add-row').on('click', function() {

            var newRow =
                '<tr>' +

                '<td><select name="page_restrictions[' + rowIndex + '][page_id]">' +
                    '<option value="">Select a page</option>' +
                    <?php foreach ( $pages as $page ) : ?>
                        '<option value="<?php echo $page->ID; ?>"><?php echo esc_js( $page->post_title ); ?></option>' +
                    <?php endforeach; ?>
                '</select></td>' +

                '<td><select name="page_restrictions[' + rowIndex + '][user_id]">' +
                    '<option value="">Select a user</option>' +
                    <?php foreach ( $users as $user ) : ?>
                        '<option value="<?php echo $user->ID; ?>"><?php echo esc_js( $user->user_login ); ?> (<?php echo esc_js( $user->display_name ); ?>)</option>' +
                    <?php endforeach; ?>
                '</select></td>' +

                '<td><button type="button" class="button button-small gs-remove-row">Remove</button></td>' +

                '</tr>';

            $('#restrictions-table').append(newRow);
            rowIndex++;
        });

        $(document).on('click', '.gs-remove-row', function() {
            $(this).closest('tr').remove();
        });
    });
    </script>

    <?php
}

// 3. Helper: discover pages assigned to user
function gs_get_user_allowed_pages( $user_id ) {

    $restrictions  = get_option( 'gs_page_restrictions', array() );
    $allowed_pages = array();

    foreach ( $restrictions as $restriction ) {

        if (
            isset( $restriction['user_id'] ) &&
            intval( $restriction['user_id'] ) === intval( $user_id )
        ) {
            if ( isset( $restriction['page_id'] ) ) {
                $allowed_pages[] = intval( $restriction['page_id'] );
            }
        }
    }

    return $allowed_pages;
}

// 4. Helper: check if current user is restricted
function gs_current_user_is_restricted() {

    if ( ! is_user_logged_in() ) {
        return false;
    }

    if ( current_user_can( 'manage_options' ) ) {
        return false;
    }

    $allowed_pages = gs_get_user_allowed_pages( get_current_user_id() );

    return ! empty( $allowed_pages );
}

function gs_restrict_admin_access() {

    if ( wp_doing_ajax() ) {
        return;
    }

    if ( ! is_admin() ) {
        return;
    }

    if ( current_user_can( 'manage_options' ) ) {
        return;
    }

    $allowed_pages = gs_get_user_allowed_pages( get_current_user_id() );

    if ( empty( $allowed_pages ) ) {
        return;
    }
}
add_action( 'admin_init', 'gs_restrict_admin_access' );

function gs_hide_admin_menu_for_restricted_users() {

    if ( ! is_user_logged_in() ) {
        return;
    }

    if ( current_user_can( 'manage_options' ) ) {
        return;
    }

    $allowed_pages = gs_get_user_allowed_pages( get_current_user_id() );

    if ( empty( $allowed_pages ) ) {
        return;
    }

    global $menu, $submenu;

    $menu    = array();
    $submenu = array();

    add_menu_page(
        __( 'My Pages', 'starter-2026' ),
        __( 'My Pages', 'starter-2026' ),
        'edit_pages',
        'edit.php?post_type=page',
        '',
        'dashicons-edit',
        2
    );
}
add_action( 'admin_menu', 'gs_hide_admin_menu_for_restricted_users', 999 );

function gs_filter_pages_list_query( $query ) {

    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }

    global $pagenow;

    if ( $pagenow !== 'edit.php' ) {
        return;
    }

    if ( $query->get( 'post_type' ) !== 'page' ) {
        return;
    }

    if ( current_user_can( 'manage_options' ) ) {
        return;
    }

    $allowed_pages = gs_get_user_allowed_pages( get_current_user_id() );

    if ( empty( $allowed_pages ) ) {
        return;
    }

    $query->set( 'post__in', $allowed_pages );
}
add_action( 'pre_get_posts', 'gs_filter_pages_list_query' );

function gs_simplify_admin_bar_for_restricted_users( $wp_admin_bar ) {

    if ( ! gs_current_user_is_restricted() ) {
        return;
    }

    $keep  = array( 'top-secondary', 'my-account', 'user-actions', 'logout' );
    $nodes = $wp_admin_bar->get_nodes();

    if ( $nodes ) {
        foreach ( $nodes as $node ) {
            if ( ! in_array( $node->id, $keep, true ) ) {
                $wp_admin_bar->remove_node( $node->id );
            }
        }
    }
}
add_action( 'admin_bar_menu', 'gs_simplify_admin_bar_for_restricted_users', 999 );


function gs_block_editing_other_pages( $allcaps, $caps, $args, $user ) {

    $allowed_pages = gs_get_user_allowed_pages( $user->ID );

    if ( empty( $allowed_pages ) ) {
        return $allcaps;
    }

    $allcaps['edit_posts']    = true;
    $allcaps['edit_pages']    = true;
    $allcaps['publish_pages'] = true;

    if (
        ! empty( $args[0] ) &&
        in_array( $args[0], array( 'edit_post', 'edit_page' ), true )
    ) {

        $post_id_being_checked = $args[2] ?? 0;

        if ( $post_id_being_checked && get_post_type( $post_id_being_checked ) === 'attachment' ) {
            return $allcaps;
        }

        if ( in_array( (int) $post_id_being_checked, $allowed_pages, true ) ) {

            foreach ( $caps as $cap ) {
                $allcaps[ $cap ] = true;
            }

        } else {

            foreach ( $caps as $cap ) {
                $allcaps[ $cap ] = false;
            }
        }
    }

    return $allcaps;
}
add_filter( 'user_has_cap', 'gs_block_editing_other_pages', 10, 4 );