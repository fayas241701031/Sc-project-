<?php
header('Content-Type: text/plain');
echo "=== PHP Network Test ===\n";

echo "OpenSSL Enabled: " . (extension_loaded('openssl') ? 'YES' : 'NO') . "\n";

$host = 'smtp.gmail.com';
echo "Resolving $host...\n";
$ip = gethostbyname($host);

if ($ip === $host) {
    echo "ERROR: Could not resolve $host (gethostbyname failed).\n";
} else {
    echo "SUCCESS: Resolved $host to $ip\n";
}

echo "\n=== Port Test (587) ===\n";
$connection = @fsockopen($host, 587, $errno, $errstr, 5);
if (is_resource($connection)) {
    echo "SUCCESS: Connected to $host:587\n";
    fclose($connection);
} else {
    echo "ERROR: Could not connect to $host:587\n";
    echo "Err: $errno - $errstr\n";
}
?>
