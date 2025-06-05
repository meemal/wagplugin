<?php

// Register a custom post status for "Disabled"
function register_disabled_post_status() {
    register_post_status( 'disabled', array(
        'label'                     => _x( 'Disabled', 'post' ),
        'public'                    => true,
        'internal'                  => true,
        'exclude_from_search'       => true,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Disabled <span class="count">(%s)</span>', 'Disabled <span class="count">(%s)</span>' ),
    ) );
}
add_action( 'init', 'register_disabled_post_status' );

add_action('admin_footer-post.php', 'add_disabled_status_to_dropdown');
add_action('admin_footer-post-new.php', 'add_disabled_status_to_dropdown');

function add_disabled_status_to_dropdown() {
    global $post;
    if ($post->post_type !== 'directory_listing') {
        return;
    }
    ?>
    <script>
        jQuery(document).ready(function($) {
            var disabledOption = $('<option>').val('disabled').text('Disabled');
            $('#post_status').append(disabledOption);
            if ('disabled' === '<?php echo $post->post_status; ?>') {
                $('#post_status').val('disabled');
            }
        });
    </script>
    <?php
}


add_filter('display_post_states', function($states, $post) {
    if (get_post_status($post) === 'disabled') {
        $states[] = __('Disabled');
    }
    return $states;
}, 10, 2);


add_action('restrict_manage_posts', function() {
    global $typenow, $wp_query;
    if ($typenow === 'directory_listing') {
        $selected = isset($_GET['post_status']) ? $_GET['post_status'] : '';
        $statuses = array(
            'disabled' => 'Disabled'
        );
        foreach ($statuses as $status => $label) {
            echo '<option value="' . esc_attr($status) . '" ' . selected($selected, $status, false) . '>' . esc_html($label) . '</option>';
        }
    }
});



add_action('admin_footer-post.php', 'add_disabled_to_status_dropdown');
add_action('admin_footer-post-new.php', 'add_disabled_to_status_dropdown');

function add_disabled_to_status_dropdown() {
    global $post;
    if ($post->post_type !== 'directory_listing') return;

    ?>
    <script>
        jQuery(document).ready(function($) {
            const $statusSelect = $('#post_status');
            if (!$statusSelect.find('option[value="disabled"]').length) {
                $statusSelect.append('<option value="disabled">Disabled</option>');
            }
            <?php if (get_post_status($post) === 'disabled') : ?>
                $('#post_status').val('disabled');
            <?php endif; ?>
        });
    </script>
    <?php
}

add_filter('display_post_states', function($states, $post) {
    if ($post->post_status === 'disabled') {
        $states[] = __('Disabled');
    }
    return $states;
}, 10, 2);

add_action('admin_footer-edit.php', function() {
    global $post_type;
    if ($post_type !== 'directory_listing') return;
    ?>
    <script>
        jQuery(document).ready(function($) {
            $('select[name="_status"]').each(function() {
                const $dropdown = $(this);
                if (!$dropdown.find('option[value="disabled"]').length) {
                    $dropdown.append('<option value="disabled">Disabled</option>');
                }
            });
        });
    </script>
    <?php
});



add_action('pre_get_posts', function($query) {
    if (
        !is_admin() &&
        $query->is_main_query() &&
        is_singular('directory_listing')
    ) {
        $post_id = get_queried_object_id();
        $post = get_post($post_id);

        if (
            $post &&
            $post->post_status === 'disabled' &&
            get_current_user_id() === (int) $post->post_author
        ) {
            $query->set('post_status', ['publish', 'pending', 'disabled']);
        }
    }
});

