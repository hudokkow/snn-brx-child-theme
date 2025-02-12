<?php

function snn_disable_bundled_theme_install() {
    $options = get_option('snn_security_options');
    if (isset($options['disable_bundled_theme_install'])) {
        define('CORE_UPGRADE_SKIP_NEW_BUNDLED', true);
    }
}
add_action('init', 'snn_disable_bundled_theme_install');

function snn_disable_bundled_theme_install_setting_field() {
    add_settings_field(
        'disable_bundled_theme_install',
        __('<label>Disable Bundled Theme Install</label>', 'snn'),
        'snn_disable_bundled_theme_install_callback',
        'snn-security',
        'snn_security_main_section'
    );
}
add_action('admin_init', 'snn_disable_bundled_theme_install_setting_field');

function snn_disable_bundled_theme_install_callback() {
    $options = get_option('snn_security_options');
    ?>
    <div class="setting-wrapper">
        <input id="disable_bundled_theme_install" type="checkbox" name="snn_security_options[disable_bundled_theme_install]" value="1" <?php checked(isset($options['disable_bundled_theme_install']), 1); ?>>
        <label for="disable_bundled_theme_install" title="hide_element">Disable Bundled Theme Install</label>
        <p class="description"><?php esc_html_e('Disables bundled theme install when upgrading WordPress core.', 'snn'); ?></p>
    </div>
    <?php
}
?>

