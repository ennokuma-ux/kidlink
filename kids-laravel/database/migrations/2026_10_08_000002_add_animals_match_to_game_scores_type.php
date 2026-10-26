<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The second Animals game is a drag-and-drop matcher, shipped under its own
 * game_type. Databases that already received the widened game_scores check
 * constraint get the new slot added on top of the first extension.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! $this->supportsCheckConstraints()) {
            return;
        }

        DB::statement('ALTER TABLE game_scores DROP CONSTRAINT IF EXISTS game_scores_game_type_check');
        DB::statement("ALTER TABLE game_scores ADD CONSTRAINT game_scores_game_type_check CHECK (game_type IN ('letters', 'numbers', 'colors', 'shapes', 'animals', 'animals-memory', 'animals-match'))");
    }

    public function down(): void
    {
        if (! $this->supportsCheckConstraints()) {
            return;
        }

        DB::statement('ALTER TABLE game_scores DROP CONSTRAINT IF EXISTS game_scores_game_type_check');
        DB::statement("ALTER TABLE game_scores ADD CONSTRAINT game_scores_game_type_check CHECK (game_type IN ('letters', 'numbers', 'colors', 'shapes', 'animals', 'animals-memory'))");
    }

    /**
     * SQLite cannot add or drop a CHECK constraint in place, so the backstop is
     * skipped there; fresh SQLite databases already get the widened list from
     * the original creation migration.
     */
    private function supportsCheckConstraints(): bool
    {
        return DB::getDriverName() !== 'sqlite';
    }
};