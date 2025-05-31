<?php
// Simple autoloader for PhpSpreadsheet
spl_autoload_register(function ($class) {
    if (strpos($class, 'PhpOffice\PhpSpreadsheet') === 0) {
        $php_path = "PhpOffice\PhpSpreadsheet\\";
        $file = __DIR__ . $php_path . str_replace('\\', '/', substr($class, strlen($php_path))) . ".php";
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
