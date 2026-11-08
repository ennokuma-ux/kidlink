<?php

namespace App\Support\Database;

class PhpBinaries
{
    /**
     * Locate a PHP binary that can load the given PDO driver, so the error page
     * can suggest a real command instead of a hardcoded path from one machine.
     *
     * Returns null when no binary on this machine has the driver. Falling back to
     * the running binary would be worse than silence, since by definition that one
     * cannot load it.
     *
     * @return array{path: string, version: string}|null
     */
    public static function findSupporting(string $driver): ?array
    {
        foreach (self::candidates() as $path) {
            if (self::canLoad($path, $driver)) {
                return ['path' => $path, 'version' => self::version($path)];
            }
        }

        return null;
    }

    /** @return list<string> */
    private static function candidates(): array
    {
        $candidates = [];

        $laragon = 'C:\laragon\bin\php';
        if (is_dir($laragon)) {
            $versions = glob($laragon.'\php-*', GLOB_ONLYDIR) ?: [];
            usort($versions, fn ($a, $b) => version_compare(basename($b), basename($a)));
            foreach ($versions as $dir) {
                $candidates[] = $dir.'\php.exe';
            }
        }

        $candidates[] = 'C:\xampp\php\php.exe';

        $herd = getenv('USERPROFILE').'\.config\herd-lite\bin\php.exe';
        if ($herd) {
            $candidates[] = $herd;
        }

        // Whatever "php" resolves to on PATH, so a correct setup needs no hardcoding.
        $path = getenv('PATH');
        if (is_string($path) && $path !== '') {
            foreach (array_filter(explode(PATH_SEPARATOR, $path)) as $dir) {
                $candidates[] = rtrim($dir, '\\/').'\php.exe';
            }
        }

        return array_values(array_unique($candidates));
    }

    private static function canLoad(string $path, string $driver): bool
    {
        if ($path === '' || ! is_file($path)) {
            return false;
        }

        // PDO driver names are plain identifiers; anything else is not a real one.
        if (preg_match('/^[A-Za-z0-9_]+$/', $driver) !== 1) {
            return false;
        }

        // Probing in a subprocess is the only reliable test: the missing extension
        // cannot be "toggled on" inside the process that is already running.
        //
        // The snippet deliberately uses single quotes and contains no double quotes.
        // escapeshellarg() wraps it in double quotes and backslash-escapes inner ones,
        // but cmd.exe does not understand backslash escaping and strips them, which
        // silently turns a quoted literal into a bare identifier.
        $output = @shell_exec(sprintf(
            '%s -r %s 2>&1',
            escapeshellarg($path),
            escapeshellarg(sprintf(
                'echo in_array(\'%s\', PDO::getAvailableDrivers(), true) ? 1 : 0;',
                $driver
            ))
        ));

        return is_string($output) && trim($output) === '1';
    }

    private static function version(string $path): string
    {
        $output = @shell_exec(sprintf(
            '%s -r %s 2>&1',
            escapeshellarg($path),
            escapeshellarg('echo PHP_VERSION;')
        ));

        return is_string($output) && trim($output) !== '' ? trim($output) : 'unknown';
    }
}
