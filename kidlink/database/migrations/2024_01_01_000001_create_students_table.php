<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('full_name')->nullable();
            $table->enum('sex', ['Female', 'Male']);
            $table->date('date_of_birth');
            $table->string('address')->nullable();
            $table->enum('dominant_hand', ['Right', 'Left', 'Both', 'Not yet established'])->default('Not yet established');
            $table->foreignId('parent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('enrolled_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('father_name')->nullable();
            $table->string('father_contact')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_contact')->nullable();
            $table->integer('siblings_count')->default(0);
            $table->string('class_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
