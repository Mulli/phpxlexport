=== PhpSpreadsheet/phpxlexport for WordPress ===
Contributors: wordpressteam
Donate link: https://example.com/donate
Tags: excel, spreadsheet, export, phpoffice, reports, xlsx, csv, data-export
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.2.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Integrates PhpSpreadsheet library into WordPress for Excel file generation with seamless compatibility for existing WordPress code.

== Description ==

PhpSpreadsheet for WordPress is a comprehensive plugin that seamlessly integrates the powerful PhpSpreadsheet library into your WordPress installation. This plugin enables developers to generate professional Excel files directly from WordPress without complex setup procedures.

= Key Features =

- **One-Click Installation** - Automatically downloads and configures PhpSpreadsheet
- **Excel Generation** - Create professional Excel files with formatting, charts, and formulas
- **Zero Code Changes** - Works with existing WordPress code that uses PhpSpreadsheet
- **Admin Management** - Easy installation and status monitoring through WordPress admin
- **Secure** - Follows WordPress security best practices
- **Compatible** - Works with themes, plugins, and custom code
- **RTL Support** - Full support for right-to-left languages like Hebrew and Arabic

= Perfect For =

- Educational Institutions - Generate student reports and academic data exports
- Business Applications - Create financial reports, inventory exports, and data analysis
- E-commerce Sites - Export order data, product catalogs, and customer information
- Government Agencies - Generate compliance reports and administrative documents
- Non-profits - Create donor reports, volunteer schedules, and program statistics

= Developer-Friendly =

This plugin is designed to work seamlessly with existing code. If you already have WordPress code that uses PhpSpreadsheet, this plugin will make it work without any modifications.

`// Your existing code continues to work
if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    // Your Excel generation code here
}`

= Technical Features =

- Multiple Installation Methods - Supports Composer and precompiled installation
- Smart Autoloading - Automatically detects and loads the library
- Error Handling - Comprehensive logging and fallback mechanisms
- WordPress Integration - Uses WordPress HTTP API and Filesystem API
- Memory Efficient - Optimized for large dataset processing
- Hook System - Provides actions and filters for developers

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to **Plugins > Add New**
3. Search for "PhpSpreadsheet for WordPress"
4. Click **Install Now**
5. Activate the plugin
6. The plugin will automatically install PhpSpreadsheet library

= Manual Installation =

1. Download the plugin ZIP file
2. Upload to `/wp-content/plugins/phpspreadsheet-wp/`
3. Activate the plugin through the **Plugins** menu
4. Go to **Settings > PhpSpreadsheet** to manage the library

= Requirements =

- WordPress 5.0 or higher
- PHP 7.4 or higher
- At least 128MB PHP memory limit (256MB recommended)
- WordPress write permissions for plugin directory

== Frequently Asked Questions ==

= Does this plugin work with my existing code? =

Yes! This plugin is designed to work seamlessly with existing WordPress code that uses PhpSpreadsheet. No modifications to your existing code are required.

= How do I know if PhpSpreadsheet is installed correctly? =

Go to **Settings > PhpSpreadsheet** in your WordPress admin. The status will show as "✓ Loaded" if the library is properly installed.

= Can I use this with custom themes and plugins? =

Absolutely! This plugin provides PhpSpreadsheet functionality to any WordPress theme or plugin that needs Excel generation capabilities.

= What happens if the automatic installation fails? =

If automatic installation fails, you can:

1. Try the manual installation button in **Settings > PhpSpreadsheet**
2. Check the plugin logs for error details
3. Install PhpSpreadsheet manually via Composer
4. Contact support for assistance

= Is this plugin compatible with multisite? =

Yes, the plugin works with WordPress multisite installations. Each site in the network can have its own PhpSpreadsheet configuration.

= Does this plugin affect site performance? =

The plugin only loads PhpSpreadsheet when it's actually needed, so it has minimal impact on site performance. The library is not loaded on every page request.

= Can I generate files with Hebrew or Arabic text? =

Yes! The plugin fully supports RTL (right-to-left) languages and UTF-8 encoding for international text.

= What Excel features are supported? =

PhpSpreadsheet supports extensive Excel features including:

- Multiple worksheets
- Cell formatting and styling
- Formulas and calculations
- Charts and graphs
- Images and shapes
- Data validation
- Conditional formatting
- And much more

== Screenshots ==

1. Admin Settings Page - Easy management and status monitoring
2. Library Status - Real-time status of PhpSpreadsheet installation
3. Installation Process - One-click installation interface
4. Generated Excel File - Sample Excel output with formatting
5. Error Handling - Comprehensive error reporting and logging

== Changelog ==

= 1.2.0 (2025-05-28) =

- Added: Improved autoloader with better PHP compatibility
- Added: Enhanced error handling and logging
- Added: Support for multiple installation methods
- Fixed: Autoloader syntax errors on some PHP versions
- Fixed: Admin page void assignment issues
- Improved: WordPress Filesystem API integration
- Improved: Better Composer executable detection

= 1.1.0 (2025-05-15) =

- Added: WordPress admin interface
- Added: One-click installation functionality
- Added: Helper functions for developers
- Added: Hook system for extensibility
- Improved: Error handling and user feedback
- Improved: Security with nonce verification

= 1.0.0 (2025-05-01) =

- Initial Release
- Added: Basic PhpSpreadsheet integration
- Added: Automatic library detection
- Added: WordPress compatibility layer

== Upgrade Notice ==

= 1.2.0 =
This version includes important fixes for PHP compatibility and improved installation reliability. Recommended for all users.

= 1.1.0 =
Major update with admin interface and enhanced functionality. Backup your site before upgrading.

== Usage ==

= For Developers =

Once installed, PhpSpreadsheet is available throughout your WordPress installation:

`
// Check if PhpSpreadsheet is available
if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
// Create new spreadsheet
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

    // Add data
    $sheet->setCellValue('A1', 'Hello World');
    $sheet->setCellValue('B1', 'WordPress');

    // Save as Excel file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('/path/to/file.xlsx');

}
`

= Helper Functions =

The plugin provides convenient helper functions:

`
// Check if library is loaded
if (phpspreadsheet_wp_is_loaded()) {
// Safe to use PhpSpreadsheet
}

// Get library version
$version = phpspreadsheet_wp_get_version();

// Force load library
phpspreadsheet_wp_load_library();
`

= WordPress Hooks =

The plugin provides hooks for advanced customization:

`
// Hook fired when PhpSpreadsheet is loaded
add_action('phpspreadsheet_wp_loaded', 'my_custom_function');

function my_custom_function() {
// PhpSpreadsheet is now available
}
`

== Support ==

For support, please visit:

- [Plugin Documentation](https://example.com/docs)
- [WordPress Support Forums](https://wordpress.org/support/plugin/phpspreadsheet-wp/)
- [GitHub Repository](https://github.com/example/phpspreadsheet-wp)

== License ==

This plugin is licensed under the MIT License.
PhpSpreadsheet library is licensed under the MIT License.
WordPress is licensed under the GPL v2 or later.
