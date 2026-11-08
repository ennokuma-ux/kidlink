<?php

namespace App\Console\Commands;

use App\Support\Database\PdoDrivers;
use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;
use Symfony\Component\Console\Input\InputOption;

class ServeCommand extends BaseServeCommand
{
    public const EXIT_MISSING_DRIVER = 1;

    protected $name = 'serve';

    protected $description = 'Serve the application on the PHP development server';

    #[\Override]
    public function handle()
    {
        if (! $this->option('skip-runtime-check') && $this->runtimeIsBroken()) {
            $this->reportBrokenRuntime();

            return self::EXIT_MISSING_DRIVER;
        }

        return parent::handle();
    }

    #[\Override]
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['skip-runtime-check', null, InputOption::VALUE_NONE, 'Start the server even if this PHP build cannot load the driver DB_CONNECTION names. Does not make the driver appear; every query will still fail.'],
        ]);
    }

    private function runtimeIsBroken(): bool
    {
        try {
            return app(PdoDrivers::class)->missingForDefaultConnection() !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    private function reportBrokenRuntime(): void
    {
        $connection = (string) config('database.default');
        $missing = app(PdoDrivers::class)->missingForDefaultConnection();
        $required = app(PdoDrivers::class)->requiredForConnection($connection);
        $available = app(PdoDrivers::class)->available();

        $this->table(['Check', 'Result'], [
            ['PHP binary', PHP_BINARY],
            ['PHP version', PHP_VERSION],
            ['PDO drivers', implode(', ', $available) ?: 'none'],
            ['DB_CONNECTION', $connection],
            ['Required driver', 'pdo_'.$required.' -> MISSING'],
        ]);

        $this->newLine();
        $this->error("Cannot start the server: pdo_{$missing} is not loaded, so the [{$connection}] connection cannot be used.");
        $this->newLine();
        $this->line('The server was NOT started. Start it with the project launcher so a PHP with the right driver is used:');
        $this->line('  <fg=cyan>serve.cmd</>');
        $this->line('  <fg=cyan>serve.cmd 8000</>');
        $this->newLine();
        $this->line('Or point KIDLINK_PHP at a suitable binary:');
        $this->line('  <fg=cyan>set KIDLINK_PHP=C:\path\to\php.exe</>');
        $this->newLine();
        // Do not advertise --skip-runtime-check here. It was tried twice on this
        // machine and produced a server that started fine and then failed every
        // request, which is worse than refusing to start. The flag stays for the
        // one case it genuinely serves: pointing DB_CONNECTION at a driver this
        // build does have (for example a temporary sqlite switch).
        $this->comment('If your .env points at a driver this PHP build does have, you can pass');
        $this->comment('--skip-runtime-check. It does not fix a missing driver.');
    }
}
