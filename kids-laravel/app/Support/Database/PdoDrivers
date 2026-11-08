<?php

namespace App\Support\Database;

use PDO;
use Throwable;

class PdoDrivers
{
    private const REQUIRED_BY_CONNECTION = [
        'mysql' => 'mysql',
        'pgsql' => 'pgsql',
        'sqlite' => 'sqlite',
        'sqlsrv' => 'sqlsrv',
    ];

    private ?array $available;

    public function __construct(?array $available = null)
    {
        $this->available = $available;
    }

    public function available(): array
    {
        return $this->available ??= PDO::getAvailableDrivers();
    }

    public function requiredFor(?string $connection): ?string
    {
        if ($connection === null || $connection === '') {
            return null;
        }

        return self::REQUIRED_BY_CONNECTION[$connection] ?? null;
    }

    public function requiredForConnection(string $connection): string
    {
        return $this->requiredFor($connection) ?? $connection;
    }

    public function missingFor(?string $connection): ?string
    {
        $required = $this->requiredFor($connection);

        if ($required === null) {
            return null;
        }

        return in_array($required, $this->available(), true) ? null : $required;
    }

    public function missingForDefaultConnection(): ?string
    {
        return $this->missingFor(config('database.default'));
    }

    public function causedByMissingDriver(Throwable $e): bool
    {
        return str_contains($e->getMessage(), 'could not find driver');
    }
}
