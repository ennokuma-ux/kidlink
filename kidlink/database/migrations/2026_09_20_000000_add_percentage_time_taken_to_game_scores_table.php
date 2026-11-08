<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_scores', function (Blueprint $table) {
            $table->unsignedInteger('percentage')->nullable()->after('total');
            $table->unsignedInteger('time_taken')->nullable()->after('percentage');
        });
    }

    public function down(): void
    {
        Schema::table('game_scores', function (Blueprint $table) {
            $table->dropColumn(['percentage', 'time_taken']);
        });
    }
};