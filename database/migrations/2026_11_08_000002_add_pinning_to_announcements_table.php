<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fixes: SQLSTATE[42703] column "is_pinned" does not exist (announcements
     * are ordered pinned-first). Guarded so it is safe to run on a database
     * where the columns were already added by hand.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('announcements', 'is_pinned')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->boolean('is_pinned')->default(false)->index();
            });
        }

        if (! Schema::hasColumn('announcements', 'pinned_at')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->timestamp('pinned_at')->nullable();
            });
        }

        if (! Schema::hasColumn('announcements', 'tag')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->string('tag', 20)->nullable();
            });
        }
    }

    public function down(): void
    {
        // Intentionally retain shared announcement columns on rollback.
    }
};
