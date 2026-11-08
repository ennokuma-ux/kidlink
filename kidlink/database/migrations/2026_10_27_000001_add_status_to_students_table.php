<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A student's lifecycle status: active, archived, graduated or dropped.
     * `is_active` stays as the single switch every existing query uses and is
     * kept in step by Student::applyStatus() (true only when status = active).
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('is_active');
            $table->index('status');
        });

        DB::table('students')->where('is_active', false)->update(['status' => 'archived']);
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
