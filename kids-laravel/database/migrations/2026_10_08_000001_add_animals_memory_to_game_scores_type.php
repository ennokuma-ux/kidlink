<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The game_scores enum/check constraint backstops the game types that can be
 * recorded. Adding a second Animals game extends it in place so results for the
 * new game are accepted by both fresh and already-migrated databases.
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
        DB::statement("ALTER TABLE game_scores ADD CONSTRAINT game_scores_game_type_check CHECK (game_type IN ('letters', 'numbers', 'colors', 'shapes', 'animals'))");
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