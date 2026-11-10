<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eccd_records', function (Blueprint $table) {
            $table->string('age_group', 3)->default('3-5')->after('student_id');
            $table->string('age_band')->nullable()->after('indicator');
            $table->index(['student_id', 'assessed_by', 'age_group'], 'eccd_records_age_group_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('eccd_records', function (Blueprint $table) {
            $table->dropIndex('eccd_records_age_group_lookup');
            $table->dropColumn(['age_group', 'age_band']);
        });
    }
};
