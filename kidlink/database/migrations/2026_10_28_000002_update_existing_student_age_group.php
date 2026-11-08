<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('students') && Schema::hasColumn('students', 'class_name')) {
            DB::table('students')
                ->where('class_name', '2-3 years old')
                ->update(['class_name' => '1-3 years old']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('students') && Schema::hasColumn('students', 'class_name')) {
            DB::table('students')
                ->where('class_name', '1-3 years old')
                ->update(['class_name' => '2-3 years old']);
        }
    }
};
