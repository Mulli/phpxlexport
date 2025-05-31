<?php
/**
 * File: templates/admin-page.php
 * Admin page template
 */

if (!defined('ABSPATH')) {
    exit;
}

$library_status = PhpSpreadsheet_Loader::is_loaded() ? true : false;
$library_version = PhpSpreadsheet_Loader::get_version();
$settings = get_option('phpspreadsheet_wp_settings', array());
?>

<div class="wrap phpspreadsheet-wp-admin">
    <h1><?php _e('PhpSpreadsheet for WordPress', 'phpspreadsheet-wp'); ?></h1>
    
    <!-- Status Section -->
    <div class="phpspreadsheet-wp-status <?php echo $library_status ? 'loaded' : 'not-loaded'; ?>">
        <h2><?php _e('Library Status', 'phpspreadsheet-wp'); ?></h2>
        <p>
            <strong><?php _e('Status:', 'phpspreadsheet-wp'); ?></strong>
            <?php if ($library_status): ?>
                <span style="color: green;">✓ <?php _e('Loaded', 'phpspreadsheet-wp'); ?></span>
                (<?php printf(__('Version: %s', 'phpspreadsheet-wp'), esc_html($library_version)); ?>)
            <?php else: ?>
                <span style="color: red;">✗ <?php _e('Not Loaded', 'phpspreadsheet-wp'); ?></span>
            <?php endif; ?>
        </p>
        
        <?php if (!$library_status): ?>
            <p>
                <button type="button" class="phpspreadsheet-wp-install-btn" id="install-library">
                    <?php _e('Install PhpSpreadsheet', 'phpspreadsheet-wp'); ?>
                </button>
                <span id="phpspreadsheet-status" style="margin-left: 10px;"></span>
            </p>
        <?php else: ?>
            <p>
                <button type="button" class="button" class="phpspreadsheet-wp-check-status">
                    <?php _e('Check Status', 'phpspreadsheet-wp'); ?>
                </button>
            </p>
        <?php endif; ?>
    </div>
    
    <!-- Settings Section -->
    <form method="post" action="options.php">
        <?php
        settings_fields('phpspreadsheet_wp_settings');
        do_settings_sections('phpspreadsheet-wp');
        ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Auto-load Library', 'phpspreadsheet-wp'); ?></th>
                <td>
                    <input type="checkbox" 
                           name="phpspreadsheet_wp_settings[auto_load]" 
                           value="1" 
                           <?php checked(isset($settings['auto_load']) && $settings['auto_load']); ?> />
                    <label><?php _e('Load PhpSpreadsheet automatically', 'phpspreadsheet-wp'); ?></label>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <!-- Usage Information -->
    <div class="phpspreadsheet-wp-info-box">
        <h2><?php _e('Usage Information', 'phpspreadsheet-wp'); ?></h2>
        <p><strong><?php _e('For Developers:', 'phpspreadsheet-wp'); ?></strong></p>
        <p><?php _e('Once installed, PhpSpreadsheet is automatically available in your WordPress code:', 'phpspreadsheet-wp'); ?></p>
        
        <div class="phpspreadsheet-wp-code-example">
// <?php _e('Check if available', 'phpspreadsheet-wp'); ?>

if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    // <?php _e('Your code here', 'phpspreadsheet-wp'); ?>

}

// <?php _e('Or use the plugin helper', 'phpspreadsheet-wp'); ?>

if (function_exists('phpspreadsheet_wp_is_loaded')) {
    if (phpspreadsheet_wp_is_loaded()) {
        // <?php _e('Library is ready to use', 'phpspreadsheet-wp'); ?>

    }
}
        </div>
        
        <button type="button" class="button phpspreadsheet-wp-copy-code">
            <?php _e('Copy Code', 'phpspreadsheet-wp'); ?>
        </button>
    </div>
    
    <!-- Installation Paths -->
    <div class="phpspreadsheet-wp-info-box">
        <h3><?php _e('Installation Paths', 'phpspreadsheet-wp'); ?></h3>
        <ul>
            <?php foreach (PhpSpreadsheet_Loader::get_autoload_paths() as $path): ?>
                <li>
                    <code><?php echo esc_html($path); ?></code>
                    <?php if (file_exists($path)): ?>
                        <span style="color: green;">✓</span>
                    <?php else: ?>
                        <span style="color: red;">✗</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#install-library').on('click', function() {
        var $button = $(this);
        var $status = $('#phpspreadsheet-status');
        
        $button.prop('disabled', true).text('<?php _e('Installing...', 'phpspreadsheet-wp'); ?>');
        $status.html('<span style="color: orange;"><?php _e('Installing PhpSpreadsheet...', 'phpspreadsheet-wp'); ?></span>');
        
        $.post(ajaxurl, {
            action: 'phpspreadsheet_install',
            nonce: '<?php echo wp_create_nonce('phpspreadsheet_wp_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                $status.html('<span style="color: green;">✓ <?php _e('Installation successful! Please reload the page.', 'phpspreadsheet-wp'); ?></span>');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                $status.html('<span style="color: red;">✗ <?php _e('Installation failed', 'phpspreadsheet-wp'); ?>: ' + response.data + '</span>');
                $button.prop('disabled', false).text('<?php _e('Retry Installation', 'phpspreadsheet-wp'); ?>');
            }
        }).fail(function() {
            $status.html('<span style="color: red;">✗ <?php _e('Installation failed due to network error', 'phpspreadsheet-wp'); ?></span>');
            $button.prop('disabled', false).text('<?php _e('Retry Installation', 'phpspreadsheet-wp'); ?>');
        });
    });
});
</script>