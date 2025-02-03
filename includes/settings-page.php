<?php

function snn_add_menu_page() {

    $dynamic_title = get_option('snn_menu_title', 'SNN Settings');

    add_menu_page(
        'SNN Settings',
        $dynamic_title,
        'manage_options',
        'snn-settings',
        'snn_settings_page_callback',
        '',
        99
    );
}
add_action('admin_menu', 'snn_add_menu_page');

function snn_settings_page_callback() {
    $dynamic_title = get_option('snn_menu_title', 'SNN Settings');
    ?>
    <div class="wrap">
        <h1><?php echo $dynamic_title; ?> - Bricks Builder Child Theme Settings</h1>
        <div style="max-width:660px; margin-bottom:80px">
            <p>SNN-BRX Child theme is designed to give you extra tools and solutions for <a href="https://bricksbuilder.io/" target="_blank">Bricks Builder</a>.</p>
            <p>Custom Post Types, Custom Fields, Taxonomies, SMTP Mail Settings, Custom Login Design, Math Chaptcha for Login/Register, Security Features, 404 Logs, 301 Redirects and some Block Editor Features.</p>
            <p>Everything is straightforward and ready to use. For more information about Bricks Builder, use the links below.</p>
            <p><strong>Enjoy building your site.</strong></p>
            <p><a href="https://academy.bricksbuilder.io/topic/getting-started/" target="_blank"
                style="font-size: 14px; text-decoration:none; line-height:20px">Bricks Builder Docs &#10138;</a></p>
            <p><a href="https://www.youtube.com/@bricksbuilder/videos" target="_blank"
                style="font-size: 14px; text-decoration:none; line-height:20px">Bricks Builder Videos &#10138;</a></p>
        </div>
        <form method="post" action="options.php">
            <?php
            settings_fields('snn_settings_group');
            do_settings_sections('snn-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function snn_register_settings() {
    register_setting('snn_settings_group', 'snn_menu_title');

    add_settings_section(
        'snn_general_section',
        'General Settings',
        'snn_general_section_callback',
        'snn-settings'
    );

    add_settings_field(
        'snn_menu_title_field',
        'White Label Name',
        'snn_menu_title_field_callback',
        'snn-settings',
        'snn_general_section'
    );
}
add_action('admin_init', 'snn_register_settings');

function snn_general_section_callback() {
    echo '<p>The name of the SNN Main Menu in the side panel.</p>';
}

function snn_menu_title_field_callback() {
    $menu_title = get_option('snn_menu_title', 'SNN Settings');
    echo '<input type="text" name="snn_menu_title" value="' . esc_attr($menu_title) . '" class="regular-text">';
}


function mytheme_customize_register( $wp_customize ) {
    $wp_customize->add_setting( 'footer_custom_css', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
    ) );

    $wp_customize->add_control( 'footer_custom_css', array(
        'label'       => ' ',
        'section'     => 'custom_css',
        'settings'    => 'footer_custom_css',
        'type'        => 'checkbox',
        'description' => ' ',
    ) );
}
add_action( 'customize_register', 'mytheme_customize_register' );

?>
