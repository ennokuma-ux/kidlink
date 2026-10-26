<?php

namespace App\Providers;

use App\Database\RetryingPostgresConnection;
use App\Models\Student;
use App\Support\Database\PdoDrivers;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PdoDrivers::class);
    }

    public function boot(): void
    {
        Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new RetryingPostgresConnection(
                $connection,
                $database,
                $prefix,
                $config,
                $this->app->make(PdoDrivers::class),
            );
        });

        View::composer('components.child_mode', function ($view) {
            $user = Auth::user();

            if (!$user) {
                $view->with('children', collect());
                return;
            }

            if ($user->role === 'parent') {
                $children = $user->children()->where('is_active', true)->get();
            } elseif ($user->role === 'teacher') {
                $children = Student::where('teacher_id', $user->id)->where('is_active', true)->get();
            } else {
                $children = collect();
            }

            $view->with('children', $children);
        });
    }
}
