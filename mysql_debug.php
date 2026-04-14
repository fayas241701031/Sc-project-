<?php
header('Content-Type: text/plain; charset=utf-8');
$output = [];
$output[] = "=== MySQL PID File ===";
$pidFile = 'C:/xampp/mysql/data/mysql.pid';
if (file_exists($pidFile)) {
    $output[] = "mysql.pid exists";
    $content = trim(file_get_contents($pidFile));
    $output[] = "pid=" . $content;
} else {
    $output[] = "mysql.pid missing";
}

$output[] = "=== Process List ===";
$task = shell_exec('tasklist /FI "IMAGENAME eq mysqld.exe" /FO LIST');
$output[] = $task ?: 'shell_exec not available or no mysqld.exe process.';

$output[] = "=== Netstat 3306 ===";
$net = shell_exec('netstat -ano | findstr ":3306"');
$output[] = $net ?: 'shell_exec not available or port 3306 not in use.';

$output[] = "=== MySQL Log Tail ===";
$log = @file('C:/xampp/mysql/data/mysql_error.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($log !== false) {
    $tail = array_slice($log, -15);
    foreach ($tail as $line) {
        $output[] = $line;
    }
} else {
    $output[] = 'Unable to read mysql_error.log';
}

file_put_contents(__DIR__ . '/mysql_debug_output.txt', implode("\n", $output));
echo implode("\n", $output);
?>