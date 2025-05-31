<?php
/**
 * File: includes/class-phpspreadsheet-hooks.php
 * WordPress hooks and filters
 */

if (!defined('ABSPATH')) {
    exit;
}

class PhpSpreadsheet_Hooks {
    
    /**
     * Initialize hooks
     */
    public static function init() {
        // Plugin lifecycle hooks
        register_activation_hook(PHPSPREADSHEET_WP_PLUGIN_FILE, array(__CLASS__, 'activate'));
        register_deactivation_hook(PHPSPREADSHEET_WP_PLUGIN_FILE, array(__CLASS__, 'deactivate'));
        
        // WordPress hooks
        add_action('plugins_loaded', array(__CLASS__, 'plugins_loaded'), 10);
        add_action('init', array(__CLASS__, 'init_early'), 1);
        
        // AJAX hooks
        add_action('wp_ajax_phpspreadsheet_install', array(__CLASS__, 'ajax_install'));
        add_action('wp_ajax_phpspreadsheet_check_status', array(__CLASS__, 'ajax_check_status'));
        add_action('wp_ajax_phpspreadsheet_get_logs', array(__CLASS__, 'ajax_get_logs'));
        add_action('wp_ajax_phpspreadsheet_clear_logs', array(__CLASS__, 'ajax_clear_logs'));
        
        // Custom filters
        add_filter('phpspreadsheet_wp_autoload_paths', array(__CLASS__, 'filter_autoload_paths'), 10, 1);
        add_filter('phpspreadsheet_wp_installation_methods', array(__CLASS__, 'filter_installation_methods'), 10, 1);
    }
    
    /**
     * Plugin activation
     */
    public static function activate() {
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            deactivate_plugins(plugin_basename(PHPSPREADSHEET_WP_PLUGIN_FILE));
            wp_die(
                sprintf(
                    __('PhpSpreadsheet for WordPress requires PHP %s or higher. You are running PHP %s.', 'phpspreadsheet-wp'),
                    '7.4',
                    PHP_VERSION
                ),
                __('Plugin Activation Error', 'phpspreadsheet-wp'),
                array('back_link' => true)
            );
        }
        
        // Check required functions
        $required_functions = array('curl_init', 'json_decode', 'json_encode', 'zip_open');
        $missing_functions = array();
        
        foreach ($required_functions as $function) {
            if (!function_exists($function)) {
                $missing_functions[] = $function;
            }
        }
        
        if (!empty($missing_functions)) {
            deactivate_plugins(plugin_basename(PHPSPREADSHEET_WP_PLUGIN_FILE));
            wp_die(
                sprintf(
                    __('PhpSpreadsheet for WordPress requires the following PHP functions: %s', 'phpspreadsheet-wp'),
                    implode(', ', $missing_functions)
                ),
                __('Plugin Activation Error', 'phpspreadsheet-wp'),
                array('back_link' => true)
            );
        }
        
        // Set default options
        $default_settings = array(
            'auto_load' => true,
            'installation_method' => 'auto',
            'version' => PHPSPREADSHEET_WP_VERSION,
            'activated_time' => current_time('timestamp')
        );
        
        add_option('phpspreadsheet_wp_settings', $default_settings);
        
        // Try auto-installation
        $installer = new PhpSpreadsheet_Installer();
        $installer->install();
        
        // Set activation flag for admin notice
        set_transient('phpspreadsheet_wp_activated', true, 60);
        
        // Clear any existing caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // Log activation
        error_log('PhpSpreadsheet for WordPress activated successfully');
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Clean up transients
        delete_transient('phpspreadsheet_wp_status');
        delete_transient('phpspreadsheet_wp_activated');
        
        // Clear caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // Log deactivation
        error_log('PhpSpreadsheet for WordPress deactivated');
    }
    
    /**
     * Plugins loaded hook
     */
    public static function plugins_loaded() {
        // Load text domain for internationalization
        load_plugin_textdomain(
            'phpspreadsheet-wp',
            false,
            dirname(plugin_basename(PHPSPREADSHEET_WP_PLUGIN_FILE)) . '/languages'
        );
        
        // Initialize admin if in admin area
        if (is_admin()) {
            $admin = new PhpSpreadsheet_Admin('phpspreadsheet-wp', PHPSPREADSHEET_WP_VERSION);
        }
    }
    
    /**
     * Early initialization
     */
    public static function init_early() {
        // Load library early for other plugins
        PhpSpreadsheet_Loader::init();
        
        // Register shutdown function for cleanup
        register_shutdown_function(array(__CLASS__, 'shutdown_handler'));
    }
    
    /**
     * AJAX install handler
     */
    public static function ajax_install() {
        // Security checks
        if (!wp_verify_nonce($_POST['nonce'], 'phpspreadsheet_wp_nonce')) {
            wp_send_json_error(__('Security check failed', 'phpspreadsheet-wp'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'phpspreadsheet-wp'));
        }
        
        // Get installation method
        $method = isset($_POST['method']) ? sanitize_text_field($_POST['method']) : 'auto';
        
        // Install library
        $installer = new PhpSpreadsheet_Installer();
        $success = $installer->install($method);
        
        if ($success) {
            // Reset loader and try to load
            PhpSpreadsheet_Loader::reset();
            $loaded = PhpSpreadsheet_Loader::load_library();
            
            if ($loaded) {
                wp_send_json_success(array(
                    'message' => __('Installation completed successfully', 'phpspreadsheet-wp'),
                    'version' => PhpSpreadsheet_Loader::get_version()
                ));
            } else {
                wp_send_json_error(__('Installation completed but library failed to load', 'phpspreadsheet-wp'));
            }
        } else {
            wp_send_json_error(__('Installation failed. Please check logs for details.', 'phpspreadsheet-wp'));
        }
    }
    
    /**
     * AJAX status check
     */
    public static function ajax_check_status() {
        if (!wp_verify_nonce($_POST['nonce'], 'phpspreadsheet_wp_nonce')) {
            wp_send_json_error(__('Security check failed', 'phpspreadsheet-wp'));
        }
        
        // Force reload
        PhpSpreadsheet_Loader::reset();
        PhpSpreadsheet_Loader::load_library();
        
        wp_send_json_success(array(
            'loaded' => PhpSpreadsheet_Loader::is_loaded(),
            'version' => PhpSpreadsheet_Loader::get_version(),
            'paths' => PhpSpreadsheet_Loader::get_autoload_paths()
        ));
    }
    
    /**
     * AJAX get logs
     */
    public static function ajax_get_logs() {
        if (!wp_verify_nonce($_POST['nonce'], 'phpspreadsheet_wp_nonce')) {
            wp_send_json_error(__('Security check failed', 'phpspreadsheet-wp'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'phpspreadsheet-wp'));
        }
        
        $installer = new PhpSpreadsheet_Installer();
        $logs = $installer->get_logs(100); // Get last 100 lines
        
        wp_send_json_success(array(
            'logs' => $logs
        ));
    }
    
    /**
     * AJAX clear logs
     */
    public static function ajax_clear_logs() {
        if (!wp_verify_nonce($_POST['nonce'], 'phpspreadsheet_wp_nonce')) {
            wp_send_json_error(__('Security check failed', 'phpspreadsheet-wp'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'phpspreadsheet-wp'));
        }
        
        $installer = new PhpSpreadsheet_Installer();
        $installer->clear_logs();
        
        wp_send_json_success(array(
            'message' => __('Logs cleared successfully', 'phpspreadsheet-wp')
        ));
    }
    
    /**
     * Filter autoload paths
     */
    public static function filter_autoload_paths($paths) {
        // Allow other plugins to modify autoload paths
        return $paths;
    }
    
    /**
     * Filter installation methods
     */
    public static function filter_installation_methods($methods) {
        // Allow other plugins to add installation methods
        return $methods;
    }
    
    /**
     * Shutdown handler
     */
    public static function shutdown_handler() {
        // Clean up any temporary files or processes
        $error = error_get_last();
        if ($error && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
            // Log fatal errors
            error_log('PhpSpreadsheet WP Fatal Error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']);
        }
    }
}