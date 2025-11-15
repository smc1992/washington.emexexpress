<?php
// Simple PHP test file to verify PHP-FPM is working
header('Content-Type: text/plain');

echo "PHP Test - Emex Express Chicago\n";
echo "===============================\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Time: " . date('Y-m-d H:i:s') . "\n";
echo "Environment Variables:\n";

// Test environment variables
$env_vars = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'ADMIN_EMAIL', 'CITY_NAME'];
foreach ($env_vars as $var) {
    echo "$var: " . ($_ENV[$var] ?? 'NOT SET') . "\n";
}

echo "\nPHP Extensions:\n";
$extensions = ['curl', 'mbstring', 'openssl', 'json'];
foreach ($extensions as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? 'LOADED' : 'NOT LOADED') . "\n";
}

echo "\nComposer Autoload:\n";
if (file_exists('vendor/autoload.php')) {
    echo "Composer: AVAILABLE\n";
    require 'vendor/autoload.php';
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "PHPMailer: LOADED\n";
    } else {
        echo "PHPMailer: NOT FOUND\n";
    }
} else {
    echo "Composer: NOT FOUND\n";
}

echo "\nTest completed successfully!\n";
?>
