<?php
// garbage_collect.php
// Cleans up files in temp directory older than 24 hours.
// Designed to be run via CLI only.

// 1. Security Check: Allow CLI only
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access Denied: This script can only be run from the command line.");
}

// Configuration
$tempDir = __DIR__ . '/../upload/temp/'; // Absolute path recommended for CLI
$logDir = __DIR__ . '/../logs/';
$logFile = $logDir . 'gc_history.log';
$maxAgeSeconds = 24 * 3600; // 24 hours

// Ensure log directory exists
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

function cleanDirectory($dir, $maxAgeSeconds)
{
    if (!is_dir($dir)) {
        return 0;
    }

    // Iterate through files and directories
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    $now = time();
    $deletedFiles = 0;
    $deletedSize = 0;

    foreach ($iterator as $file) {
        $path = $file->getRealPath();

        if ($file->isFile()) {
            // Check file modification time
            if (($now - $file->getMTime()) > $maxAgeSeconds) {
                $size = $file->getSize();
                if (@unlink($path)) {
                    $deletedFiles++;
                    $deletedSize += $size;
                }
            }
        } else if ($file->isDir()) {
            // Try to remove empty directories
            // @rmdir suppresses errors if dir is not empty
            @rmdir($path);
        }
    }

    return ['count' => $deletedFiles, 'size' => $deletedSize];
}

// Run Cleanup
$result = cleanDirectory($tempDir, $maxAgeSeconds);

// Logging
$timestamp = date('Y-m-d H:i:s');
$sizeFormatted = number_format($result['size'] / 1024, 2) . ' KB';
$message = "[$timestamp] Deleted files: {$result['count']} (Size: $sizeFormatted)" . PHP_EOL;

// Append to log file
file_put_contents($logFile, $message, FILE_APPEND);

// Output to console (for verifying execution)
echo $message;
?>