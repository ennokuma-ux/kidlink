<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('game_type', ['letters', 'numbers', 'colors', 'colors-sort', 'whats-missing', 'shapes', 'animals', 'animals-memory', 'animals-match', 'shapes-bingo']);
            $table->integer('score');
            $table->integer('total');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_scores');
    }
};
