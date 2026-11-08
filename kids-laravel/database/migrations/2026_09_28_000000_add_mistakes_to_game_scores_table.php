<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_scores', function (Blueprint $table) {
            $table->unsignedSmallInteger('mistakes')->nullable()->after('total');
        });

        DB::statement('UPDATE game_scores SET percentage = ROUND(100.0 * score / NULLIF(total, 0)) WHERE percentage IS NULL');
    }

    public function down(): void
    {
        Schema::table('game_scores', function (Blueprint $table) {
            $table->dropColumn('mistakes');
        });
    }
};
