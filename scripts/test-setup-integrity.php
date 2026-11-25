<?php

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\AppIntegrityService;

require __DIR__ . '/../src/EcclesiaCRM/dto/SystemURLs.php';
require __DIR__ . '/../src/EcclesiaCRM/Service/AppIntegrityService.php';

$fixture = sys_get_temp_dir() . '/crm-integrity-' . bin2hex(random_bytes(8));
$root = $fixture . '/data/crm';
mkdir($root . '/setup', 0777, true);
mkdir($root . '/Plugins');
mkdir($root . '/data');
mkdir($root . '/Include');
file_put_contents($root . '/known.php', 'original');
file_put_contents($root . '/data/generated.txt', 'generated');
$files = [['filename' => 'known.php', 'sha1' => sha1('original')]];
file_put_contents($root . '/signatures.json', json_encode([
    'files' => $files,
    'sha1' => sha1(json_encode($files, JSON_UNESCAPED_SLASHES)),
]));

$cache = new ReflectionProperty(AppIntegrityService::class, 'IntegrityCheckDetails');
$check = function (string $path) use ($cache): array {
    SystemURLs::init('', '', $path);
    $cache->setValue(null, null);
    return AppIntegrityService::verifyApplicationIntegrity();
};

try {
    foreach ([$root, $root . '/setup/..', $root . '/setup/../'] as $path) {
        $result = $check($path);
        if ($result['status'] !== 'success') {
            throw new RuntimeException('Unexpected integrity failure for ' . $path . ': ' . json_encode($result));
        }
    }

    file_put_contents($root . '/Include/Config.php', 'installed configuration');
    $result = $check($root . '/setup/..');
    if ($result['status'] !== 'success') {
        throw new RuntimeException('Unsigned configuration was reported: ' . json_encode($result));
    }

    $signedFiles = $files;
    $signedFiles[] = ['filename' => 'Include/Config.php', 'sha1' => sha1('different configuration')];
    file_put_contents($root . '/signatures.json', json_encode([
        'files' => $signedFiles,
        'sha1' => sha1(json_encode($signedFiles, JSON_UNESCAPED_SLASHES)),
    ]));
    $result = $check($root . '/setup/..');
    if ($result['status'] !== 'success') {
        throw new RuntimeException('Installed configuration was reported: ' . json_encode($result));
    }
    unlink($root . '/Include/Config.php');
    $result = $check($root . '/setup/..');
    if ($result['status'] !== 'success') {
        throw new RuntimeException('Missing configuration was reported: ' . json_encode($result));
    }

    file_put_contents($root . '/added.php', 'added');
    file_put_contents($root . '/Include/Config.php.backup', 'backup');
    file_put_contents($root . '/known.php', 'changed');
    $result = $check($root . '/setup/..');
    $failures = $result['files']['CRM'] ?? [];
    $statuses = array_column($failures, 'status', 'filename');
    if ($result['status'] !== 'failure' || $statuses != [
        'known.php' => 'Hash Mismatch',
        'added.php' => 'File Added',
        'Include/Config.php.backup' => 'File Added',
    ]) {
        throw new RuntimeException('Unexpected integrity failures: ' . json_encode($result));
    }

    unlink($root . '/known.php');
    $result = $check($root . '/setup/..');
    $statuses = array_column($result['files']['CRM'] ?? [], 'status', 'filename');
    if (($statuses['known.php'] ?? null) !== 'File Missing') {
        throw new RuntimeException('Missing signed file was not reported: ' . json_encode($result));
    }

    echo "Setup integrity tests passed\n";
} finally {
    foreach (['known.php', 'added.php', 'signatures.json', 'data/generated.txt', 'Include/Config.php', 'Include/Config.php.backup'] as $file) {
        if (file_exists($root . '/' . $file)) {
            unlink($root . '/' . $file);
        }
    }
    foreach ([$root . '/data', $root . '/Include', $root . '/Plugins', $root . '/setup', $root, $fixture . '/data', $fixture] as $directory) {
        rmdir($directory);
    }
}
