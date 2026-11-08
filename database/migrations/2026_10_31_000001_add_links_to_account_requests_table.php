<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('account_requests', function (Blueprint $table) {
            // A child may be requested without a login, so the email is optional.
            $table->string('email')->nullable()->change();
            $table->foreignId('teacher_id')->nullable()->after('requested_by')->constrained('users')->nullOnDelete();
            $table->json('parent_ids')->nullable()->after('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('account_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_id');
            $table->dropColumn('parent_ids');
        });
    }
};
