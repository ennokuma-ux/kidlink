<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\ActivityController as TeacherActivityController;
use App\Http\Controllers\Teacher\EccdController as TeacherEccdController;
use App\Http\Controllers\Teacher\AnalyticsController as TeacherAnalyticsController;
use App\Http\Controllers\Teacher\SettingsController as TeacherSettingsController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Parent\ProgressController as ParentProgressController;
use App\Http\Controllers\Parent\ResultsController as ParentResultsController;
use App\Http\Controllers\Parent\MilestonesController as ParentMilestonesController;
use App\Http\Controllers\Parent\AttendanceController as ParentAttendanceController;
use App\Http\Controllers\Student\HomeController as StudentHomeController;
use App\Http\Controllers\Student\GameController as StudentGameController;
use App\Http\Controllers\Student\ScoreController as StudentScoreController;
use App\Http\Controllers\ChildModeController;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        return match ($user->role) {
            'super_admin' => redirect()->route('admin.users.index'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'parent' => redirect()->route('parent.dashboard'),
            'student' => redirect()->route('student.home'),
            default => redirect('/'),
        };
    })->name('dashboard');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/students', [TeacherStudentController::class, 'index'])->name('students.index');
    Route::post('/students', [TeacherStudentController::class, 'store'])->name('students.store');
    Route::put('/students/{student}', [TeacherStudentController::class, 'toggleActive'])->name('students.toggle');
    Route::put('/students/{student}/update', [TeacherStudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [TeacherStudentController::class, 'destroy'])->name('students.destroy');
    Route::get('/attendance', [TeacherAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [TeacherAttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/activities', [TeacherActivityController::class, 'index'])->name('activities.index');
    Route::post('/activities', [TeacherActivityController::class, 'store'])->name('activities.store');
    Route::put('/activities/{activity}', [TeacherActivityController::class, 'toggleComplete'])->name('activities.toggle');
    Route::delete('/activities/{activity}', [TeacherActivityController::class, 'destroy'])->name('activities.destroy');
    Route::get('/eccd', [TeacherEccdController::class, 'index'])->name('eccd.index');
    Route::post('/eccd/update', [TeacherEccdController::class, 'update'])->name('eccd.update');
    Route::get('/analytics', [TeacherAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/settings', [TeacherSettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [TeacherSettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/password', [TeacherSettingsController::class, 'updatePassword'])->name('settings.password');
});

Route::middleware(['auth', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/progress', [ParentProgressController::class, 'index'])->name('progress.index');
    Route::get('/results', [ParentResultsController::class, 'index'])->name('results.index');
    Route::get('/milestones', [ParentMilestonesController::class, 'index'])->name('milestones.index');
    Route::get('/attendance', [ParentAttendanceController::class, 'index'])->name('attendance.index');
});

Route::middleware(['auth', 'role:parent,teacher'])->prefix('child-mode')->name('child-mode.')->group(function () {
    Route::post('/enter/{student}', [ChildModeController::class, 'enter'])->name('enter');
});

Route::middleware(['auth'])->prefix('child-mode')->name('child-mode.')->group(function () {
    Route::post('/exit', [ChildModeController::class, 'exit'])->name('exit');
    Route::post('/set', [ChildModeController::class, 'set'])->name('set');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/home', [StudentHomeController::class, 'index'])->name('home');
    Route::get('/game/{type}', [StudentGameController::class, 'show'])->name('game.show');
    Route::post('/save-score', [StudentScoreController::class, 'save'])->name('score.save');
});
