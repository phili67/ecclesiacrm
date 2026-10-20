#!/usr/bin/env php
<?php
/**
 * EcclesiaCRM Application & Plugins Integrity Check
 * CLI Tool for automated integrity verification
 * 
 * Usage:
 *   php tools/integrity-check.php [format]
 * 
 * Formats: json, csv, text (default: json)
 * 
 * Examples:
 *   php tools/integrity-check.php json
 *   php tools/integrity-check.php csv > report.csv
 *   php tools/integrity-check.php text
 */

// Racine du CRM déduite de l'emplacement du script (src/tools/..), indépendante du chemin d'installation
define('APP_ROOT', dirname(__DIR__));
define('PLUGINS_DIR', APP_ROOT . '/Plugins');

class IntegrityChecker {
    private $results = [];
    private $stats = [
        'total_files' => 0,
        'valid_files' => 0,
        'corrupted_files' => 0,
        'missing_files' => 0,
        'total_plugins' => 0,
        'valid_plugins' => 0,
        'corrupted_plugins' => 0
    ];
    private $startTime;

    public function __construct() {
        $this->startTime = microtime(true);
    }

    public function checkAll() {
        $this->checkApplication();
        $this->checkPlugins();
        return $this->results;
    }

    private function checkApplication() {
        $sigFile = APP_ROOT . '/signatures.json';
        $result = [
            'type' => 'application',
            'name' => 'EcclesiaCRM',
            'path' => APP_ROOT,
            'status' => 'unknown',
            'message' => '',
            'files' => ['total' => 0, 'valid' => 0, 'corrupted' => [], 'missing' => []]
        ];

        if (!file_exists($sigFile)) {
            $result['status'] = 'warning';
            $result['message'] = 'signatures.json not found';
            $this->results[] = $result;
            return;
        }

        $data = @json_decode(@file_get_contents($sigFile), true);
        if (!$data || !isset($data['sha1']) || !isset($data['files'])) {
            $result['status'] = 'error';
            $result['message'] = 'Invalid signatures.json';
            $this->results[] = $result;
            return;
        }

        $calcSha1 = sha1(json_encode($data['files'], JSON_UNESCAPED_SLASHES));
        if ($calcSha1 !== $data['sha1']) {
            $result['status'] = 'error';
            $result['message'] = 'Signature verification failed';
            $this->results[] = $result;
            return;
        }

        foreach ($data['files'] as $file) {
            $result['files']['total']++;
            $this->stats['total_files']++;
            
            $filename = $file['filename'] ?? '';
            $filePath = APP_ROOT . '/' . $filename;
            
            if (!file_exists($filePath)) {
                $result['files']['missing'][] = $filename;
                $this->stats['missing_files']++;
            } else {
                $hash = @sha1_file($filePath);
                if ($hash !== ($file['sha1'] ?? '')) {
                    $result['files']['corrupted'][] = ['file' => $filename, 'expected' => $file['sha1'] ?? ''];
                    $this->stats['corrupted_files']++;
                } else {
                    $result['files']['valid']++;
                    $this->stats['valid_files']++;
                }
            }
        }

        if (empty($result['files']['corrupted']) && empty($result['files']['missing'])) {
            $result['status'] = 'ok';
            $result['message'] = 'All files verified';
        } else {
            $result['status'] = 'error';
            $result['message'] = count($result['files']['corrupted']) . ' corrupted, ' . count($result['files']['missing']) . ' missing';
        }

        $this->results[] = $result;
    }

    private function checkPlugins() {
        if (!is_dir(PLUGINS_DIR)) return;

        $plugins = @scandir(PLUGINS_DIR);
        if (!$plugins) return;

        foreach ($plugins as $plugin) {
            if ($plugin === '.' || $plugin === '..' || $plugin[0] === '.') continue;
            $pluginPath = PLUGINS_DIR . '/' . $plugin;
            if (!is_dir($pluginPath)) continue;
            $this->checkPlugin($plugin, $pluginPath);
        }
    }

    private function checkPlugin($name, $path) {
        $this->stats['total_plugins']++;
        
        $sigFile = $path . '/signatures.json';
        $result = [
            'type' => 'plugin',
            'name' => $name,
            'status' => 'unknown',
            'message' => '',
            'files' => ['total' => 0, 'valid' => 0, 'corrupted' => [], 'missing' => []]
        ];

        if (!file_exists($sigFile)) {
            $result['status'] = 'warning';
            $result['message'] = 'No signatures.json';
            $this->results[] = $result;
            return;
        }

        $data = @json_decode(@file_get_contents($sigFile), true);
        if (!$data || !isset($data['sha1']) || !isset($data['files'])) {
            $result['status'] = 'error';
            $result['message'] = 'Invalid signatures.json';
            $this->stats['corrupted_plugins']++;
            $this->results[] = $result;
            return;
        }

        $calcSha1 = sha1(json_encode($data['files'], JSON_UNESCAPED_SLASHES));
        if ($calcSha1 !== $data['sha1']) {
            $result['status'] = 'error';
            $result['message'] = 'Signature verification failed';
            $this->stats['corrupted_plugins']++;
            $this->results[] = $result;
            return;
        }

        foreach ($data['files'] as $file) {
            $result['files']['total']++;
            $this->stats['total_files']++;
            
            $filename = $file['filename'] ?? '';
            $cleanPath = preg_replace('#^Plugins/[^/]+/#', '', $filename);
            $filePath = $path . '/' . $cleanPath;

            if (!file_exists($filePath)) {
                $result['files']['missing'][] = $cleanPath;
                $this->stats['missing_files']++;
            } else {
                $hash = @sha1_file($filePath);
                if ($hash !== ($file['sha1'] ?? '')) {
                    $result['files']['corrupted'][] = ['file' => $cleanPath];
                    $this->stats['corrupted_files']++;
                } else {
                    $result['files']['valid']++;
                    $this->stats['valid_files']++;
                }
            }
        }

        if (empty($result['files']['corrupted']) && empty($result['files']['missing'])) {
            $result['status'] = 'ok';
            $result['message'] = 'All files verified';
            $this->stats['valid_plugins']++;
        } else {
            $result['status'] = 'error';
            $result['message'] = count($result['files']['corrupted']) . ' corrupted, ' . count($result['files']['missing']) . ' missing';
            $this->stats['corrupted_plugins']++;
        }

        $this->results[] = $result;
    }

    public function getStats() {
        return array_merge($this->stats, [
            'duration_ms' => round((microtime(true) - $this->startTime) * 1000, 2),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function toJSON() {
        $hasErrors = $this->stats['corrupted_files'] > 0 || $this->stats['corrupted_plugins'] > 0;
        return json_encode([
            'status' => $hasErrors ? 'error' : 'ok',
            'timestamp' => date('c'),
            'stats' => $this->getStats(),
            'results' => $this->results
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function toCSV() {
        $lines = ['timestamp,type,name,status,message,total_files,valid_files,corrupted_count,missing_count'];
        foreach ($this->results as $result) {
            $lines[] = implode(',', [
                date('Y-m-d H:i:s'),
                $result['type'],
                $result['name'],
                $result['status'],
                '"' . str_replace('"', '""', $result['message']) . '"',
                $result['files']['total'],
                $result['files']['valid'],
                count($result['files']['corrupted']),
                count($result['files']['missing'])
            ]);
        }
        return implode("\n", $lines) . "\n";
    }

    public function toText() {
        $lines = [];
        $lines[] = str_repeat("=", 80);
        $lines[] = "INTEGRITY CHECK REPORT - " . date('Y-m-d H:i:s');
        $lines[] = str_repeat("=", 80);
        $lines[] = '';

        $s = $this->stats;
        $lines[] = "SUMMARY:";
        $lines[] = "  Status: " . ($s['corrupted_files'] === 0 && $s['corrupted_plugins'] === 0 ? 'OK' : 'FAILED');
        $lines[] = sprintf("  Total Files: %d | Valid: %d | Corrupted: %d | Missing: %d",
            $s['total_files'], $s['valid_files'], $s['corrupted_files'], $s['missing_files']);
        $lines[] = sprintf("  Plugins: %d total | %d valid | %d corrupted",
            $s['total_plugins'], $s['valid_plugins'], $s['corrupted_plugins']);
        $lines[] = sprintf("  Duration: %.0f ms", $s['duration_ms'] ?? 0);
        $lines[] = '';

        foreach ($this->results as $result) {
            $icon = $result['status'] === 'ok' ? '✓' : ($result['status'] === 'warning' ? '⚠' : '✗');
            $lines[] = sprintf("%s [%s] %s/%s", $icon, strtoupper($result['status']), $result['type'], $result['name']);
            $lines[] = "  " . $result['message'];
            
            if ($result['files']['total'] > 0) {
                $lines[] = sprintf("  Files: %d/%d valid", $result['files']['valid'], $result['files']['total']);
            }

            if (!empty($result['files']['corrupted'])) {
                $lines[] = "  Corrupted:";
                foreach ($result['files']['corrupted'] as $c) {
                    $lines[] = "    - " . $c['file'];
                }
            }

            if (!empty($result['files']['missing'])) {
                $lines[] = "  Missing:";
                foreach ($result['files']['missing'] as $m) {
                    $lines[] = "    - " . $m;
                }
            }
            $lines[] = '';
        }

        $lines[] = str_repeat("=", 80);
        return implode("\n", $lines);
    }
}

// Main execution
$format = 'json';
if (isset($argv[1])) {
    if (in_array($argv[1], ['json', 'csv', 'text'])) {
        $format = $argv[1];
    }
}

$checker = new IntegrityChecker();
$checker->checkAll();
$stats = $checker->getStats();

$output = '';
switch ($format) {
    case 'csv':
        $output = $checker->toCSV();
        break;
    case 'text':
        $output = $checker->toText();
        break;
    default:
        $output = $checker->toJSON();
}

echo $output;

$exitCode = ($stats['corrupted_files'] > 0 || $stats['corrupted_plugins'] > 0) ? 1 : 0;
exit($exitCode);
