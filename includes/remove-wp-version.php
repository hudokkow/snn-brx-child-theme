<?php


function snn_remove_wp_version() {
    $options = get_option('snn_security_options');
    if (isset($options['remove_wp_version'])) {
        return '';
    }
    return;
}
add_filter('the_generator', 'snn_remove_wp_version');


function snn_remove_wp_version_setting_field() {
    add_settings_field(
        'remove_wp_version',
        __('Remove/Hide WP Version', 'snn'),
        'snn_remove_wp_version_callback',
        'snn-security',
        'snn_security_main_section'
    );
}
add_action('admin_init', 'snn_remove_wp_version_setting_field');


function snn_remove_wp_version_callback() {
    $options = get_option('snn_security_options');
    ?>
    <div class="setting-wrapper">
        <input id="remove_wp_version" type="checkbox" name="snn_security_options[remove_wp_version]" value="1" <?php checked(isset($options['remove_wp_version']), 1); ?>>
        <label for="remove_wp_version" title="hide_element">Disable WordPress' Version</label>
        <p class="description"><?php esc_html_e('Disables WordPress\' version number from your website\'s HTML source code.', 'snn'); ?></p>
    </div>
    <?php
}
?>

