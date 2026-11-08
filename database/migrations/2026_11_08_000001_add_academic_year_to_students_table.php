<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fixes: SQLSTATE[42703] column "academic_year" does not exist.
     *
     * The school year a child is enrolled for, stored as "2025-2026" (see
     * App\Support\AcademicYear: 2025, 25-26 or 2025/2026 are tidied to that shape,
     * and a custom label such as "Summer 2026" is kept as typed). Guarded so it is safe to run on a
     * database where the column was already added by hand.
     */
    public function up(): void
    {
        foreach (['students', 'account_requests'] as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'academic_year')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->string('academic_year', 60)->nullable();
            });
        }

        Schema::table('students', function (Blueprint $table) {
            $table->index('academic_year');
        });

        // Existing children get the school year they enrolled in, so a year
        // filter does not start out hiding the whole roll. The Philippine
        // school year starts in June, which App\Support\AcademicYear mirrors.
        DB::table('students')
            ->whereNull('academic_year')
            ->whereNotNull('enrolled_date')
            ->orderBy('id')
            ->select(['id', 'enrolled_date'])
            ->chunkById(200, function ($students) {
                foreach ($students as $student) {
                    $date = \Carbon\Carbon::parse($student->enrolled_date);
                    $start = $date->month >= \App\Support\AcademicYear::START_MONTH ? $date->year : $date->year - 1;

                    DB::table('students')->where('id', $student->id)
                        ->update(['academic_year' => $start.'-'.($start + 1)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['academic_year']);
        });

        foreach (['students', 'account_requests'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'academic_year')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('academic_year');
                });
            }
        }
    }
};
