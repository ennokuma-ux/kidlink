<?php

namespace App\Database;

use App\Exceptions\MissingDatabaseDriverException;
use App\Support\Database\PdoDrivers;
use Closure;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\QueryException;
use PDOException;
use Throwable;

class RetryingPostgresConnection extends PostgresConnection
{
    /**
     * SQLSTATEs that are worth another attempt even though they carry no useful
     * message. 53300 is the one that bites on a shared pooler: the pooler hands
     * out slots, and when it runs dry the connection dies before any query runs.
     */
    private const TRANSIENT_SQLSTATES = [
        '53300', // too_many_connections
        '57P01', // admin_shutdown
        '57P02', // crash_shutdown
        '57P03', // cannot_connect_now
    ];

    /**
     * Postgres reports these as plain FATAL text with no SQLSTATE at all.
     */
    private const TRANSIENT_PGSQL_MESSAGES = [
        'terminating connection due to administrator command',
        'server closed the connection unexpectedly',
        'SSL SYSCALL error',
        'SSL error',
        'could not connect to server',
        'timeout expired',
    ];

    private ?PdoDrivers $pdoDrivers;

    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [], ?PdoDrivers $pdoDrivers = null)
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);

        $this->pdoDrivers = $pdoDrivers;
    }

    public function getPdo()
    {
        $drivers = $this->pdoDrivers ??= new PdoDrivers;

        if (($missing = $drivers->missingFor('pgsql')) !== null) {
            throw new MissingDatabaseDriverException('pgsql', $missing);
        }

        return parent::getPdo();
    }

    protected function tryAgainIfCausedByLostConnection(QueryException $e, $query, $bindings, Closure $callback)
    {
        if (! $this->isTransient($e)) {
            throw $e;
        }

        $attempts = max(1, (int) config('app.db_retry_attempts', 4));
        $delayMs = max(0, (int) config('app.db_retry_delay', 400));

        $last = $e;

        for ($attempt = 1; $attempt < $attempts; $attempt++) {
            usleep($delayMs * 1000 * $attempt);

            try {
                $this->reconnect();
            } catch (Throwable $reconnectError) {
                if (! $this->isTransient($reconnectError)) {
                    throw $reconnectError;
                }

                $last = $reconnectError;
                continue;
            }

            try {
                return $this->runQueryCallback($query, $bindings, $callback);
            } catch (Throwable $queryError) {
                if (! $this->isTransient($queryError)) {
                    throw $queryError;
                }

                $last = $queryError;
            }
        }

        throw $last;
    }

    private function isTransient(Throwable $e): bool
    {
        $probe = $e instanceof QueryException && $e->getPrevious() instanceof Throwable
            ? $e->getPrevious()
            : $e;

        return $this->isTransientSqlState($probe)
            || $this->matchesTransientMessage($probe)
            || $this->causedByLostConnection($probe);
    }

    /**
     * Classify by SQLSTATE rather than by wording. Laravel's detector matches
     * literal message strings, so the many Postgres connection failures fall
     * straight through it - "SSL SYSCALL error: EOF detected" is only listed in
     * its MySQL phrasing, as "SQLSTATE[HY000]: General error: 7 ...", and the
     * Postgres equivalent never matches.
     *
     * Class 08 is the SQL standard connection exception and is always worth a
     * retry. Class 28 (authentication) deliberately is not.
     */
    private function isTransientSqlState(Throwable $e): bool
    {
        $state = $this->sqlStateOf($e);

        if ($state === null) {
            return false;
        }

        return str_starts_with($state, '08')
            || in_array($state, self::TRANSIENT_SQLSTATES, true);
    }

    private function sqlStateOf(Throwable $e): ?string
    {
        if ($e instanceof PDOException && is_array($e->errorInfo ?? null)) {
            $state = $e->errorInfo[0] ?? null;

            if (is_string($state) && $state !== '') {
                return strtoupper($state);
            }
        }

        if (preg_match('/SQLSTATE\[([0-9A-Za-z]{5})\]/', $e->getMessage(), $matches)) {
            return strtoupper($matches[1]);
        }

        return null;
    }

    private function matchesTransientMessage(Throwable $e): bool
    {
        $message = $e->getMessage();

        foreach (self::TRANSIENT_PGSQL_MESSAGES as $needle) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }
}
