<?php

namespace Tests\Feature;

use App\Console\Commands\EnvironmentDoctor;
use App\Console\Commands\ServeCommand;
use App\Exceptions\MissingDatabaseDriverException;
use App\Support\Database\PdoDrivers;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;
use PDOException;
use Tests\TestCase;

class MissingDatabaseDriverTest extends TestCase
{
    public function test_doctor_reports_a_healthy_runtime(): void
    {
        $this->artisan('env:doctor')
            ->expectsOutputToContain('Required driver')
            ->expectsOutputToContain('Connection')
            ->assertExitCode(EnvironmentDoctor::EXIT_OK);
    }

    public function test_doctor_fails_when_the_driver_is_missing(): void
    {
        $this->app->instance(PdoDrivers::class, new PdoDrivers(['mysql', 'sqlite']));
        config(['database.default' => 'pgsql']);

        $this->artisan('env:doctor')
            ->expectsOutputToContain('pdo_pgsql -> MISSING')
            ->expectsOutputToContain('serve.cmd')
            ->assertExitCode(EnvironmentDoctor::EXIT_MISSING_DRIVER);
    }

    public function test_doctor_reports_the_connection_failure_separately(): void
    {
        $this->app->instance(PdoDrivers::class, new PdoDrivers(['pgsql']));
        config([
            'database.default' => 'pgsql',
            'database.connections.pgsql.host' => '127.0.0.1',
            'database.connections.pgsql.port' => 1,
        ]);

        $this->artisan('env:doctor')
            ->expectsOutputToContain('Connection')
            ->assertExitCode(EnvironmentDoctor::EXIT_CONNECTION_FAILED);
    }

    public function test_doctor_can_print_only_the_required_extension_name(): void
    {
        $this->artisan('env:doctor --print-driver')
            ->expectsOutput('pdo_sqlite')
            ->assertExitCode(EnvironmentDoctor::EXIT_OK);
    }

    public function test_doctor_json_output_reports_the_missing_driver(): void
    {
        $this->app->instance(PdoDrivers::class, new PdoDrivers(['mysql', 'sqlite']));
        config(['database.default' => 'pgsql']);

        // A single expectation: PendingCommand only satisfies one substring
        // expectation per written line, and the JSON is emitted as one line.
        $this->artisan('env:doctor --json')
            ->expectsOutputToContain('"required_extension":"pdo_pgsql","missing_driver":"pgsql"')
            ->assertExitCode(EnvironmentDoctor::EXIT_MISSING_DRIVER);
    }

    public function test_a_pgsql_query_without_the_driver_fails_fast_with_an_actionable_message(): void
    {
        $connection = new \App\Database\RetryingPostgresConnection(
            null,
            'postgres',
            '',
            ['name' => 'pgsql'],
            new PdoDrivers(['mysql', 'sqlite']),
        );

        $this->expectException(MissingDatabaseDriverException::class);
        $this->expectExceptionMessage('pdo_pgsql');

        $connection->getPdo();
    }

    public function test_a_query_exception_wrapping_the_driver_error_still_renders_the_help_page(): void
    {
        $this->app->instance(PdoDrivers::class, new PdoDrivers(['mysql', 'sqlite']));
        config(['database.default' => 'pgsql']);

        $previous = new MissingDatabaseDriverException('pgsql', 'pgsql');
        $wrapped = new QueryException('pgsql', 'select * from "users"', [], $previous);

        $this->assertTrue(MissingDatabaseDriverException::matches($wrapped, new PdoDrivers(['mysql'])));

        Route::get('/driver-probe', function () use ($wrapped) {
            throw $wrapped;
        });

        $this->get('/driver-probe')
            ->assertStatus(503)
            ->assertSee('pdo_pgsql')
            ->assertSee('serve.cmd')
            ->assertDontSee('select * from');
    }

    public function test_a_raw_pdo_driver_error_renders_the_help_page(): void
    {
        $this->app->instance(PdoDrivers::class, new PdoDrivers(['mysql', 'sqlite']));
        config(['database.default' => 'pgsql']);

        Route::get('/driver-probe', function () {
            throw new PDOException('could not find driver');
        });

        $this->get('/driver-probe')
            ->assertStatus(503)
            ->assertSee('pdo_pgsql')
            ->assertSee('serve.cmd');
    }

    public function test_json_clients_receive_a_structured_503(): void
    {
        $this->app->instance(PdoDrivers::class, new PdoDrivers(['mysql', 'sqlite']));
        config(['database.default' => 'pgsql']);

        Route::get('/driver-probe', function () {
            throw new PDOException('could not find driver');
        });

        $this->getJson('/driver-probe')
            ->assertStatus(503)
            ->assertJson([
                'required_extension' => 'pdo_pgsql',
                'php_binary' => PHP_BINARY,
            ]);
    }

    public function test_unrelated_errors_are_not_swallowed(): void
    {
        $this->app->instance(PdoDrivers::class, new PdoDrivers(['mysql', 'sqlite']));

        Route::get('/other-probe', function () {
            throw new PDOException('SQLSTATE[08006] could not connect to server');
        });

        $this->get('/other-probe')->assertStatus(500);
    }

    public function test_serve_refuses_to_start_when_the_driver_is_missing(): void
    {
        $this->app->instance(PdoDrivers::class, new PdoDrivers(['mysql', 'sqlite']));
        config(['database.default' => 'pgsql']);

        $this->artisan('serve --host=127.0.0.1 --port=8197')
            ->expectsOutputToContain('pdo_pgsql -> MISSING')
            ->expectsOutputToContain('Cannot start the server')
            ->expectsOutputToContain('serve.cmd')
            ->expectsOutputToContain('--skip-runtime-check')
            ->assertExitCode(ServeCommand::EXIT_MISSING_DRIVER);
    }

    public function test_serve_exposes_a_flag_to_bypass_the_runtime_check(): void
    {
        $command = $this->app->make(ServeCommand::class);

        $this->assertTrue($command->getDefinition()->hasOption('skip-runtime-check'));
    }
}
