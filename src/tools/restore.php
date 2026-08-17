<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/Include/Config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use EcclesiaCRM\Backup\RestoreBackup;
use EcclesiaCRM\Utils\LoggerUtils;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(64);
}

$input = [];
for ($index = 1; $index < $argc; $index++) {
    $argument = $argv[$index];
    $separator = strpos($argument, '=');

    if ($separator === false) {
        if (!isset($input['file'])) {
            $input['file'] = $argument;
        }
        continue;
    }

    $key = substr($argument, 0, $separator);
    $input[$key] = substr($argument, $separator + 1);
}

$sourcePath = $input['file'] ?? $input['filename'] ?? $input['path'] ?? null;
if ($sourcePath === null || $sourcePath === '') {
    fwrite(STDERR, "Usage: php tools/restore.php file=/path/to/backup [password=...] [cleanup=true]\n");
    exit(64);
}

$sourcePath = realpath($sourcePath);
if ($sourcePath === false || !is_file($sourcePath) || !is_readable($sourcePath)) {
    fwrite(STDERR, "Restore file does not exist or is not readable: " . ($input['file'] ?? $sourcePath) . "\n");
    exit(66);
}

$fileName = basename($input['name'] ?? $sourcePath);
if ($fileName === '' || $fileName === '.' || $fileName === DIRECTORY_SEPARATOR) {
    fwrite(STDERR, "Restore file name is invalid.\n");
    exit(64);
}

$documentRoot = dirname(__DIR__);
$maintenanceFile = $documentRoot . '/tmp_attach/maintenance_mode';
$progressFile = $documentRoot . '/tmp_attach/restore_in_progress.txt';
$resultFile = $documentRoot . '/tmp_attach/restore_result.json';
$restorePassword = $input['restorePassword'] ?? $input['password'] ?? '';
$cleanupSource = filter_var($input['cleanup'] ?? false, FILTER_VALIDATE_BOOLEAN);

$_POST['restorePassword'] = $restorePassword;
$_SERVER['REQUEST_METHOD'] = 'CLI';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '';

if (file_exists($resultFile)) {
    unlink($resultFile);
}

file_put_contents($maintenanceFile, date('c'));
file_put_contents($progressFile, date('c'));
$exitCode = 0;

try {
    LoggerUtils::getAppLogger()->info('Entering maintenance mode for restore');
    LoggerUtils::getAppLogger()->info('Start restore from command line: ' . $fileName);

    $restoreFile = [
        'name' => $fileName,
        'tmp_name' => $sourcePath,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($sourcePath),
    ];

    $restoreJob = new RestoreBackup($restoreFile);
    $restore = $restoreJob->run();
    $result = [
        'success' => true,
        'Messages' => $restore->getMessages(),
    ];

    file_put_contents($resultFile, json_encode($result));
    LoggerUtils::getAppLogger()->info('Restore from command line completed');
    fwrite(STDOUT, "Restore complete.\n");
} catch (Throwable $exception) {
    $result = [
        'success' => false,
        'message' => $exception->getMessage(),
    ];

    file_put_contents($resultFile, json_encode($result));
    LoggerUtils::getAppLogger()->error('Restore from command line failed: ' . $exception->getMessage());
    fwrite(STDERR, "Restore failed: " . $exception->getMessage() . "\n");
    $exitCode = 1;
} finally {
    if (file_exists($progressFile)) {
        unlink($progressFile);
    }

    if (file_exists($maintenanceFile)) {
        unlink($maintenanceFile);
    }

    LoggerUtils::getAppLogger()->info('Leaving maintenance mode after restore');

    if ($cleanupSource && file_exists($sourcePath)) {
        unlink($sourcePath);
    }
}

if ($exitCode !== 0) {
    exit($exitCode);
}