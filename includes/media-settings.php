<?php
function snn_add_media_submenu() {
    add_submenu_page(
        'snn-settings',
        'Media Settings',
        'Media Settings',
        'manage_options',
        'snn-media-settings',
        'snn_render_media_settings'
    );
}
add_action('admin_menu', 'snn_add_media_submenu');

function snn_render_media_settings() {
    ?>
    <div class="wrap">
        <h1>Media Settings</h1>
        <form method="post" action="options.php">
            <?php
                settings_fields('snn_media_settings_group');
                do_settings_sections('snn-media-settings');
                submit_button();
            ?>
        </form>
    </div>
    <?php
}

function snn_register_media_settings() {
    register_setting(
        'snn_media_settings_group',
        'snn_media_settings',
        'snn_sanitize_media_settings'
    );

    add_settings_section(
        'snn_media_settings_section',
        'Media Settings',
        'snn_media_settings_section_callback',
        'snn-media-settings'
    );

    add_settings_field(
        'redirect_media_library',
        'Redirect Media Library Grid View to List View',
        'snn_redirect_media_library_callback',
        'snn-media-settings',
        'snn_media_settings_section'
    );

    add_settings_field(
        'media_categories',
        'Enable Media Categories (BETA)',
        'snn_media_categories_callback',
        'snn-media-settings',
        'snn_media_settings_section'
    );
}
add_action('admin_init', 'snn_register_media_settings');

function snn_sanitize_media_settings($input) {
    $sanitized = array();
    $sanitized['redirect_media_library'] = isset($input['redirect_media_library']) && $input['redirect_media_library'] ? 1 : 0;
    $sanitized['media_categories'] = isset($input['media_categories']) && $input['media_categories'] ? 1 : 0;
    return $sanitized;
}

function snn_media_settings_section_callback() {
    echo '<p>Configure media-related settings below.</p>';
}

function snn_redirect_media_library_callback() {
    $options = get_option('snn_media_settings');
    ?>
    <input type="checkbox" name="snn_media_settings[redirect_media_library]" value="1" <?php checked(1, isset($options['redirect_media_library']) ? $options['redirect_media_library'] : 0); ?>>
    <p>Media list view default.</p>
    <?php
}

function snn_media_categories_callback() {
    $options = get_option('snn_media_settings');
    ?>
    <input type="checkbox" name="snn_media_settings[media_categories]" value="1" <?php checked(1, isset($options['media_categories']) ? $options['media_categories'] : 0); ?>>
    <p>Enable Media Categories with drag-and-drop functionality.</p>
    <?php
}

function snn_redirect_media_library_grid_to_list() {
    $options = get_option('snn_media_settings');
    if (
        isset($options['redirect_media_library']) &&
        $options['redirect_media_library'] &&
        is_admin() &&
        strpos($_SERVER['REQUEST_URI'], 'upload.php') !== false
    ) {
        $current_mode = isset($_GET['mode']) ? $_GET['mode'] : '';
        if ($current_mode !== 'list') {
            $list_mode_url = remove_query_arg('mode');
            $list_mode_url = add_query_arg('mode', 'list', $list_mode_url);
            wp_redirect($list_mode_url);
            exit;
        }
    }
}
add_action('admin_init', 'snn_redirect_media_library_grid_to_list');

function snn_register_media_taxonomy_categories() {
    $options = get_option('snn_media_settings');
    if (isset($options['media_categories']) && $options['media_categories']) {
        $labels = array(
            'name'              => _x('Media Categories', 'taxonomy general name', 'textdomain'),
            'singular_name'     => _x('Media Category', 'taxonomy singular name', 'textdomain'),
            'search_items'      => __('Search Media Categories', 'textdomain'),
            'all_items'         => __('All Media Categories', 'textdomain'),
            'parent_item'       => __('Parent Media Category', 'textdomain'),
            'parent_item_colon' => __('Parent Media Category:', 'textdomain'),
            'edit_item'         => __('Edit Media Category', 'textdomain'),
            'update_item'       => __('Update Media Category', 'textdomain'),
            'add_new_item'      => __('Add New Media Category', 'textdomain'),
            'new_item_name'     => __('New Media Category Name', 'textdomain'),
            'menu_name'         => __('Media Categories', 'textdomain'),
        );

        $args = array(
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'query_var'             => false,
            'rewrite'               => false,
            'public'                => false,
            'show_in_rest'          => false,
            'update_count_callback' => '_update_generic_term_count'
        );

        register_taxonomy('media_taxonomy_categories', 'attachment', $args);
    }
}
add_action('init', 'snn_register_media_taxonomy_categories');

function snn_add_custom_css_js_to_media_page() {
    if (!function_exists('get_current_screen')) {
        return;
    }
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'upload') {
        return;
    }

    $options = get_option('snn_media_settings');

    if (isset($options['redirect_media_library']) && $options['redirect_media_library']) {
        ?>
        <style>
        #view-switch-grid {
            display: none;
        }
        table.media .column-title .media-icon img {
            max-width: 100px;
            width: 100%;
            
        }
        .media-icon {
            width: 100%;
            text-align: left;
        }
        .row-actions {
            margin-left:0px !important;
        }
        #the-list tr {
            cursor: grab;
        }
        </style>
        <?php
    }

    if (isset($options['media_categories']) && $options['media_categories']) {
        ?>
        <style>

            #media-categories-manager {
                padding-right: 10px;
                padding-top:25px;
                border-right: 1px solid #ddd;
                margin-bottom: 20px;
                position:relative;
            }

            #wpbody{
                display:grid;
                grid-template-columns:220px 1fr;
                gap:20px;
            }
            .media-categories-wrapper{
                position:fixed;
                width:200px
            }

            #media-categories-manager h2 {
                margin-top: 0;
                
            }
            #media-categories-list {
                list-style: none;
                padding: 0;
            }
            #media-categories-list li {
                padding: 10px 8px;
                background: #fff;
                margin-bottom: 10px;
                cursor: move;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            #media-categories-list li .delete-category {
                color: red;
                cursor: pointer;
                margin-left: 10px;
            }
            #add-category-form input[type="text"] {
                padding: 5px;
                width: 100%;
                margin-bottom:5px;
            }
            #add-category-form input[type="submit"] {
                padding: 5px 10px;
            }
            .drag-over {
                border: 2px dashed #000;
            }
            #media-categories-list li .category-name {
                flex: 1;
            }
            #media-categories-list li .category-count {
                margin-left: 10px;
                font-size: 0.9em;
                color: #666;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const rows = document.querySelectorAll('#the-list tr');
                rows.forEach(function(row) {
                    row.setAttribute('draggable', 'true');
                });
            });
        </script>

        <div id="media-categories-manager">
            <div class="media-categories-wrapper">
                <h2>Media Categories</h2>
                <ul id="media-categories-list">
                    <?php
                    $terms = get_terms(array(
                        'taxonomy'   => 'media_taxonomy_categories',
                        'hide_empty' => false,
                    ));
                    if (!empty($terms) && !is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            $term_id   = esc_attr($term->term_id);
                            $term_name = esc_html($term->name);
                            $count     = intval($term->count);
                            echo '<li data-id="' . $term_id . '">';
                            echo '<span class="category-name" data-id="' . $term_id . '" style="cursor:pointer;">' 
                                 . $term_name 
                                 . '</span>';
                            echo '<span class="category-count">(' . $count . ')</span>';
                            echo '<span class="delete-category" data-id="' . $term_id . '">✖</span>';
                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
                <form id="add-category-form">
                    <input type="text" id="new-category-name" placeholder="New Category Name" required>
                    <input type="submit" value="Add Category">
                </form>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.getElementById('add-category-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const categoryName = document.getElementById('new-category-name').value.trim();
                if (categoryName === '') return;

                const xhr = new XMLHttpRequest();
                xhr.open('POST', ajaxurl, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                const termId = response.data.term_id;
                                const termName = response.data.name;
                                const li = document.createElement('li');
                                li.setAttribute('data-id', termId);

                                const nameSpan = document.createElement('span');
                                nameSpan.classList.add('category-name');
                                nameSpan.style.cursor = 'pointer';
                                nameSpan.setAttribute('data-id', termId);
                                nameSpan.textContent = termName;

                                const countSpan = document.createElement('span');
                                countSpan.classList.add('category-count');
                                countSpan.textContent = '(0)';

                                const deleteSpan = document.createElement('span');
                                deleteSpan.classList.add('delete-category');
                                deleteSpan.setAttribute('data-id', termId);
                                deleteSpan.textContent = '✖';

                                li.appendChild(nameSpan);
                                li.appendChild(countSpan);
                                li.appendChild(deleteSpan);

                                document.getElementById('media-categories-list').appendChild(li);
                                document.getElementById('new-category-name').value = '';
                            } else {
                                alert(response.data);
                            }
                        } catch (err) {
                            console.error(err);
                        }
                    }
                };
                xhr.send('action=snn_add_media_category&category_name=' + encodeURIComponent(categoryName) + '&nonce=' + '<?php echo wp_create_nonce("snn_media_categories_nonce"); ?>');
            });

            document.getElementById('media-categories-list').addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('delete-category')) {
                    const termId = e.target.getAttribute('data-id');
                    if (!confirm('Are you sure you want to delete this category?')) return;

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', ajaxurl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    const li = e.target.parentElement;
                                    li.parentElement.removeChild(li);
                                } else {
                                    alert(response.data);
                                }
                            } catch (err) {
                                console.error(err);
                            }
                        }
                    };
                    xhr.send('action=snn_delete_media_category&term_id=' + encodeURIComponent(termId) + '&nonce=' + '<?php echo wp_create_nonce("snn_media_categories_nonce"); ?>');
                }
            });

            document.getElementById('media-categories-list').addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('category-name')) {
                    const termId = e.target.getAttribute('data-id');
                    const url = new URL(window.location.href);
                    url.searchParams.set('media_taxonomy_categories', termId);
                    window.location.href = url.toString();
                }
            });

            const categories = document.querySelectorAll('#media-categories-list li');
            const mediaRows = document.querySelectorAll('#the-list tr');

            categories.forEach(function(category) {
                category.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    category.classList.add('drag-over');
                });

                category.addEventListener('dragleave', function(e) {
                    category.classList.remove('drag-over');
                });

                category.addEventListener('drop', function(e) {
                    e.preventDefault();
                    category.classList.remove('drag-over');
                    const termId = category.getAttribute('data-id');
                    const mediaId = e.dataTransfer.getData('text/plain');

                    if (mediaId && termId) {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', ajaxurl, true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4 && xhr.status === 200) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (!response.success) {
                                        alert(response.data);
                                    } else {
                                        let countSpan = category.querySelector('.category-count');
                                        if (countSpan) {
                                            let countText = countSpan.textContent.replace(/[()]/g, '');
                                            let currentCount = parseInt(countText, 10);
                                            currentCount++;
                                            countSpan.textContent = '(' + currentCount + ')';
                                        }
                                    }
                                } catch (err) {
                                    console.error(err);
                                }
                            }
                        };
                        xhr.send('action=snn_assign_media_category&media_id=' + encodeURIComponent(mediaId) + '&term_id=' + encodeURIComponent(termId) + '&nonce=' + '<?php echo wp_create_nonce("snn_media_categories_nonce"); ?>');
                    }
                });
            });

            mediaRows.forEach(function(row) {
                row.addEventListener('dragstart', function(e) {
                    const mediaId = row.getAttribute('id').replace('post-', '');
                    e.dataTransfer.setData('text/plain', mediaId);
                });
            });
        });
        </script>
        <?php
    }
}
add_action('admin_head', 'snn_add_custom_css_js_to_media_page');

function snn_add_media_categories_manager_dom() {
    if (!function_exists('get_current_screen')) {
        return;
    }
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'upload') {
        return;
    }

    $options = get_option('snn_media_settings');
    if (isset($options['media_categories']) && $options['media_categories']) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wpBodyContent = document.getElementById('wpbody-content');
            const mediaManager = document.getElementById('media-categories-manager');
            if (wpBodyContent && mediaManager) {
                wpBodyContent.parentNode.insertBefore(mediaManager, wpBodyContent);
            }
        });
        </script>
        <?php
    }
}
add_action('admin_footer', 'snn_add_media_categories_manager_dom');

function snn_add_media_category() {
    check_ajax_referer('snn_media_categories_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized user');
    }

    $category_name = sanitize_text_field($_POST['category_name']);
    if (empty($category_name)) {
        wp_send_json_error('Category name cannot be empty');
    }

    $term = wp_insert_term($category_name, 'media_taxonomy_categories');
    if (is_wp_error($term)) {
        wp_send_json_error($term->get_error_message());
    }

    $term_obj = get_term($term['term_id'], 'media_taxonomy_categories');
    if (is_wp_error($term_obj) || !$term_obj) {
        wp_send_json_error('Error fetching new category.');
    }

    wp_send_json_success(array(
        'term_id' => $term_obj->term_id,
        'name'    => $term_obj->name
    ));
}
add_action('wp_ajax_snn_add_media_category', 'snn_add_media_category');

function snn_delete_media_category() {
    check_ajax_referer('snn_media_categories_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized user');
    }

    $term_id = intval($_POST['term_id']);
    if (!$term_id) {
        wp_send_json_error('Invalid term ID');
    }

    $result = wp_delete_term($term_id, 'media_taxonomy_categories');
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_send_json_success();
}
add_action('wp_ajax_snn_delete_media_category', 'snn_delete_media_category');

function snn_assign_media_category() {
    check_ajax_referer('snn_media_categories_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized user');
    }

    $media_id = intval($_POST['media_id']);
    $term_id = intval($_POST['term_id']);

    if (!$media_id || !$term_id) {
        wp_send_json_error('Invalid media ID or term ID');
    }

    $result = wp_set_object_terms($media_id, array($term_id), 'media_taxonomy_categories', true);
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_send_json_success();
}
add_action('wp_ajax_snn_assign_media_category', 'snn_assign_media_category');

function snn_filter_media_by_taxonomy($query) {
    global $pagenow;

    if (
        is_admin() &&
        $pagenow === 'upload.php' &&
        $query->is_main_query() &&
        isset($_GET['media_taxonomy_categories']) &&
        !empty($_GET['media_taxonomy_categories'])
    ) {
        $term_id = intval($_GET['media_taxonomy_categories']);
        if ($term_id > 0) {
            $tax_query = array(
                array(
                    'taxonomy' => 'media_taxonomy_categories',
                    'field'    => 'term_id',
                    'terms'    => $term_id
                )
            );
            $query->set('tax_query', $tax_query);
        }
    }
}
add_filter('pre_get_posts', 'snn_filter_media_by_taxonomy');
?>
