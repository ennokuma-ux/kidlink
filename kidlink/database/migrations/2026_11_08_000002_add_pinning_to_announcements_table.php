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
        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false);
            }
            if (! Schema::hasColumn('announcements', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable();
            }
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->index(['is_pinned', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex(['is_pinned', 'created_at']);
            $table->dropColumn(['is_pinned', 'pinned_at']);
        });
    }
};
