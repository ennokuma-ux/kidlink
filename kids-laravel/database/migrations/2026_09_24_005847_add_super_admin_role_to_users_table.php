<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->supportsCheckConstraints()) {
            return;
        }

        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('teacher', 'parent', 'student', 'super_admin'))");
    }

    public function down(): void
    {
        if (! $this->supportsCheckConstraints()) {
            return;
        }

        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('teacher', 'parent', 'student'))");
    }

    /**
     * SQLite cannot add or drop a CHECK constraint in place, so the role
     * backstop is skipped there and enforced in the application layer instead.
     */
    private function supportsCheckConstraints(): bool
    {
        return DB::getDriverName() !== 'sqlite';
    }
};
