<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
        {
            Schema::table('game_scores', function (Blueprint $table) {
                $table->unsignedSmallInteger('level')->default(1)->after('game_type');
                $table->index('level');
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_scores', function (Blueprint $table) {
            //
        });
    }
};
