<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // users
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'created_at'], 'idx_users_role_created');
        });

        // students
        Schema::table('students', function (Blueprint $table) {
            $table->index('parent_id', 'idx_students_parent_id');
            $table->index('teacher_id', 'idx_students_teacher_id');
        });

        // attendance
        Schema::table('attendance', function (Blueprint $table) {
            $table->index(['student_id', 'date'], 'idx_attendance_student_date');
        });

        // messages
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['receiver_id', 'is_read'], 'idx_messages_receiver_read');
            $table->index('sender_id', 'idx_messages_sender');
        });

        // activity_attendance
        Schema::table('activity_attendance', function (Blueprint $table) {
            $table->index('student_id', 'idx_activity_attendance_student');
        });

        // eccd_records
        Schema::table('eccd_records', function (Blueprint $table) {
            $table->index('student_id', 'idx_eccd_records_student');
        });

        // game_scores
        Schema::table('game_scores', function (Blueprint $table) {
            $table->index('student_id', 'idx_game_scores_student');
        });

        // parent_student
        Schema::table('parent_student', function (Blueprint $table) {
            $table->index('parent_id', 'idx_parent_student_parent');
            $table->index('student_id', 'idx_parent_student_student');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_created');
        });
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_parent_id');
            $table->dropIndex('idx_students_teacher_id');
        });
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropIndex('idx_attendance_student_date');
        });
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('idx_messages_receiver_read');
            $table->dropIndex('idx_messages_sender');
        });
        Schema::table('activity_attendance', function (Blueprint $table) {
            $table->dropIndex('idx_activity_attendance_student');
        });
        Schema::table('eccd_records', function (Blueprint $table) {
            $table->dropIndex('idx_eccd_records_student');
        });
        Schema::table('game_scores', function (Blueprint $table) {
            $table->dropIndex('idx_game_scores_student');
        });
        Schema::table('parent_student', function (Blueprint $table) {
            $table->dropIndex('idx_parent_student_parent');
            $table->dropIndex('idx_parent_student_student');
        });
    }
};
