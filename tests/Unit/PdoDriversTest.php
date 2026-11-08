<?php

namespace Tests\Unit;

use App\Support\Database\PdoDrivers;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

class PdoDriversTest extends TestCase
{
    public function test_it_reports_the_driver_required_by_a_known_connection(): void
    {
        $drivers = new PdoDrivers(['mysql', 'pgsql']);

        $this->assertSame('pgsql', $drivers->requiredFor('pgsql'));
        $this->assertSame('mysql', $drivers->requiredFor('mysql'));
        $this->assertSame('sqlite', $drivers->requiredFor('sqlite'));
    }

    public function test_it_returns_null_for_an_unknown_connection(): void
    {
        $drivers = new PdoDrivers(['mysql']);

        $this->assertNull($drivers->requiredFor('mongodb'));
        $this->assertNull($drivers->requiredFor(''));
        $this->assertNull($drivers->requiredFor(null));
    }

    public function test_it_falls_back_to_the_connection_name_for_unknown_drivers(): void
    {
        $drivers = new PdoDrivers(['mongodb']);

        $this->assertSame('mongodb', $drivers->requiredForConnection('mongodb'));
    }

    public function test_it_detects_a_missing_driver(): void
    {
        $drivers = new PdoDrivers(['mysql', 'sqlite']);

        $this->assertSame('pgsql', $drivers->missingFor('pgsql'));
        $this->assertNull($drivers->missingFor('mysql'));
    }

    public function test_it_ignores_connections_with_no_pdo_requirement(): void
    {
        $drivers = new PdoDrivers(['mysql']);

        $this->assertNull($drivers->missingFor('mongodb'));
    }

    public function test_it_uses_the_real_pdo_driver_list_when_none_is_injected(): void
    {
        $drivers = new PdoDrivers;

        $this->assertSame(PDO::getAvailableDrivers(), $drivers->available());
    }

    public function test_it_recognises_the_raw_pdo_message(): void
    {
        $drivers = new PdoDrivers(['mysql']);

        $this->assertTrue($drivers->causedByMissingDriver(new PDOException('could not find driver')));
    }

    public function test_it_ignores_unrelated_errors(): void
    {
        $drivers = new PdoDrivers(['mysql']);

        $this->assertFalse($drivers->causedByMissingDriver(new PDOException('SQLSTATE[08006] connection refused')));
    }
}
