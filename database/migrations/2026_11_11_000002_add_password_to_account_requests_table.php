<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('account_requests', 'password')) {
            Schema::table('account_requests', function (Blueprint $table) {
                // Store the already-hashed optional parent password until approval.
                $table->string('password')->nullable()->after('email');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('account_requests', 'password')) {
            Schema::table('account_requests', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }
    }
};
