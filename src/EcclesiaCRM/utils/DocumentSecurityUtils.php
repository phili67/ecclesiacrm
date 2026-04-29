<?php

namespace EcclesiaCRM\Utils;

class DocumentSecurityUtils
{
    private const FORBIDDEN_UPLOAD_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'js', 'mjs', 'cjs',
        'jsp', 'asp', 'aspx', 'cgi', 'pl', 'py', 'sh', 'bash', 'exe', 'com', 'jar', 'msi',
        'htaccess', 'html', 'htm', 'xhtml', 'svg'
    ];

    private const FORBIDDEN_UPLOAD_MIME_TYPES = [
        'application/x-httpd-php', 'application/x-php', 'text/x-php', 'text/php',
        'application/php', 'application/javascript', 'text/javascript', 'application/x-javascript',
        'text/x-shellscript', 'application/x-sh', 'text/html', 'application/xhtml+xml',
        'image/svg+xml'
    ];

    public static function normalizeDirectoryPath(string $path): ?string
    {
        $normalizedRelativePath = self::normalizeRelativePath($path);
        if (is_null($normalizedRelativePath)) {
            return null;
        }

        if ($normalizedRelativePath === '') {
            return '/';
        }

        return '/' . $normalizedRelativePath . '/';
    }

    public static function normalizeRelativePath(string $path): ?string
    {
        $normalizedPath = str_replace('\\', '/', trim($path));
        $segments = [];

        foreach (explode('/', $normalizedPath) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                return null;
            }

            if (preg_match('/[\x00-\x1F\x7F]/u', $segment)) {
                return null;
            }

            $segments[] = $segment;
        }

        return implode('/', $segments);
    }

    public static function sanitizeUploadFileName(string $fileName): ?string
    {
        $safeName = basename(str_replace('\\', '/', $fileName));
        $safeName = trim($safeName);

        if ($safeName === '' || $safeName === '.' || $safeName === '..') {
            return null;
        }

        $safeName = preg_replace('/[^\pL\pN._ -]/u', '_', $safeName);
        $safeName = preg_replace('/\s+/u', ' ', $safeName);
        $safeName = trim((string)$safeName, " .\t\n\r\0\x0B");

        if ($safeName === '' || $safeName[0] === '.') {
            return null;
        }

        return $safeName;
    }

    public static function validateSimpleName(string $name): ?string
    {
        $validatedName = trim(str_replace('\\', '/', $name));

        if ($validatedName === '' || $validatedName === '.' || $validatedName === '..') {
            return null;
        }

        if (str_contains($validatedName, '/')) {
            return null;
        }

        if (preg_match('/[\x00-\x1F\x7F]/u', $validatedName)) {
            return null;
        }

        if ($validatedName[0] === '.') {
            return null;
        }

        return $validatedName;
    }

    public static function normalizeFolderToken(string $folderToken, bool $allowParent = false): ?string
    {
        $normalizedToken = str_replace('\\', '/', trim($folderToken));

        if ($allowParent && $normalizedToken === '/..') {
            return '/..';
        }

        if (!str_starts_with($normalizedToken, '/')) {
            return null;
        }

        $folderName = self::validateSimpleName(substr($normalizedToken, 1));
        if (is_null($folderName)) {
            return null;
        }

        return '/' . $folderName;
    }

    public static function isAllowedUpload(string $fileName, string $tmpFileName): bool
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $sanitizedExtension = strtolower(MiscUtils::SanitizeExtension($extension));

        if ($extension === '' || $extension !== $sanitizedExtension || in_array($extension, self::FORBIDDEN_UPLOAD_EXTENSIONS, true)) {
            return false;
        }

        $mimeType = self::detectUploadMimeType($tmpFileName);
        if ($mimeType !== '' && in_array($mimeType, self::FORBIDDEN_UPLOAD_MIME_TYPES, true)) {
            return false;
        }

        return true;
    }

    public static function encodeUrlPath(string $path): string
    {
        $segments = array_filter(explode('/', trim(str_replace('\\', '/', $path), '/')), 'strlen');

        return implode('/', array_map('rawurlencode', $segments));
    }

    public static function resolveDirectoryFromRelativeBase(string $documentRoot, string $baseRelativePath, string $suffix = ''): ?string
    {
        $documentRoot = rtrim($documentRoot, '/');
        $baseDirectory = realpath($documentRoot . '/' . trim($baseRelativePath, '/'));

        if ($baseDirectory === false || !is_dir($baseDirectory)) {
            return null;
        }

        $targetDirectory = $baseDirectory . $suffix;

        if ($targetDirectory === false || !is_dir($targetDirectory)) {
            return null;
        }

        if (!self::isPathWithinBase($baseDirectory, $targetDirectory)) {
            return null;
        }

        return $targetDirectory;
    }

    public static function resolvePathWithinBase(string $baseDirectory, string $relativePath, bool $mustExist = true): ?string
    {
        $resolvedBaseDirectory = realpath($baseDirectory);
        if ($resolvedBaseDirectory === false || !is_dir($resolvedBaseDirectory)) {
            return null;
        }

        $normalizedRelativePath = self::normalizeRelativePath($relativePath);
        if (is_null($normalizedRelativePath)) {
            return null;
        }

        $candidatePath = $resolvedBaseDirectory;
        if ($normalizedRelativePath !== '') {
            $candidatePath .= DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalizedRelativePath);
        }

        if ($mustExist) {
            $resolvedCandidatePath = realpath($candidatePath);
            if ($resolvedCandidatePath === false) {
                return null;
            }

            return self::isPathWithinBase($resolvedBaseDirectory, $resolvedCandidatePath) ? $resolvedCandidatePath : null;
        }

        $resolvedParentPath = realpath(dirname($candidatePath));
        if ($resolvedParentPath === false) {
            return null;
        }

        return self::isPathWithinBase($resolvedBaseDirectory, $resolvedParentPath) ? $candidatePath : null;
    }

    private static function detectUploadMimeType(string $tmpFileName): string
    {
        if (!function_exists('finfo_open')) {
            return '';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return '';
        }

        $mimeType = finfo_file($finfo, $tmpFileName);
        finfo_close($finfo);

        return is_string($mimeType) ? strtolower($mimeType) : '';
    }

    private static function isPathWithinBase(string $baseDirectory, string $candidatePath): bool
    {
        return $candidatePath === $baseDirectory || strpos($candidatePath, $baseDirectory . DIRECTORY_SEPARATOR) === 0;
    }
}
