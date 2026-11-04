<?php

namespace EcclesiaCRM\Service;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Plugin;
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\SQLUtils;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\Propel;

class PluginUpgradeService
{
    public function getUpdates(): array
    {
        $updates = [];
        $issues = [];

        foreach (PluginQuery::create()->find() as $plugin) {
            try {
                $config = $this->getConfig($plugin);
                $comparison = $this->compareVersions($plugin->getVersion(), $config['version']);
                if ($comparison < 0) {
                    $this->getMigrationPlan($plugin, $config['version']);
                    $updates[] = $this->describePlugin($plugin, $config['version']);
                } elseif ($comparison > 0) {
                    $issues[] = $this->describePlugin($plugin, $config['version']) + ['reason' => 'database_newer'];
                }
            } catch (\RuntimeException | \JsonException $exception) {
                LoggerUtils::getAppLogger()->error('Plugin update check failed for ' . $plugin->getName() . ': ' . $exception->getMessage());
                $issues[] = [
                    'id' => $plugin->getId(),
                    'name' => $plugin->getName(),
                    'installedVersion' => $plugin->getVersion(),
                    'reason' => 'invalid_configuration',
                ];
            }
        }

        return ['updates' => $updates, 'issues' => $issues];
    }

    public function upgrade(int $id, string $expectedInstalledVersion, string $expectedVersion): array
    {
        $connection = Propel::getConnection();
        $lockName = 'ecclesiacrm-plugin-upgrade-' . $id;
        $lock = $connection->prepare('SELECT GET_LOCK(?, 0)');
        $lock->execute([$lockName]);
        if ((int) $lock->fetchColumn() !== 1) {
            throw new \RuntimeException('Plugin upgrade is already running.');
        }

        try {
            $plugin = PluginQuery::create()->findOneById($id);
            if ($plugin === null) {
                throw new \RuntimeException('Plugin not found.');
            }

            $config = $this->getConfig($plugin);
            if ($plugin->getVersion() !== $expectedInstalledVersion || $config['version'] !== $expectedVersion
                || $this->compareVersions($plugin->getVersion(), $config['version']) >= 0) {
                throw new \RuntimeException('Plugin versions changed since the update check.');
            }

            $plan = $this->getMigrationPlan($plugin, $config['version']);
            $logger = LoggerUtils::getAppLogger();
            foreach ($plan as $script) {
                $logger->info('Plugin upgrade ' . $plugin->getName() . ': ' . $script);
                if (pathinfo($script, PATHINFO_EXTENSION) === 'sql') {
                    SQLUtils::sqlImport($script, $connection);
                } else {
                    require $script;
                }
            }

            $plugin->setVersion($config['version']);
            $plugin->save($connection);

            return $this->describePlugin($plugin, $config['version']);
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                // SQLUtils starts implicit transactions without Propel's transaction nesting.
                $connection->exec('ROLLBACK');
            }
            $connection->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
            throw $exception;
        } finally {
            $release = $connection->prepare('SELECT RELEASE_LOCK(?)');
            $release->execute([$lockName]);
        }
    }

    private function describePlugin(Plugin $plugin, string $version): array
    {
        return [
            'id' => $plugin->getId(),
            'name' => $plugin->getName(),
            'installedVersion' => $plugin->getVersion(),
            'availableVersion' => $version,
        ];
    }

    private function getConfig(Plugin $plugin): array
    {
        $name = $plugin->getName();
        if (!is_string($name) || !preg_match('/\A[A-Za-z0-9_-]+\z/', $name)) {
            throw new \RuntimeException('Invalid plugin name.');
        }

        $directory = realpath(SystemURLs::getDocumentRoot() . '/Plugins/' . $name);
        if ($directory === false || !is_dir($directory)) {
            throw new \RuntimeException('Plugin directory not found.');
        }

        $config = $this->readJson($directory . '/config.json');
        if (!isset($config['Name']) || !is_string($config['Name']) || trim($config['Name']) === ''
            || !$this->isValidVersion($config['version'] ?? null)
            || !$this->isValidVersion($plugin->getVersion())) {
            throw new \RuntimeException('Invalid plugin name or version.');
        }

        return $config;
    }

    private function getMigrationPlan(Plugin $plugin, string $targetVersion): array
    {
        $pluginDirectory = realpath(SystemURLs::getDocumentRoot() . '/Plugins/' . $plugin->getName());
        $directory = realpath(SystemURLs::getDocumentRoot() . '/Plugins/' . $plugin->getName() . '/mysql');
        if ($pluginDirectory === false || $directory === false
            || strncmp($directory, $pluginDirectory . DIRECTORY_SEPARATOR, strlen($pluginDirectory) + 1) !== 0) {
            throw new \RuntimeException('Plugin migration directory not found.');
        }
        $updates = $this->readJson($directory . '/upgrade.json');
        $steps = [];
        foreach ($updates as $update) {
            if (!is_array($update) || !$this->isValidVersion($update['dbVersion'] ?? null)) {
                throw new \RuntimeException('Invalid migration version.');
            }
            if ($this->compareVersions($update['dbVersion'], $plugin->getVersion()) <= 0
                || $this->compareVersions($update['dbVersion'], $targetVersion) > 0) {
                continue;
            }
            $steps[] = $update;
        }

        usort($steps, function ($a, $b) {
            return $this->compareVersions($a['dbVersion'], $b['dbVersion']);
        });
        if (!$steps || $this->compareVersions(end($steps)['dbVersion'], $targetVersion) !== 0) {
            throw new \RuntimeException('No migration reaches the available plugin version.');
        }

        $scripts = [];
        $currentVersion = $plugin->getVersion();
        foreach ($steps as $step) {
            if ($this->compareVersions($step['dbVersion'], $currentVersion) <= 0) {
                throw new \RuntimeException('Duplicate migration version.');
            }
            if (isset($step['versions'])) {
                if (!is_array($step['versions'])) {
                    throw new \RuntimeException('Invalid supported migration versions.');
                }
                if ($step['versions']) {
                    $isSupportedVersion = false;
                    foreach ($step['versions'] as $supportedVersion) {
                        if (!is_string($supportedVersion) || !$this->isValidVersion($supportedVersion)) {
                            throw new \RuntimeException('Invalid supported migration version.');
                        }
                        if ($this->compareVersions($currentVersion, $supportedVersion) === 0) {
                            $isSupportedVersion = true;
                            break;
                        }
                    }
                    if (!$isSupportedVersion) {
                        throw new \RuntimeException('Migration does not support the installed version.');
                    }
                }
            }
            foreach (['prescripts', 'scripts', 'postscripts'] as $phase) {
                $entries = $step[$phase] ?? ($phase === 'postscripts' ? ($step['postcripts'] ?? null) : null);
                if (!is_array($entries)) {
                    throw new \RuntimeException('Invalid migration scripts.');
                }
                foreach ($entries as $entry) {
                    if (!is_string($entry) || $entry === '') {
                        throw new \RuntimeException('Invalid migration script path.');
                    }
                    $path = realpath($directory . '/' . $entry);
                    if ($path === false || !is_file($path) || !is_readable($path)
                        || strncmp($path, $directory . DIRECTORY_SEPARATOR, strlen($directory) + 1) !== 0
                        || !in_array(pathinfo($path, PATHINFO_EXTENSION), ['sql', 'php'], true)) {
                        throw new \RuntimeException('Migration script is missing or invalid: ' . $entry);
                    }
                    $scripts[] = $path;
                }
            }
            $currentVersion = $step['dbVersion'];
        }

        return $scripts;
    }

    private function readJson(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \RuntimeException('File is missing or unreadable: ' . $path);
        }
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read file: ' . $path);
        }
        $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new \RuntimeException('Invalid JSON in: ' . $path);
        }
        return $data;
    }

    private function isValidVersion($version): bool
    {
        return is_string($version)
            && preg_match('/\A[0-9]+(?:[._-][0-9A-Za-z]+)*(?:\+[0-9A-Za-z.-]+)?\z/', $version) === 1;
    }

    private function compareVersions(string $left, string $right): int
    {
        return version_compare($this->normalizeVersion($left), $this->normalizeVersion($right));
    }

    private function normalizeVersion(string $version): string
    {
        if (preg_match('/\A\d+(?:\.\d+)*\z/', $version) !== 1) {
            return $version;
        }

        $parts = explode('.', $version);
        while (count($parts) < 3) {
            $parts[] = '0';
        }

        return implode('.', $parts);
    }
}
