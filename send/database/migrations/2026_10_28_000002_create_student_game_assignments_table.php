<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_game_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->string('class_name')->nullable();
            $table->string('game_slug', 80);
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['student_id', 'game_slug']);
            $table->index(['teacher_id', 'class_name', 'game_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_game_assignments');
    }
};
