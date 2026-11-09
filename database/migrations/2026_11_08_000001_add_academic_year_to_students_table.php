<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        // The other migration with this timestamp may already have created
        // this index. Check before adding it so PostgreSQL doesn't reject a
        // duplicate index during a fresh or partially-applied deployment.
        if (Schema::hasTable('students') && Schema::hasColumn('students', 'academic_year')
            && ! Schema::hasIndex('students', ['academic_year'])) {
            Schema::table('students', function (Blueprint $table) {
                $table->index('academic_year');
            });
        }
    }

    public function down(): void
    {
        // Kept intentionally: academic_year is shared by current application
        // code and a second historical migration with the same timestamp.
    }
};
