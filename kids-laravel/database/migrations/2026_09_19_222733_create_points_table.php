<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('game_type')->index();
            $table->unsignedSmallInteger('level')->default(1);
            $table->integer('points_earned')->default(0);
            $table->integer('total_points')->default(0);
            $table->string('source')->index(); // correct_answer, level_complete, streak_bonus, level_complete, perfect_game
            $table->json('metadata')->nullable(); // {streak: 3, correct_streak: 5, etc}
            $table->timestamps();
            
            $table->index(['student_id', 'game_type']);
            $table->index(['student_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points');
    }
};
