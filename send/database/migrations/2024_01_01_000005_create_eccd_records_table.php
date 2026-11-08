<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eccd_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('domain', ['Gross Motor', 'Fine Motor', 'Self-Help', 'Receptive Language', 'Expressive Language', 'Cognitive', 'Social-Emotional']);
            $table->text('indicator');
            $table->text('notes')->nullable();
            $table->enum('status', ['Not Yet', 'In Progress', 'Achieved'])->default('Not Yet');
            $table->foreignId('assessed_by')->constrained('users')->onDelete('cascade');
            $table->date('assessed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eccd_records');
    }
};
