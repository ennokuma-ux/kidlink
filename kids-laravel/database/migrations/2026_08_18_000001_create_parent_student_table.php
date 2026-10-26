<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['parent_id', 'student_id']);
        });

        $rows = DB::table('students')->whereNotNull('parent_id')->get(['id', 'parent_id']);
        foreach ($rows as $row) {
            DB::table('parent_student')->insertOrIgnore([
                'parent_id' => $row->parent_id,
                'student_id' => $row->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_student');
    }
};
