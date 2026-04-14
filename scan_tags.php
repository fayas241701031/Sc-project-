<?php
$dir = __DIR__;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$phpFiles = new RegexIterator($files, '/\.php$/');

echo "Scanning for missing <?php tags...\n";
foreach ($phpFiles as $file) {
    if (strpos($file->getPathname(), 'vendor') !== false) continue;
    if (strpos($file->getPathname(), 'PHPMailer') !== false) continue;

    $content = file_get_contents($file->getPathname());
    if (strpos($content, '<?php') !== 0 && strpos($content, '<?') !== 0) {
        echo "MISSING TAG: " . $file->getPathname() . "\n";
    }
}
echo "Scan complete.\n";
?>
