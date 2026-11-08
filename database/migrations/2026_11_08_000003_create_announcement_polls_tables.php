<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One optional poll per announcement.
        Schema::create('announcement_polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->unique()->constrained('announcements')->cascadeOnDelete();
            $table->string('question');
            $table->boolean('allow_multiple')->default(false);
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();
        });

        Schema::create('announcement_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_poll_id')->constrained('announcement_polls')->cascadeOnDelete();
            $table->string('label', 150);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['announcement_poll_id', 'position']);
        });

        Schema::create('announcement_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_poll_id')->constrained('announcement_polls')->cascadeOnDelete();
            $table->foreignId('announcement_poll_option_id')->constrained('announcement_poll_options')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // A person can pick an option only once.
            $table->unique(['announcement_poll_option_id', 'user_id'], 'poll_vote_option_user_unique');
            $table->index(['announcement_poll_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_poll_votes');
        Schema::dropIfExists('announcement_poll_options');
        Schema::dropIfExists('announcement_polls');
    }
};
