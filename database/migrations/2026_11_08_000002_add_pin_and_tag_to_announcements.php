<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
        // Keep this migration reversible without dropping columns that may be
        // shared with the guarded follow-up migration or existing installations.
    }
};
