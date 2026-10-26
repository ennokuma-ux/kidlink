<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The old 60-indicator checklist was replaced by the official 109-indicator,
 * seven-domain list, so every stored row names indicators that no longer
 * exist. The rows are cleared; each child's checklist is rebuilt with the
 * new indicators the next time a teacher or parent opens the ECCD screen
 * for that child.
 *
 * There is no down(): the previous indicator data is not restorable.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('eccd_records')->delete();
    }

    public function down(): void
    {
        //
    }
};
