<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Academic year ("2026-2027") a child is enrolled in. Stored as a plain
     * string so filters and exports read it the same way everywhere. Existing
     * children are placed in the school year of their enrolment date, which
     * starts on 1 June (the Philippine school calendar).
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('academic_year', 9)->nullable()->after('class_name');
            $table->index('academic_year');
        });

        Schema::table('account_requests', function (Blueprint $table) {
            $table->string('academic_year', 9)->nullable()->after('class_name');
        });

        DB::table('students')->orderBy('id')->each(function ($row) {
            $date = $row->enrolled_date ?: $row->created_at;
            $when = $date ? \Carbon\Carbon::parse($date) : now();
            $start = $when->month >= 6 ? $when->year : $when->year - 1;

            DB::table('students')->where('id', $row->id)->update([
                'academic_year' => $start.'-'.($start + 1),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('account_requests', function (Blueprint $table) {
            $table->dropColumn('academic_year');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['academic_year']);
            $table->dropColumn('academic_year');
        });
    }
};
