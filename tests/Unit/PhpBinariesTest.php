<?php

namespace Tests\Unit;

use App\Exceptions\MissingDatabaseDriverException;
use App\Support\Database\PhpBinaries;
use PDO;
use Tests\TestCase;

class PhpBinariesTest extends TestCase
{
    public function test_it_finds_a_binary_that_can_load_the_driver()
    {
        // This test suite runs on a PHP that has pdo_pgsql, so at least one
        // candidate must be discoverable.
        $found = PhpBinaries::findSupporting('pgsql');

        $this->assertIsArray($found, 'Expected to find a PHP binary with pdo_pgsql.');
        $this->assertFileExists($found['path']);
        $this->assertMatchesRegularExpression('/^\d+\.\d+/', $found['version']);
    }

    public function test_it_returns_null_when_no_binary_has_the_driver()
    {
        $this->assertNull(
            PhpBinaries::findSupporting('definitely_not_a_pdo_driver'),
            'Must not fall back to the running binary, since that one is already broken.'
        );
    }

    public function test_the_exception_exposes_a_usable_suggestion()
    {
        $exception = MissingDatabaseDriverException::forConnection(
            app(\App\Support\Database\PdoDrivers::class),
            'pgsql',
        );

        $suggested = $exception->suggestedBinary();

        $this->assertSame('pgsql', $exception->pdoDriver);
        $this->assertSame('pdo_pgsql', $exception->extensionName());
        $this->assertIsArray($suggested);
        $this->assertFileExists($suggested['path']);

        // The suggestion is only useful if it differs from a broken running binary.
        // This suite runs on a PHP that already has pdo_pgsql, so they may match here.
        if (! in_array('pgsql', PDO::getAvailableDrivers(), true)) {
            $this->assertNotSame(
                PHP_BINARY,
                $suggested['path'],
                'A working PHP must never be suggested as the alternative to a broken one.'
            );
        }
    }
}
