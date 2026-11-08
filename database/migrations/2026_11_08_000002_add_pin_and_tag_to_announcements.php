<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // Pinned announcements stay at the top of the board until unpinned.
            $table->boolean('is_pinned')->default(false)->index();
            $table->timestamp('pinned_at')->nullable();
            // Optional label: "urgent" or "emergency" (see Announcement::TAGS).
            $table->string('tag', 20)->nullable();
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
