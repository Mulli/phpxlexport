<?php
/**
 * File: uninstall.php
 * Handles plugin uninstallation
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options
delete_option('phpspreadsheet_wp_settings');

// Remove transients
delete_transient('phpspreadsheet_wp_status');

// Remove plugin directory (optional - user might want to keep downloaded library)
$plugin_dir = WP_PLUGIN_DIR . '/phpspreadsheet-wp/';
$keep_library = get_option('phpspreadsheet_wp_keep_on_uninstall', false);

if (!$keep_library) {
    // Recursively remove plugin directory
    function phpspreadsheet_wp_remove_directory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            if (!phpspreadsheet_wp_remove_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        
        return rmdir($dir);
    }
    
    // Remove vendor directory (downloaded libraries)
    phpspreadsheet_wp_remove_directory($plugin_dir . 'vendor');
    phpspreadsheet_wp_remove_directory($plugin_dir . 'temp');
    phpspreadsheet_wp_remove_directory($plugin_dir . 'logs');
}

// Clear any cached data
wp_cache_flush();
