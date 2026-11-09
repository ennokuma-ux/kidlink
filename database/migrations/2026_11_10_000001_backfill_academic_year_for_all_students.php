<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('students') || ! Schema::hasColumn('students', 'academic_year')) {
            return;
        }

        $currentStart = now()->month >= 6 ? now()->year : now()->year - 1;
        $currentAcademicYear = $currentStart.'-'.($currentStart + 1);

        // Existing records with an enrollment date are assigned to the school
        // year in which they enrolled. Records without one receive the current year.
        if (Schema::hasColumn('students', 'enrolled_date')) {
            DB::table('students')->whereNull('academic_year')->whereNotNull('enrolled_date')
                ->orderBy('id')->select(['id', 'enrolled_date'])->chunkById(200, function ($rows) {
                    foreach ($rows as $student) {
                        $date = \Carbon\Carbon::parse($student->enrolled_date);
                        $start = $date->month >= 6 ? $date->year : $date->year - 1;
                        DB::table('students')->where('id', $student->id)->whereNull('academic_year')
                            ->update(['academic_year' => $start.'-'.($start + 1)]);
                    }
                });
        }

        DB::table('students')->whereNull('academic_year')->update(['academic_year' => $currentAcademicYear]);
    }

    public function down(): void
    {
        // Intentionally retain assigned academic years to avoid erasing user data.
    }
};
