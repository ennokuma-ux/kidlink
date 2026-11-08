<?php

namespace App\Exceptions;

use App\Support\Database\PhpBinaries;
use App\Support\Database\PdoDrivers;
use RuntimeException;
use Throwable;

class MissingDatabaseDriverException extends RuntimeException
{
    public function __construct(
        public readonly string $connection,
        public readonly string $pdoDriver,
    ) {
        parent::__construct(sprintf(
            'The extension [%s] is not loaded in %s (PHP %s), so the [%s] connection cannot be used. Run "php artisan env:doctor" for details.',
            'pdo_'.$pdoDriver,
            PHP_BINARY,
            PHP_VERSION,
            $connection,
        ));
    }

    public static function forConnection(PdoDrivers $drivers, string $connection): self
    {
        return new self($connection, $drivers->requiredForConnection($connection));
    }

    public static function forDefaultConnection(PdoDrivers $drivers): self
    {
        return self::forConnection($drivers, (string) config('database.default'));
    }

    public static function matches(Throwable $e, PdoDrivers $drivers): bool
    {
        do {
            if ($e instanceof self || $drivers->causedByMissingDriver($e)) {
                return true;
            }

            $e = $e->getPrevious();
        } while ($e !== null);

        return false;
    }

    public function extensionName(): string
    {
        return 'pdo_'.$this->pdoDriver;
    }

    public function runningBinary(): string
    {
        return PHP_BINARY;
    }

    /**
     * A binary elsewhere on this machine that can load the driver, if there is one.
     *
     * @return array{path: string, version: string}|null
     */
    public function suggestedBinary(): ?array
    {
        return PhpBinaries::findSupporting($this->pdoDriver);
    }
}
