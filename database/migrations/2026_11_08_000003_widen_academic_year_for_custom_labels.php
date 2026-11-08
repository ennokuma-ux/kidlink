<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Academic years can be custom labels ("Summer 2026"), so the column holds
     * up to 60 characters. Only needed where the first migration already ran
     * with the older, shorter column; harmless otherwise.
     */
    public function up(): void
    {
        foreach (['students', 'account_requests'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'academic_year')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('academic_year', 60)->nullable()->change();
                });
            }
        }
    }

    public function down(): void
    {
        // Left wide: shrinking could cut off saved labels.
    }
};
