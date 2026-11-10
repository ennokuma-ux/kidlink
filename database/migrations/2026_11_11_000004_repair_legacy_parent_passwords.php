<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair legacy parent credentials created before password hashing and
     * first-login enforcement were consistently applied.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'password')) {
            return;
        }

        $hasFirstLoginFlag = Schema::hasColumn('users', 'must_change_password');

        DB::table('users')
            ->where('role', 'parent')
            ->orderBy('id')
            ->chunkById(100, function ($parents) use ($hasFirstLoginFlag) {
                foreach ($parents as $parent) {
                    $storedPassword = (string) ($parent->password ?? '');
                    $passwordInfo = $storedPassword === ''
                        ? ['algo' => null]
                        : password_get_info($storedPassword);

                    // Keep valid hashes unchanged. Hash legacy plaintext values
                    // so existing credentials continue to work securely.
                    if ($storedPassword === '') {
                        $updates = ['password' => Hash::make('password123')];
                    } elseif (($passwordInfo['algo'] ?? null) === null) {
                        $updates = ['password' => Hash::make($storedPassword)];
                    } else {
                        $updates = [];
                    }

                    if ($hasFirstLoginFlag && ($storedPassword === '' || ($passwordInfo['algo'] ?? null) === null)) {
                        $updates['must_change_password'] = true;
                    }

                    if ($updates) {
                        DB::table('users')->where('id', $parent->id)->update($updates);
                    }
                }
            });
    }

    public function down(): void
    {
        // Credential repairs are intentionally irreversible; passwords remain hashed.
    }
};
