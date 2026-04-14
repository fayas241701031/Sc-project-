<?php
header('Content-Type: text/plain; charset=utf-8');
$host = '127.0.0.1';
$port = 3306;
$timeout = 3;
$output = [];
$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
if ($fp) {
    $output[] = "MySQL port $port is reachable on $host.";
    fclose($fp);
} else {
    $output[] = "MySQL port $port is not reachable on $host.";
    $output[] = "Error: $errno - $errstr";
}

$pidFile = 'C:/xampp/mysql/data/mysql.pid';
if (file_exists($pidFile)) {
    $output[] = "mysql.pid exists: $pidFile";
    $output[] = "Contents: " . trim(file_get_contents($pidFile));
} else {
    $output[] = "mysql.pid does not exist.";
}

$dataDir = 'C:/xampp/mysql/data';
if (is_dir($dataDir)) {
    $output[] = "Data directory exists: $dataDir";
    $files = ['ibdata1', 'ib_logfile0', 'ib_logfile1', 'mysql_error.log'];
    foreach ($files as $file) {
        $path = $dataDir . '/' . $file;
        $output[] = basename($path) . ': ' . (file_exists($path) ? 'exists' : 'missing');
    }
}

file_put_contents(__DIR__ . '/mysql_check_output.txt', implode("\n", $output));
echo implode("\n", $output);
?>