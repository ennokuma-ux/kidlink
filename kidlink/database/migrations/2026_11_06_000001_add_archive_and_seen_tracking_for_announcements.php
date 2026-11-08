<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // Archiving replaces deleting: the row stays, it just leaves the board.
            if (! Schema::hasColumn('announcements', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->index();
            }
            if (! Schema::hasColumn('announcements', 'archived_by')) {
                $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        if (! Schema::hasColumn('users', 'announcements_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                // Last time this user opened the announcement board. Anything posted
                // after it counts as "new" and drives the red badge.
                $table->timestamp('announcements_seen_at')->nullable();
            });

            // Existing users shouldn't open the app to a badge for every old post.
            DB::table('users')->update(['announcements_seen_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('announcements_seen_at');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropColumn(['archived_at', 'archived_by']);
        });
    }
};
