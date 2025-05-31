/**
 * File: assets/js/admin.js
 * Admin JavaScript for the plugin
 */

jQuery(document).ready(function($) {
    
    // Handle installation button click
    $('.phpspreadsheet-wp-install-btn').on('click', function() {
        var $button = $(this);
        var $status = $('#phpspreadsheet-status');
        
        // Disable button and show loading state
        $button.prop('disabled', true)
               .text(phpspreadsheet_wp_ajax.strings.installing);
        
        // Show loading message
        $status.html('<span style="color: orange;">Installing PhpSpreadsheet...</span>');
        
        // Make AJAX request
        $.ajax({
            url: phpspreadsheet_wp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'phpspreadsheet_install',
                nonce: phpspreadsheet_wp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status.html('<span style="color: green;">✓ ' + 
                               phpspreadsheet_wp_ajax.strings.success + ' ' +
                               phpspreadsheet_wp_ajax.strings.reload + '</span>');
                    
                    // Reload page after 2 seconds
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $status.html('<span style="color: red;">✗ ' + 
                               phpspreadsheet_wp_ajax.strings.error + ': ' + 
                               response.data + '</span>');
                    
                    // Re-enable button
                    $button.prop('disabled', false)
                           .text('Retry Installation');
                }
            },
            error: function(xhr, status, error) {
                $status.html('<span style="color: red;">✗ ' + 
                           phpspreadsheet_wp_ajax.strings.error + ': ' + 
                           error + '</span>');
                
                // Re-enable button
                $button.prop('disabled', false)
                       .text('Retry Installation');
            }
        });
    });
    
    // Handle status check
    $('.phpspreadsheet-wp-check-status').on('click', function() {
        var $button = $(this);
        var $status = $('#phpspreadsheet-status');
        
        $button.prop('disabled', true).text('Checking...');
        
        $.ajax({
            url: phpspreadsheet_wp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'phpspreadsheet_check_status',
                nonce: phpspreadsheet_wp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    if (data.loaded) {
                        $status.html('<span style="color: green;">✓ Loaded (Version: ' + 
                                   data.version + ')</span>');
                    } else {
                        $status.html('<span style="color: red;">✗ Not Loaded</span>');
                    }
                }
                
                $button.prop('disabled', false).text('Check Status');
            },
            error: function() {
                $status.html('<span style="color: red;">✗ Status check failed</span>');
                $button.prop('disabled', false).text('Check Status');
            }
        });
    });
    
    // Copy to clipboard functionality
    $('.phpspreadsheet-wp-copy-code').on('click', function() {
        var $code = $(this).siblings('.phpspreadsheet-wp-code-example');
        var text = $code.text();
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Code copied to clipboard!');
            });
        } else {
            // Fallback for older browsers
            var textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('Code copied to clipboard!');
        }
    });
});
