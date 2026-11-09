<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['students', 'account_requests'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'academic_year')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('academic_year', 60)->nullable();
                });
            }
        }

        if (Schema::hasTable('students') && Schema::hasColumn('students', 'academic_year')
            && ! Schema::hasIndex('students', ['academic_year'])) {
            Schema::table('students', function (Blueprint $table) {
                $table->index('academic_year');
            });
        }

        if (Schema::hasTable('students') && Schema::hasColumn('students', 'academic_year')
            && Schema::hasColumn('students', 'enrolled_date')) {
            DB::table('students')
                ->whereNull('academic_year')
                ->whereNotNull('enrolled_date')
                ->orderBy('id')
                ->select(['id', 'enrolled_date'])
                ->chunkById(200, function ($students) {
                    foreach ($students as $student) {
                        $date = \Carbon\Carbon::parse($student->enrolled_date);
                        $start = $date->month >= 6 ? $date->year : $date->year - 1;

                        DB::table('students')->where('id', $student->id)->update([
                            'academic_year' => $start.'-'.($start + 1),
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        // Kept intentionally: academic_year is shared by current application
        // code and a second historical migration with the same timestamp.
    }
};
