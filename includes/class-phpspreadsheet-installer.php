<?php
/**
 * File: includes/class-phpspreadsheet-installer.php
 * Handles library installation
 */

if (!defined('ABSPATH')) {
    exit;
}

class PhpSpreadsheet_Installer {
    
    private $plugin_dir;
    private $log_file;
    private $temp_dir;
    private $vendor_dir;
    
    public function __construct() {
        $this->plugin_dir = PHPSPREADSHEET_WP_PLUGIN_DIR;
        $this->log_file = $this->plugin_dir . 'logs/installation.log';
        $this->temp_dir = $this->plugin_dir . 'temp/';
        $this->vendor_dir = $this->plugin_dir . 'vendor/';
    }
    
    /**
     * Install PhpSpreadsheet library
     */
    public function install($method = 'auto') {
        $this->log('Starting PhpSpreadsheet installation with method: ' . $method);
        
        // Create directories
        $this->create_directories();
        
        switch ($method) {
            case 'composer':
                return $this->install_via_composer();
                
            case 'precompiled':
                return $this->install_precompiled();
                
            case 'auto':
            default:
                // Try Composer first
                if ($this->is_composer_available()) {
                    $this->log('Attempting Composer installation...');
                    if ($this->install_via_composer()) {
                        $this->log('Composer installation successful');
                        return true;
                    }
                    $this->log('Composer installation failed, trying precompiled...');
                }
                
                // Fallback to precompiled
                $this->log('Attempting precompiled installation...');
                if ($this->install_precompiled()) {
                    $this->log('Precompiled installation successful');
                    return true;
                }
                
                $this->log('All installation methods failed');
                return false;
        }
    }
    
    /**
     * Create necessary directories
     */
    private function create_directories() {
        $dirs = array(
            $this->vendor_dir,
            $this->temp_dir,
            dirname($this->log_file)
        );
        
        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
                
                // Add security files
                $htaccess = "Order deny,allow\nDeny from all\n";
                file_put_contents($dir . '.htaccess', $htaccess);
                file_put_contents($dir . 'index.php', '<?php // Silence is golden');
            }
        }
    }
    
    /**
     * Check if Composer is available
     */
    private function is_composer_available() {
        $composer_paths = array('composer', 'composer.phar', '/usr/local/bin/composer', '/usr/bin/composer');
        
        foreach ($composer_paths as $composer) {
            exec("$composer --version 2>&1", $output, $return_code);
            if ($return_code === 0) {
                $this->log('Composer found at: ' . $composer);
                return true;
            }
        }
        
        $this->log('Composer not found in system PATH');
        return false;
    }
    
    /**
     * Install via Composer
     */
    private function install_via_composer() {
        $composer_json = array(
            'name' => 'wordpress/phpspreadsheet-wp',
            'description' => 'PhpSpreadsheet for WordPress',
            'type' => 'wordpress-plugin',
            'require' => array(
                'phpoffice/phpspreadsheet' => '^1.29'
            ),
            'config' => array(
                'vendor-dir' => 'vendor',
                'optimize-autoloader' => true,
                'sort-packages' => true
            ),
            'minimum-stability' => 'stable',
            'prefer-stable' => true
        );
        
        $composer_file = $this->plugin_dir . 'composer.json';
        if (file_put_contents($composer_file, json_encode($composer_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
            $this->log('Failed to create composer.json');
            return false;
        }
        
        $old_cwd = getcwd();
        chdir($this->plugin_dir);
        
        // Try different composer commands
        $commands = array(
            'composer install --no-dev --optimize-autoloader --no-interaction',
            'php composer.phar install --no-dev --optimize-autoloader --no-interaction',
            '/usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction'
        );
        
        $success = false;
        $last_output = '';
        
        foreach ($commands as $command) {
            $this->log('Trying command: ' . $command);
            exec($command . ' 2>&1', $output, $return_code);
            $last_output = implode("\n", $output);
            
            if ($return_code === 0 && file_exists($this->vendor_dir . 'autoload.php')) {
                $success = true;
                $this->log('Command successful: ' . $command);
                break;
            }
            
            $this->log('Command failed: ' . $command . ' - ' . $last_output);
            $output = array(); // Reset for next attempt
        }
        
        chdir($old_cwd);
        
        if ($success) {
            $this->log('Composer installation completed successfully');
            return true;
        } else {
            $this->log('All Composer commands failed. Last output: ' . $last_output);
            return false;
        }
    }
    
    /**
     * Install precompiled version
     */
    private function install_precompiled() {
        // Try to get latest version from GitHub API
        $api_url = 'https://api.github.com/repos/PHPOffice/PhpSpreadsheet/releases/latest';
        $download_url = 'https://github.com/PHPOffice/PhpSpreadsheet/archive/refs/tags/1.29.0.zip';
        $version = '1.29.0';
        
        $this->log('Checking GitHub API for latest version...');
        $api_response = wp_remote_get($api_url, array(
            'timeout' => 30,
            'user-agent' => 'PhpSpreadsheet-WP-Plugin/' . PHPSPREADSHEET_WP_VERSION
        ));
        
        if (!is_wp_error($api_response) && wp_remote_retrieve_response_code($api_response) === 200) {
            $release_data = json_decode(wp_remote_retrieve_body($api_response), true);
            if (isset($release_data['zipball_url']) && isset($release_data['tag_name'])) {
                $download_url = $release_data['zipball_url'];
                $version = $release_data['tag_name'];
                $this->log('Using latest version from GitHub: ' . $version);
            }
        } else {
            $this->log('GitHub API failed, using fallback version: ' . $version);
        }
        
        $temp_file = $this->temp_dir . 'phpspreadsheet.zip';
        $extract_dir = $this->vendor_dir . 'phpoffice/phpspreadsheet/';
        
        // Download with extended timeout
        $this->log('Downloading PhpSpreadsheet from: ' . $download_url);
        
        $response = wp_remote_get($download_url, array(
            'timeout' => 300,
            'redirection' => 5,
            'httpversion' => '1.1',
            'user-agent' => 'PhpSpreadsheet-WP-Plugin/' . PHPSPREADSHEET_WP_VERSION,
            'headers' => array(
                'Accept' => 'application/zip'
            )
        ));
        
        if (is_wp_error($response)) {
            $this->log('Download failed: ' . $response->get_error_message());
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $this->log('Download failed with HTTP code: ' . $response_code);
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            $this->log('Downloaded file is empty');
            return false;
        }
        
        // Save the downloaded file
        if (file_put_contents($temp_file, $body) === false) {
            $this->log('Failed to save downloaded file');
            return false;
        }
        
        // Verify file size
        $file_size = filesize($temp_file);
        if ($file_size < 10000) { // Less than 10KB is probably an error
            $this->log('Downloaded file too small: ' . $file_size . ' bytes');
            return false;
        }
        
        $this->log('Downloaded file size: ' . $file_size . ' bytes');
        
        // Extract the file
        $temp_extract = $this->temp_dir . 'extract/';
        wp_mkdir_p($temp_extract);
        
        $result = unzip_file($temp_file, $temp_extract);
        
        if (is_wp_error($result)) {
            $this->log('Extraction failed: ' . $result->get_error_message());
            return false;
        }
        
        // Find the extracted directory
        $extracted_dirs = glob($temp_extract . '*', GLOB_ONLYDIR);
        if (empty($extracted_dirs)) {
            $this->log('No directories found after extraction');
            return false;
        }
        
        $source_dir = $extracted_dirs[0];
        $this->log('Extracted to: ' . $source_dir);
        
        // Create destination directory
        wp_mkdir_p($extract_dir);
        
        // Copy files to final location
        if ($this->copy_directory($source_dir, $extract_dir)) {
            // Create our custom autoloader
            $this->create_autoloader();
            
            // Cleanup temporary files
            $this->cleanup_temp_files();
            
            $this->log('Precompiled installation completed successfully (version: ' . $version . ')');
            return true;
        } else {
            $this->log('Failed to copy extracted files to final location');
            return false;
        }
    }
    
    /**
     * Create custom autoloader
     */
    private function create_autoloader() {
        $autoloader_content = '<?php
/**
 * PhpSpreadsheet Autoloader for WordPress
 * Generated by PhpSpreadsheet WordPress Plugin
 */

if (!defined("ABSPATH")) {
    exit;
}

// Register the autoloader
spl_autoload_register(function ($class) {
    if (strpos($class, \'PhpOffice\\PhpSpreadsheet\') === 0) {
        $php_path = "PhpOffice\\PhpSpreadsheet\\";
        $file = __DIR__ . "/phpoffice/phpspreadsheet/src/" . str_replace(\'\\\\\', \'/\', substr($class, strlen($php_path))) . ".php";
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});

// Try to load Composer autoloader if available
$composer_autoload = __DIR__ . "/phpoffice/phpspreadsheet/vendor/autoload.php";
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
}

// Set default timezone if not set (PhpSpreadsheet requirement)
if (!ini_get("date.timezone")) {
    date_default_timezone_set("UTC");
}
';
        
        $autoloader_file = $this->vendor_dir . 'autoload.php';
        if (file_put_contents($autoloader_file, $autoloader_content) === false) {
            $this->log('Failed to create autoloader');
            return false;
        }
        
        $this->log('Custom autoloader created successfully');
        return true;
    }
    
    /**
     * Copy directory recursively
     */
    private function copy_directory($src, $dst) {
        if (!is_dir($src)) {
            $this->log('Source is not a directory: ' . $src);
            return false;
        }
        
        if (!file_exists($dst)) {
            wp_mkdir_p($dst);
        }
        
        $dir = opendir($src);
        if (!$dir) {
            $this->log('Failed to open source directory: ' . $src);
            return false;
        }
        
        while (false !== ($file = readdir($dir))) {
            if ($file !== '.' && $file !== '..') {
                $src_path = $src . '/' . $file;
                $dst_path = $dst . '/' . $file;
                
                if (is_dir($src_path)) {
                    if (!$this->copy_directory($src_path, $dst_path)) {
                        closedir($dir);
                        return false;
                    }
                } else {
                    if (!copy($src_path, $dst_path)) {
                        $this->log('Failed to copy file: ' . $src_path);
                        closedir($dir);
                        return false;
                    }
                }
            }
        }
        
        closedir($dir);
        return true;
    }
    
    /**
     * Cleanup temporary files
     */
    private function cleanup_temp_files() {
        $temp_files = glob($this->temp_dir . '*');
        foreach ($temp_files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->remove_directory($file);
            }
        }
        $this->log('Temporary files cleaned up');
    }
    
    /**
     * Remove directory recursively
     */
    private function remove_directory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            if (!$this->remove_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * Get installation status
     */
    public function get_status() {
        return array(
            'vendor_exists' => file_exists($this->vendor_dir),
            'autoload_exists' => file_exists($this->vendor_dir . 'autoload.php'),
            'phpspreadsheet_exists' => file_exists($this->vendor_dir . 'phpoffice/phpspreadsheet/'),
            'composer_json_exists' => file_exists($this->plugin_dir . 'composer.json'),
            'logs_exist' => file_exists($this->log_file)
        );
    }
    
    /**
     * Log message
     */
    private function log($message) {
        $timestamp = current_time('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message" . PHP_EOL;
        
        // Ensure log directory exists
        $log_dir = dirname($this->log_file);
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get installation logs
     */
    public function get_logs($lines = 50) {
        if (!file_exists($this->log_file)) {
            return array();
        }
        
        $logs = file($this->log_file, FILE_IGNORE_NEW_LINES);
        return array_slice($logs, -$lines);
    }
    
    /**
     * Clear installation logs
     */
    public function clear_logs() {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
    }
}
