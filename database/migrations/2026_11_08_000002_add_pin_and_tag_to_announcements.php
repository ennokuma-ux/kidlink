<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // This overlaps with the follow-up pinning migration; guard each
            // column so either migration can safely run first on upgraded DBs.
            if (! Schema::hasColumn('announcements', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->index();
            }
            if (! Schema::hasColumn('announcements', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable();
            }
            if (! Schema::hasColumn('announcements', 'tag')) {
                $table->string('tag', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex(['is_pinned']);
            $table->dropColumn(['is_pinned', 'pinned_at', 'tag']);
        });
    }
};
