<?php

namespace App\Console\Commands;

use App\Support\Database\PdoDrivers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class EnvironmentDoctor extends Command
{
    public const EXIT_OK = 0;

    public const EXIT_MISSING_DRIVER = 1;

    public const EXIT_CONNECTION_FAILED = 2;

    protected $signature = 'env:doctor
                            {--json : Emit the report as JSON for scripts}
                            {--print-driver : Print only the required extension name, then exit}';

    protected $description = 'Report the PHP runtime, the PDO driver the app needs, and whether the database is reachable';

    public function handle(PdoDrivers $drivers): int
    {
        $connection = (string) config('database.default');
        $required = $drivers->requiredForConnection($connection);
        $missing = $drivers->missingFor($connection);
        $config = (array) config("database.connections.{$connection}", []);

        if ($this->option('print-driver')) {
            $this->line('pdo_'.$required);

            return self::EXIT_OK;
        }

        if ($this->option('json')) {
            return $this->json($drivers, $connection, $required, $missing, $config);
        }

        $rows = [
            ['PHP binary', PHP_BINARY],
            ['PHP version', PHP_VERSION],
            ['PDO drivers', implode(', ', $drivers->available()) ?: 'none'],
            ['DB_CONNECTION', $connection],
            ['Required driver', 'pdo_'.$required.' -> '.($missing === null ? 'loaded' : 'MISSING')],
        ];

        if (($target = $this->target($config)) !== null) {
            $rows[] = ['Target', $target];
        }

        if ($missing !== null) {
            $rows[] = ['Connection', 'not attempted, the driver is missing'];

            $this->table(['Check', 'Result'], $rows);
            $this->newLine();
            $this->error("This PHP build cannot use the [{$connection}] connection: pdo_{$missing} is not loaded.");
            $this->line('Start the app with the project launcher so a PHP with the right driver is used:');
            $this->line('  <fg=cyan>serve.cmd</>');
            $this->line('Or point KIDLINK_PHP at a suitable binary:');
            $this->line('  <fg=cyan>set KIDLINK_PHP=C:\path\to\php.exe</>');
            $this->newLine();

            return self::EXIT_MISSING_DRIVER;
        }

        try {
            DB::connection($connection)->select('select 1');

            $rows[] = ['Connection', 'OK'];
        } catch (Throwable $e) {
            $rows[] = ['Connection', 'FAILED: '.$e->getMessage()];

            $this->table(['Check', 'Result'], $rows);
            $this->newLine();
            $this->error("The [{$connection}] driver is loaded but the database could not be reached.");
            $this->newLine();

            return self::EXIT_CONNECTION_FAILED;
        }

        $this->table(['Check', 'Result'], $rows);
        $this->newLine();
        $this->info('Runtime is ready.');

        return self::EXIT_OK;
    }

    private function json(PdoDrivers $drivers, string $connection, string $required, ?string $missing, array $config): int
    {
        $connected = false;
        $error = null;

        if ($missing === null) {
            try {
                DB::connection($connection)->select('select 1');
                $connected = true;
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }

        $this->line(json_encode([
            'php_binary' => PHP_BINARY,
            'php_version' => PHP_VERSION,
            'available_drivers' => $drivers->available(),
            'connection' => $connection,
            'required_driver' => $required,
            'required_extension' => 'pdo_'.$required,
            'missing_driver' => $missing,
            'target' => $this->target($config),
            'connected' => $connected,
            'error' => $error,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        if ($missing !== null) {
            return self::EXIT_MISSING_DRIVER;
        }

        return $connected ? self::EXIT_OK : self::EXIT_CONNECTION_FAILED;
    }

    private function target(array $config): ?string
    {
        if (($config['driver'] ?? null) === 'sqlite') {
            return (string) ($config['database'] ?? '');
        }

        $host = $config['host'] ?? null;

        if ($host === null) {
            return null;
        }

        return sprintf(
            '%s@%s:%s/%s',
            $config['username'] ?? '',
            $host,
            $config['port'] ?? '',
            $config['database'] ?? '',
        );
    }
}
