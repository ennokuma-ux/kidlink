<?php

namespace App\Providers;

use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
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
