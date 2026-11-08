<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('account_requests', function (Blueprint $table) {
            $table->enum('sex', ['Female', 'Male'])->nullable()->after('contact');
            $table->date('date_of_birth')->nullable()->after('sex');
            $table->string('address')->nullable()->after('date_of_birth');
            $table->string('class_name')->nullable()->after('address');
            $table->string('dominant_hand')->nullable()->after('class_name');
            $table->string('father_name')->nullable()->after('dominant_hand');
            $table->string('father_contact')->nullable()->after('father_name');
            $table->string('mother_name')->nullable()->after('father_contact');
            $table->string('mother_contact')->nullable()->after('mother_name');
            $table->unsignedInteger('siblings_count')->nullable()->after('mother_contact');
        });
    }

    public function down(): void
    {
        Schema::table('account_requests', function (Blueprint $table) {
            $table->dropColumn([
                'sex', 'date_of_birth', 'address', 'class_name', 'dominant_hand',
                'father_name', 'father_contact', 'mother_name', 'mother_contact', 'siblings_count',
            ]);
        });
    }
};
