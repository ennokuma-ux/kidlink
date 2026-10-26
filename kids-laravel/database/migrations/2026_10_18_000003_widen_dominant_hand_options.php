<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A child's handedness can be Left, Right, Both, or Not yet established. The
 * last one is the neutral choice for toddlers whose dominant hand is still
 * undecided, and it also becomes the default so an account created without a
 * pick is never silently marked right-handed.
 *
 * Databases that already received the original three-value list get the new
 * slot and the new default added here.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        // SQLite cannot alter a CHECK constraint in place; fresh databases
        // already get the widened list from the original creation migration.
        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE students MODIFY dominant_hand ENUM('Right', 'Left', 'Both', 'Not yet established') NOT NULL DEFAULT 'Not yet established'");

            return;
        }

        DB::statement('ALTER TABLE students DROP CONSTRAINT IF EXISTS students_dominant_hand_check');
        DB::statement("ALTER TABLE students ADD CONSTRAINT students_dominant_hand_check CHECK (dominant_hand in ('Right', 'Left', 'Both', 'Not yet established'))");
        DB::statement("ALTER TABLE students ALTER COLUMN dominant_hand SET DEFAULT 'Not yet established'");
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        // Rows already recorded as "Not yet established" are kept: narrowing
        // the list back would leave them violating the restored constraint.
        DB::statement("UPDATE students SET dominant_hand = 'Right' WHERE dominant_hand = 'Not yet established'");

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE students MODIFY dominant_hand ENUM('Right', 'Left', 'Both') NOT NULL DEFAULT 'Right'");

            return;
        }

        DB::statement('ALTER TABLE students DROP CONSTRAINT IF EXISTS students_dominant_hand_check');
        DB::statement("ALTER TABLE students ADD CONSTRAINT students_dominant_hand_check CHECK (dominant_hand in ('Right', 'Left', 'Both'))");
        DB::statement("ALTER TABLE students ALTER COLUMN dominant_hand SET DEFAULT 'Right'");
    }
};
