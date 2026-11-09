<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->text('body');
                $table->timestamps();
                $table->index('created_at');
            });
        }

        $this->addAnnouncementColumn('is_pinned', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false)->index();
        });
        $this->addAnnouncementColumn('pinned_at', function (Blueprint $table) {
            $table->timestamp('pinned_at')->nullable();
        });
        $this->addAnnouncementColumn('tag', function (Blueprint $table) {
            $table->string('tag', 20)->nullable();
        });
        $this->addAnnouncementColumn('archived_at', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->index();
        });
        $this->addAnnouncementColumn('archived_by', function (Blueprint $table) {
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
        });

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'announcements_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('announcements_seen_at')->nullable();
            });
        }

        if (! Schema::hasTable('announcement_comments')) {
            Schema::create('announcement_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('body');
                $table->timestamps();
                $table->index(['announcement_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('announcement_polls')) {
            Schema::create('announcement_polls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->unique()->constrained('announcements')->cascadeOnDelete();
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
                $table->string('label', 150);
                $table->unsignedSmallInteger('position')->default(0);
                $table->timestamps();
                $table->index(['announcement_poll_id', 'position']);
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

    private function addAnnouncementColumn(string $column, callable $definition): void
    {
        if (Schema::hasTable('announcements') && ! Schema::hasColumn('announcements', $column)) {
            Schema::table('announcements', $definition);
        }
    }

    public function down(): void
    {
        // Repair migration intentionally keeps schema/data in place. Dropping
        // these columns or tables could destroy live announcements and votes.
    }
};
