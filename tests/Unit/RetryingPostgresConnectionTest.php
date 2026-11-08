<?php

namespace Tests\Unit;

use App\Database\RetryingPostgresConnection;
use Closure;
use Illuminate\Database\QueryException;
use Tests\TestCase;
use Throwable;

class RetryingPostgresConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.db_retry_attempts' => 4, 'app.db_retry_delay' => 5]);
    }

    public function test_transient_failure_recovers_on_retry(): void
    {
        $connection = new FakeRetryingConnection(failures: 2);

        $result = $connection->attempt();

        $this->assertSame('RECOVERED', $result);
        $this->assertSame(3, $connection->queryCalls);
        $this->assertSame(2, $connection->reconnects);
    }

    public function test_recovers_on_final_permitted_attempt(): void
    {
        $connection = new FakeRetryingConnection(failures: 3);

        $result = $connection->attempt();

        $this->assertSame('RECOVERED', $result);
        $this->assertSame(4, $connection->queryCalls);
        $this->assertSame(3, $connection->reconnects);
    }

    public function test_exhausts_attempts_and_throws_when_it_never_recovers(): void
    {
        $connection = new FakeRetryingConnection(failures: 999);

        $thrown = null;

        try {
            $connection->attempt();
        } catch (Throwable $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(QueryException::class, $thrown);
        $this->assertStringContainsString('could not translate host name', $thrown->getMessage());
        $this->assertSame(4, $connection->queryCalls);
        $this->assertSame(3, $connection->reconnects);
    }

    public function test_non_transient_errors_are_not_retried(): void
    {
        $connection = new FakeRetryingConnection(failures: 999, mode: 'syntax');

        $thrown = null;

        try {
            $connection->attempt();
        } catch (Throwable $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(QueryException::class, $thrown);
        $this->assertStringContainsString('syntax error', $thrown->getMessage());
        $this->assertSame(1, $connection->queryCalls);
        $this->assertSame(0, $connection->reconnects);
    }

    public function test_healthy_first_attempt_does_not_touch_retry_machinery(): void
    {
        $connection = new FakeRetryingConnection(failures: 0);

        $result = $connection->attempt();

        $this->assertSame('NO_THROW', $result);
        $this->assertSame(1, $connection->queryCalls);
        $this->assertSame(0, $connection->reconnects);
    }

    /**
     * The pooler dropping the TLS socket mid-handshake. This is the error the
     * app actually threw: Postgres words it "SQLSTATE[08006] [7] connection to
     * server ... failed: SSL SYSCALL error: EOF detected", while Laravel's
     * detector only lists the MySQL phrasing of the same fault, so it was
     * treated as permanent and the retry never ran.
     */
    public function test_recovers_from_an_ssl_socket_dropped_by_the_pooler(): void
    {
        $connection = new FakeRetryingConnection(failures: 1, mode: 'ssl-eof');

        $result = $connection->attempt();

        $this->assertSame('RECOVERED', $result);
        $this->assertSame(1, $connection->reconnects);
    }

    public function test_recovers_when_the_pooler_runs_out_of_connection_slots(): void
    {
        $connection = new FakeRetryingConnection(failures: 1, mode: 'too-many');

        $this->assertSame('RECOVERED', $connection->attempt());
        $this->assertSame(1, $connection->reconnects);
    }

    public function test_recovers_from_an_administrator_terminating_the_backend(): void
    {
        $connection = new FakeRetryingConnection(failures: 1, mode: 'admin-shutdown');

        $this->assertSame('RECOVERED', $connection->attempt());
    }

    /**
     * Connection trouble is worth retrying; a wrong password is not. Retrying
     * bad credentials just delays the real error.
     */
    public function test_authentication_failures_are_not_retried(): void
    {
        $connection = new FakeRetryingConnection(failures: 999, mode: 'auth');

        $thrown = null;

        try {
            $connection->attempt();
        } catch (Throwable $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(QueryException::class, $thrown);
        $this->assertStringContainsString('password authentication failed', $thrown->getMessage());
        $this->assertSame(1, $connection->queryCalls, 'A bad password should fail on the first try.');
        $this->assertSame(0, $connection->reconnects);
    }
}

class FakeRetryingConnection extends RetryingPostgresConnection
{
    public int $queryCalls = 0;

    public int $reconnects = 0;

    public function __construct(
        private int $failures = 0,
        private string $mode = 'dns',
    ) {
        parent::__construct(null, 'postgres', '', ['name' => 'pgsql']);
    }

    public function reconnect()
    {
        $this->reconnects++;
    }

    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        $this->queryCalls++;

        if ($this->queryCalls <= $this->failures) {
            throw $this->error();
        }

        return 'RECOVERED';
    }

    public function attempt(): mixed
    {
        try {
            $this->runQueryCallback('select 1', [], fn () => 'first');
        } catch (QueryException $e) {
            return $this->tryAgainIfCausedByLostConnection($e, 'select 1', [], fn () => 'RECOVERED');
        }

        return 'NO_THROW';
    }

    private function error(): QueryException
    {
        [$message, $sqlState, $native] = match ($this->mode) {
            'dns' => [
                'SQLSTATE[08006] [7] could not translate host name "aws-0-ap-southeast-1.pooler.supabase.com" to address: Unknown host',
                '08006',
                'could not translate host name',
            ],
            'ssl-eof' => [
                'SQLSTATE[08006] [7] connection to server at "aws-0-ap-southeast-1.pooler.supabase.com" (52.77.146.31), port 6543 failed: SSL SYSCALL error: EOF detected',
                '08006',
                'SSL SYSCALL error: EOF detected',
            ],
            'too-many' => [
                'SQLSTATE[53300]: too many connections for role "postgres"',
                '53300',
                'too many connections',
            ],
            'admin-shutdown' => [
                'FATAL:  terminating connection due to administrator command',
                null,
                'terminating connection due to administrator command',
            ],
            'auth' => [
                'SQLSTATE[28P01]: password authentication failed for user "postgres"',
                '28P01',
                'password authentication failed',
            ],
            default => [
                'SQLSTATE[42601] syntax error at or near "frobnicate"',
                '42601',
                'syntax error',
            ],
        };

        $previous = new \PDOException($message);
        $previous->errorInfo = [$sqlState, 7, $native];

        return new QueryException('pgsql', 'select * from "users" where "id" = 1 limit 1', [], $previous, [
            'driver' => 'pgsql',
            'host' => 'aws-0-ap-southeast-1.pooler.supabase.com',
            'port' => 6543,
            'database' => 'postgres',
        ]);
    }
}
