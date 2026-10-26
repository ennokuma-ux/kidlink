<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The ECCD checklist grew a seventh domain, "Social-Emotional". Databases
 * that already received the original six-value list get the new slot added
 * here; fresh databases already get it from the creation migration.
 *
 * SQLite cannot alter a CHECK constraint in place, so it is skipped: the
 * tests and any fresh install build the widened list from the start.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE eccd_records MODIFY domain ENUM('Gross Motor', 'Fine Motor', 'Self-Help', 'Receptive Language', 'Expressive Language', 'Cognitive', 'Social-Emotional') NOT NULL");

            return;
        }

        DB::statement('ALTER TABLE eccd_records DROP CONSTRAINT IF EXISTS eccd_records_domain_check');
        DB::statement("ALTER TABLE eccd_records ADD CONSTRAINT eccd_records_domain_check CHECK (domain in ('Gross Motor', 'Fine Motor', 'Self-Help', 'Receptive Language', 'Expressive Language', 'Cognitive', 'Social-Emotional'))");
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        // Social-Emotional rows would violate the restored six-value list.
        DB::statement("DELETE FROM eccd_records WHERE domain = 'Social-Emotional'");

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE eccd_records MODIFY domain ENUM('Gross Motor', 'Fine Motor', 'Self-Help', 'Receptive Language', 'Expressive Language', 'Cognitive') NOT NULL");

            return;
        }

        DB::statement('ALTER TABLE eccd_records DROP CONSTRAINT IF EXISTS eccd_records_domain_check');
        DB::statement("ALTER TABLE eccd_records ADD CONSTRAINT eccd_records_domain_check CHECK (domain in ('Gross Motor', 'Fine Motor', 'Self-Help', 'Receptive Language', 'Expressive Language', 'Cognitive'))");
    }
};
