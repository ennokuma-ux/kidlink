<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_page_locks')) {
            Schema::create('user_page_locks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('page_key', 100);
                $table->string('lock_token', 100);
                $table->timestamp('last_seen_at');
                $table->timestamp('expires_at')->index();
                $table->timestamps();
                $table->unique(['user_id', 'page_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_page_locks');
    }
};
