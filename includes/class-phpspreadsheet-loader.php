<?php
/**
 * File: includes/class-phpspreadsheet-loader.php
 * Handles library loading and autoloading
 */

if (!defined('ABSPATH')) {
    exit;
}

class PhpSpreadsheet_Loader {
    
    /**
     * Autoload paths to check
     */
    private static $autoload_paths = array();
    
    /**
     * Library loaded status
     */
    private static $loaded = null;
    
    /**
     * Library version
     */
    private static $version = null;
    
    /**
     * Initialize loader
     */
    public static function init() {
        self::$autoload_paths = array(
            PHPSPREADSHEET_WP_PLUGIN_DIR . 'vendor/autoload.php',
            ABSPATH . 'vendor/autoload.php',
            get_template_directory() . '/vendor/autoload.php',
            get_stylesheet_directory() . '/vendor/autoload.php'
        );
        
        // Allow other plugins to add paths
        self::$autoload_paths = apply_filters('phpspreadsheet_wp_autoload_paths', self::$autoload_paths);
        
        // Try to load library
        self::load_library();
    }
    
    /**
     * Load PhpSpreadsheet library
     */
    public static function load_library() {
        if (self::$loaded !== null) {
            return self::$loaded;
        }
        
        // Check if already loaded by another plugin
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            self::$loaded = true;
            self::detect_version();
            do_action('phpspreadsheet_wp_loaded');
            return true;
        }
        
        foreach (self::$autoload_paths as $path) {
            if (file_exists($path)) {
                try {
                    require_once $path;
                    
                    // Test if PhpSpreadsheet is available
                    if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                        self::$loaded = true;
                        self::detect_version();
                        
                        // Fire action hook
                        do_action('phpspreadsheet_wp_loaded');
                        
                        // Log successful loading
                        self::log_message('PhpSpreadsheet loaded from: ' . $path);
                        
                        return true;
                    }
                } catch (Exception $e) {
                    self::log_message('Error loading from ' . $path . ': ' . $e->getMessage());
                }
            }
        }
        
        self::$loaded = false;
        self::log_message('PhpSpreadsheet library not found in any autoload path');
        return false;
    }
    
    /**
     * Check if library is loaded
     */
    public static function is_loaded() {
        if (self::$loaded === null) {
            self::load_library();
        }
        return self::$loaded === true;
    }
    
    /**
     * Get library version
     */
    public static function get_version() {
        if (!self::is_loaded()) {
            return false;
        }
        
        if (self::$version !== null) {
            return self::$version;
        }
        
        return self::detect_version();
    }
    
    /**
     * Detect library version
     */
    private static function detect_version() {
        try {
            $reflection = new ReflectionClass('PhpOffice\PhpSpreadsheet\Spreadsheet');
            $filename = $reflection->getFileName();
            
            // Try to get version from composer.json
            $possible_composer_paths = array(
                dirname($filename) . '/../../composer.json',
                dirname($filename) . '/../../../composer.json',
                dirname($filename) . '/../../../../composer.json'
            );
            
            foreach ($possible_composer_paths as $composer_file) {
                if (file_exists($composer_file)) {
                    $composer_data = json_decode(file_get_contents($composer_file), true);
                    if (isset($composer_data['version'])) {
                        self::$version = $composer_data['version'];
                        return self::$version;
                    }
                    if (isset($composer_data['name']) && $composer_data['name'] === 'phpoffice/phpspreadsheet') {
                        self::$version = 'Installed';
                        return self::$version;
                    }
                }
            }
            
            // Try to get version from class constants
            if (defined('PhpOffice\PhpSpreadsheet\Settings::VERSION')) {
                self::$version = constant('PhpOffice\PhpSpreadsheet\Settings::VERSION');
                return self::$version;
            }
            
            self::$version = 'Unknown';
            return self::$version;
        } catch (Exception $e) {
            self::$version = 'Unknown';
            return self::$version;
        }
    }
    
    /**
     * Add custom autoload path
     */
    public static function add_autoload_path($path) {
        if (!in_array($path, self::$autoload_paths)) {
            array_unshift(self::$autoload_paths, $path);
        }
    }
    
    /**
     * Get all autoload paths
     */
    public static function get_autoload_paths() {
        return self::$autoload_paths;
    }
    
    /**
     * Reset loader state (for testing)
     */
    public static function reset() {
        self::$loaded = null;
        self::$version = null;
    }
    
    /**
     * Log message
     */
    private static function log_message($message) {
        if (defined('PHPSPREADSHEET_WP_PLUGIN_DIR')) {
            $log_file = PHPSPREADSHEET_WP_PLUGIN_DIR . 'logs/loader.log';
            $timestamp = current_time('Y-m-d H:i:s');
            $log_entry = "[$timestamp] $message" . PHP_EOL;
            
            // Ensure logs directory exists
            $log_dir = dirname($log_file);
            if (!file_exists($log_dir)) {
                wp_mkdir_p($log_dir);
            }
            
            file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
        }
    }
}