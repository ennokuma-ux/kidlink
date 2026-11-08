<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fixes: SQLSTATE[42P01] relation "announcement_polls" does not exist.
     *
     * Polls attached to an announcement: the poll, its answer options, and one
     * vote per person per option. The column names are a best guess from the
     * table names; if your Poll model expects different ones, the next error
     * names the column, so adjust it here (or in the model) to match.
     * Each table is only created if it is missing.
     */
    public function up(): void
    {
        if (! Schema::hasTable('announcement_polls')) {
            Schema::create('announcement_polls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete();
                $table->string('question');
                $table->boolean('allow_multiple')->default(false);
                $table->timestamp('closes_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('announcement_poll_options')) {
            Schema::create('announcement_poll_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_poll_id')->constrained('announcement_polls')->cascadeOnDelete();
                $table->string('label');
                $table->unsignedSmallInteger('position')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('announcement_poll_votes')) {
            Schema::create('announcement_poll_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_poll_id')->constrained('announcement_polls')->cascadeOnDelete();
                $table->foreignId('announcement_poll_option_id')->constrained('announcement_poll_options')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['announcement_poll_option_id', 'user_id'], 'poll_vote_option_user_unique');
                $table->index(['announcement_poll_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_poll_votes');
        Schema::dropIfExists('announcement_poll_options');
        Schema::dropIfExists('announcement_polls');
    }
};
