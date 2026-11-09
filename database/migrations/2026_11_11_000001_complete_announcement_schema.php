<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->text('body');
                $table->string('tag', 20)->nullable();
                $table->boolean('is_pinned')->default(false);
                $table->timestamp('pinned_at')->nullable();
                $table->timestamp('archived_at')->nullable()->index();
                $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        } else {
            $this->add('tag', fn (Blueprint $table) => $table->string('tag', 20)->nullable());
            $this->add('is_pinned', fn (Blueprint $table) => $table->boolean('is_pinned')->default(false));
            $this->add('pinned_at', fn (Blueprint $table) => $table->timestamp('pinned_at')->nullable());
            $this->add('archived_at', fn (Blueprint $table) => $table->timestamp('archived_at')->nullable());
            $this->add('archived_by', fn (Blueprint $table) => $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete());
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'announcements_seen_at')) {
            Schema::table('users', fn (Blueprint $table) => $table->timestamp('announcements_seen_at')->nullable());
        }
    }

    private function add(string $column, callable $definition): void
    {
        if (!Schema::hasColumn('announcements', $column)) {
            Schema::table('announcements', $definition);
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: announcements and read state are live data.
    }
};
