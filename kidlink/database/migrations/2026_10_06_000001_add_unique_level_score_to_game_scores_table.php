<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A stage can only be finished once, so it can only ever be recorded
        // once. This is the backstop: application code checks the same thing,
        // but two requests landing at the same moment would both pass that check.
        $duplicates = DB::select(
            'select student_id, game_type, level
               from game_scores
              group by student_id, game_type, level
             having count(*) > 1'
        );

        if ($duplicates !== []) {
            // Keep the best attempt of each duplicated stage and drop the rest,
            // otherwise the index cannot be created.
            DB::statement(
                'delete from game_scores
                  where id in (
                      select id from (
                          select id,
                                 row_number() over (
                                     partition by student_id, game_type, level
                                     order by percentage desc, score desc, id asc
                                 ) as keep_rank
                            from game_scores
                      ) ranked
                      where ranked.keep_rank > 1
                  )'
            );
        }

        Schema::table('game_scores', function (Blueprint $table) {
            $table->unique(['student_id', 'game_type', 'level'], 'game_scores_student_game_level_unique');
        });
    }

    public function down(): void
    {
        Schema::table('game_scores', function (Blueprint $table) {
            $table->dropUnique('game_scores_student_game_level_unique');
        });
    }
};