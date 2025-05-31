<?php
/**
 * File: includes/class-phpspreadsheet-admin.php
 * Handles admin-specific functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class PhpSpreadsheet_Admin
{

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->init_hooks();
    }

    /**
     * Initialize admin hooks
     */
    private function init_hooks()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_styles($hook)
    {
        // Only load on our plugin's admin page
        if ($hook !== 'settings_page_phpspreadsheet-wp') {
            return;
        }

        wp_enqueue_style(
            $this->plugin_name . '-admin',
            PHPSPREADSHEET_WP_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts($hook)
    {
        // Only load on our plugin's admin page
        if ($hook !== 'settings_page_phpspreadsheet-wp') {
            return;
        }

        wp_enqueue_script('jquery');
        wp_enqueue_script(
            $this->plugin_name . '-admin',
            PHPSPREADSHEET_WP_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            $this->version,
            false
        );

        // Localize script for AJAX
        wp_localize_script(
            $this->plugin_name . '-admin',
            'phpspreadsheet_wp_ajax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('phpspreadsheet_wp_nonce'),
                'strings' => array(
                    'installing' => __('Installing...', 'phpspreadsheet-wp'),
                    'success' => __('Installation successful!', 'phpspreadsheet-wp'),
                    'error' => __('Installation failed', 'phpspreadsheet-wp'),
                    'reload' => __('Please reload the page.', 'phpspreadsheet-wp'),
                    'retry' => __('Retry Installation', 'phpspreadsheet-wp'),
                    'checking' => __('Checking...', 'phpspreadsheet-wp'),
                    'check_status' => __('Check Status', 'phpspreadsheet-wp')
                )
            )
        );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        add_options_page(
            __('PhpSpreadsheet Settings', 'phpspreadsheet-wp'),
            __('PhpSpreadsheet', 'phpspreadsheet-wp'),
            'manage_options',
            'phpspreadsheet-wp',
            array($this, 'admin_page')
        );
    }

    /**
     * Initialize admin settings
     */
    public function admin_init()
    {
        register_setting(
            'phpspreadsheet_wp_settings',
            'phpspreadsheet_wp_settings',
            array($this, 'sanitize_settings')
        );

        add_settings_section(
            'phpspreadsheet_wp_main',
            __('PhpSpreadsheet Configuration', 'phpspreadsheet-wp'),
            array($this, 'settings_section_callback'),
            'phpspreadsheet-wp'
        );

        add_settings_field(
            'auto_load',
            __('Auto-load Library', 'phpspreadsheet-wp'),
            array($this, 'auto_load_callback'),
            'phpspreadsheet-wp',
            'phpspreadsheet_wp_main'
        );

        add_settings_field(
            'installation_method',
            __('Installation Method', 'phpspreadsheet-wp'),
            array($this, 'installation_method_callback'),
            'phpspreadsheet-wp',
            'phpspreadsheet_wp_main'
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input)
    {
        $sanitized = array();

        if (isset($input['auto_load'])) {
            $sanitized['auto_load'] = (bool) $input['auto_load'];
        }

        if (isset($input['installation_method'])) {
            $allowed_methods = array('auto', 'composer', 'precompiled');
            $sanitized['installation_method'] = in_array($input['installation_method'], $allowed_methods)
                ? $input['installation_method']
                : 'auto';
        }

        return $sanitized;
    }

    /**
     * Settings section callback
     */
    public function settings_section_callback()
    {
        echo '<p>' . __('Configure PhpSpreadsheet library settings for WordPress.', 'phpspreadsheet-wp') . '</p>';
    }

    /**
     * Auto-load callback
     */
    public function auto_load_callback()
    {
        $options = get_option('phpspreadsheet_wp_settings', array());
        $checked = isset($options['auto_load']) && $options['auto_load'] ? 'checked' : '';

        echo '<input type="checkbox" name="phpspreadsheet_wp_settings[auto_load]" value="1" ' . $checked . ' />';
        echo '<label for="phpspreadsheet_wp_settings[auto_load]"> ' . __('Load PhpSpreadsheet automatically', 'phpspreadsheet-wp') . '</label>';
        echo '<p class="description">' . __('When enabled, PhpSpreadsheet will be loaded on every WordPress page load.', 'phpspreadsheet-wp') . '</p>';
    }

    /**
     * Installation method callback
     */
    public function installation_method_callback()
    {
        $options = get_option('phpspreadsheet_wp_settings', array());
        $current = isset($options['installation_method']) ? $options['installation_method'] : 'auto';

        $methods = array(
            'auto' => __('Automatic (Try Composer, then Precompiled)', 'phpspreadsheet-wp'),
            'composer' => __('Composer Only', 'phpspreadsheet-wp'),
            'precompiled' => __('Precompiled Only', 'phpspreadsheet-wp')
        );

        foreach ($methods as $value => $label) {
            $checked = ($current === $value) ? 'checked' : '';
            echo '<input type="radio" name="phpspreadsheet_wp_settings[installation_method]" value="' . esc_attr($value) . '" ' . $checked . ' />';
            echo '<label> ' . esc_html($label) . '</label><br>';
        }

        echo '<p class="description">' . __('Choose how PhpSpreadsheet should be installed.', 'phpspreadsheet-wp') . '</p>';
    }

    /**
     * Admin page
     */
    public function admin_page()
    {
        // Include the template
        include PHPSPREADSHEET_WP_PLUGIN_DIR . 'templates/admin-page.php';
    }

    /**
     * Admin notices
     */
    public function admin_notices()
    {
        // Show activation notice
        if (get_transient('phpspreadsheet_wp_activated')) {
            delete_transient('phpspreadsheet_wp_activated');

            $plugin = PhpSpreadsheet_WordPress_Plugin::get_instance();
            $loaded = $plugin->is_library_loaded() ? true : false;

            $class = $loaded ? 'notice-success' : 'notice-warning';
            $message = $loaded
                ? __('PhpSpreadsheet for WordPress activated successfully!', 'phpspreadsheet-wp')
                : __('PhpSpreadsheet for WordPress activated but library installation failed. Please check settings.', 'phpspreadsheet-wp');

            printf('<div class="notice %s is-dismissible"><p>%s</p></div>', esc_attr($class), esc_html($message));
        }

        // Show library status notice on non-plugin pages
        $screen = get_current_screen();
        if ($screen && $screen->id !== 'settings_page_phpspreadsheet-wp') {
            $plugin = PhpSpreadsheet_WordPress_Plugin::get_instance();
            if (!$plugin->is_library_loaded()) {
                $settings_url = admin_url('options-general.php?page=phpspreadsheet-wp');
                printf(
                    '<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
                    __('PhpSpreadsheet library is not installed.', 'phpspreadsheet-wp'),
                    esc_url($settings_url),
                    __('Install now', 'phpspreadsheet-wp')
                );
            }
        }
    }
}