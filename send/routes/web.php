<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\AccountRequestController as AdminAccountRequestController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\ActivityController as TeacherActivityController;
  use App\Http\Controllers\AnnouncementController;
  use App\Http\Controllers\AnnouncementCommentController;
use App\Http\Controllers\Teacher\EccdController as TeacherEccdController;
use App\Http\Controllers\Teacher\SettingsController as TeacherSettingsController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Student\Games\ShapesGameController;
use App\Http\Controllers\Student\Games\ColorsGameController;
use App\Http\Controllers\Student\Games\ColorsSortGameController;
use App\Http\Controllers\Student\Games\LettersGameController;
use App\Http\Controllers\Student\Games\AnimalsGameController;

use App\Http\Controllers\Student\Games\AnimalsMatchGameController;
use App\Http\Controllers\Student\Games\ShapesBingoGameController;
use App\Http\Controllers\Student\Games\NumbersGameController;
use App\Http\Controllers\Student\Games\WhatsMissingGameController;
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

// Language preference. POST, because it writes session state, and it is open to
// guests so the landing and login screens can be switched too. Anything not in
// the configured list is ignored rather than reaching App::setLocale().
Route::post('/language', function (Illuminate\Http\Request $request) {
    $locale = $request->input('locale');

    if (! array_key_exists($locale, config('app.supported_locales', []))) {
        return back();
    }

    $request->session()->put('locale', $locale);

    // Mirrored into a long-lived cookie so the choice survives the session
    // ending; SetLocale reads the session first, so this is only the fallback.
    return redirect()->back()->withCookie(
        Illuminate\Support\Facades\Cookie::forever('locale', $locale)
    );
})->name('language.switch');

// Touches the session so a page left open on a desk does not go stale, and hands
// back the current CSRF token so long-lived pages can pick up a rotated one.
// Deliberately outside the auth/role groups: a guest session is worth refreshing
// too, and this must never 419 on its own.
Route::get('/session/keep-alive', function (Illuminate\Http\Request $request) {
    $request->session()->put('last_seen_at', time());

    return response()->json([
        'token' => csrf_token(),
        'expires_in' => config('session.lifetime') * 60,
    ]);
})->name('session.keep-alive');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        return match ($user->role) {
            'super_admin' => redirect()->route('admin.analytics.index'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'parent' => redirect()->route('parent.dashboard'),
            'student' => redirect()->route('student.home'),
            default => redirect('/'),
        };
    })->name('dashboard');

    // Bare role prefixes are shortcuts to that role's first page, so a stale
    // link or hand-typed URL lands on the home screen instead of an error page.
    Route::middleware('role:super_admin')->get('/admin', fn () => redirect()->route('admin.users.index'));
    Route::middleware('role:teacher')->get('/teacher', fn () => redirect()->route('teacher.dashboard'));
    Route::middleware('role:parent')->get('/parent', fn () => redirect()->route('parent.dashboard'));
    Route::middleware('role:student')->get('/student', fn () => redirect()->route('student.home'));
});

Route::middleware(['auth', 'active', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::put('/users/{user}/archive', [AdminUserController::class, 'archive'])->name('users.archive');

    // Accounts waiting for a super admin to approve or reject them.
    Route::get('/account-requests', [AdminAccountRequestController::class, 'index'])->name('account-requests.index');
    Route::post('/account-requests/{accountRequest}/approve', [AdminAccountRequestController::class, 'approve'])->name('account-requests.approve');
    Route::post('/account-requests/{accountRequest}/reject', [AdminAccountRequestController::class, 'reject'])->name('account-requests.reject');
});

Route::middleware(['auth', 'active', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/accounts', [TeacherStudentController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [TeacherStudentController::class, 'store'])->name('accounts.store');
    Route::put('/accounts/{student}', [TeacherStudentController::class, 'toggleActive'])->name('accounts.toggle');
    Route::put('/accounts/{student}/update', [TeacherStudentController::class, 'update'])->name('accounts.update');
    Route::put('/accounts/parent/{user}', [TeacherStudentController::class, 'toggleParentActive'])->name('accounts.parent.toggle');
    Route::post('/parents', [TeacherStudentController::class, 'storeParent'])->name('parents.store');
    Route::put('/parents/{user}', [TeacherStudentController::class, 'updateParent'])->name('parents.update');
    Route::post('/account-requests', [TeacherStudentController::class, 'storeAccountRequest'])->name('account-requests.store');
    Route::delete('/account-requests/{accountRequest}', [TeacherStudentController::class, 'cancelAccountRequest'])->name('account-requests.cancel');
    Route::get('/attendance', [TeacherAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [TeacherAttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/activities', [TeacherActivityController::class, 'index'])->name('activities.index');
    Route::post('/activities', [TeacherActivityController::class, 'store'])->name('activities.store');
    Route::get('/activities/{activity}/participation', [TeacherActivityController::class, 'participation'])->name('activities.participation');
    Route::put('/activities/{activity}/participation', [TeacherActivityController::class, 'saveParticipation'])->name('activities.participation.save');
    Route::put('/activities/{activity}', [TeacherActivityController::class, 'update'])->name('activities.update');
    Route::put('/activities/{activity}/toggle', [TeacherActivityController::class, 'toggleComplete'])->name('activities.toggle');
    Route::delete('/activities/{activity}', [TeacherActivityController::class, 'destroy'])->name('activities.destroy');
    Route::get('/eccd', [TeacherEccdController::class, 'index'])->name('eccd.index');
    Route::post('/eccd/update', [TeacherEccdController::class, 'update'])->name('eccd.update');
    // Analytics is folded into the dashboard; the old URL still serves it so
    // existing bookmarks and filtered links keep working.
    Route::get('/analytics', [TeacherDashboardController::class, 'index'])
        ->name('analytics.index');
    Route::get('/settings', [TeacherSettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [TeacherSettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/password', [TeacherSettingsController::class, 'updatePassword'])->name('settings.password');
});

// Legacy path kept so existing bookmarks and links still land on Accounts.
Route::middleware(['auth', 'role:teacher'])
    ->get('/teacher/students', fn () => redirect()->route('teacher.accounts.index'));

  Route::middleware(['auth', 'active', 'role:super_admin,admin,teacher,parent'])->prefix('announcements')->name('announcements.')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index'])->name('index');
    Route::post('/{announcement}/comments', [AnnouncementCommentController::class, 'store'])->name('comments.store');
    Route::delete('/{announcement}/comments/{comment}', [AnnouncementCommentController::class, 'destroy'])->name('comments.destroy');
  });

  // Posting is limited to admins and teachers. Parents can read but never write.
  Route::middleware(['auth', 'active', 'role:super_admin,admin,teacher'])->prefix('announcements')->name('announcements.')->group(function () {
    Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
    Route::post('/', [AnnouncementController::class, 'store'])->name('store');
    Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
  });

Route::middleware(['auth', 'active', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [ParentDashboardController::class, 'index'])->name('analytics.index');
    Route::get('/results', [ParentResultsController::class, 'index'])->name('results.index');
    Route::get('/milestones', [ParentMilestonesController::class, 'index'])->name('milestones.index');
    Route::get('/attendance', [ParentAttendanceController::class, 'index'])->name('attendance.index');
});

Route::middleware(['auth', 'active', 'role:parent,teacher,admin,super_admin'])->prefix('child-mode')->name('child-mode.')->group(function () {
    Route::get('/accounts', [ChildModeController::class, 'accounts'])->name('accounts');
    Route::post('/enter/{student}', [ChildModeController::class, 'enter'])->name('enter');
});

Route::middleware(['auth'])->prefix('child-mode')->name('child-mode.')->group(function () {
    Route::post('/exit', [ChildModeController::class, 'exit'])->name('exit');
    Route::post('/set', [ChildModeController::class, 'set'])->name('set');
});

Route::middleware(['auth', 'active', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/home', [StudentHomeController::class, 'index'])->name('home');
    Route::get('/stats', [StudentHomeController::class, 'stats'])->name('stats');
    Route::get('/game/{type}', [StudentGameController::class, 'show'])->name('game.show');
    Route::post('/save-score', [StudentScoreController::class, 'save'])->name('score.save');

    // Shapes Game
    Route::prefix('games/shapes')->name('games.shapes.')->group(function () {
        Route::get('/', [ShapesGameController::class, 'stageSelect'])->name('stage-select');
        Route::get('/play/{level}', [ShapesGameController::class, 'play'])->name('play');
        Route::post('/submit/{level}', [ShapesGameController::class, 'submit'])->name('submit');
    });

    // Colors Game
    Route::prefix('games/colors')->name('games.colors.')->group(function () {
        Route::get('/', [ColorsGameController::class, 'stageSelect'])->name('stage-select');
        Route::get('/play/{level}', [ColorsGameController::class, 'play'])->name('play');
        Route::post('/submit/{level}', [ColorsGameController::class, 'submit'])->name('submit');
    });

    // Color Sort Game
    Route::prefix('games/colors-sort')->name('games.colors-sort.')->group(function () {
        Route::get('/', [ColorsSortGameController::class, 'stageSelect'])->name('stage-select');
        Route::get('/play/{level}', [ColorsSortGameController::class, 'play'])->name('play');
        Route::post('/submit/{level}', [ColorsSortGameController::class, 'submit'])->name('submit');
    });

    // Letters Game
    Route::prefix('games/letters')->name('games.letters.')->group(function () {
        Route::get('/', [LettersGameController::class, 'stageSelect'])->name('stage-select');
        Route::get('/play/{level}', [LettersGameController::class, 'play'])->name('play');
        Route::post('/submit/{level}', [LettersGameController::class, 'submit'])->name('submit');
    });

    // Animals Game
    Route::prefix('games/animals')->name('games.animals.')->group(function () {
        Route::get('/', [AnimalsGameController::class, 'stageSelect'])->name('stage-select');
        Route::get('/play/{level}', [AnimalsGameController::class, 'play'])->name('play');
        Route::post('/submit/{level}', [AnimalsGameController::class, 'submit'])->name('submit');
    });

    // Animals Match Game
    Route::prefix('games/animals-match')->name('games.animals-match.')->group(function () {
        Route::get('/', [AnimalsMatchGameController::class, 'stageSelect'])->name('stage-select');
        Route::get('/play/{level}', [AnimalsMatchGameController::class, 'play'])->name('play');
        Route::post('/submit/{level}', [AnimalsMatchGameController::class, 'submit'])->name('submit');
    });

    // Shape Bingo Game
    Route::prefix('games/shapes-bingo')->name('games.shapes-bingo.')->group(function () {
        Route::get('/', [ShapesBingoGameController::class, 'stageSelect'])->name('stage-select');
        Route::get('/play/{level}', [ShapesBingoGameController::class, 'play'])->name('play');
        Route::post('/submit/{level}', [ShapesBingoGameController::class, 'submit'])->name('submit');
    });

    // Numbers Game
    Route::prefix('games/numbers')->name('games.numbers.')->group(function () {
        Route::get('/', [NumbersGameController::class, 'stageSelect'])->name('stage-select');
        Route::get('/play/{level}', [NumbersGameController::class, 'play'])->name('play');
        Route::post('/submit/{level}', [NumbersGameController::class, 'submit'])->name('submit');
    });

    // What's Missing? Game
    Route::prefix('games/whats-missing')->name('games.whats-missing.')->group(function () {
        Route::get('/', [WhatsMissingGameController::class, 'stageSelect'])->name('stage-select');
        Route::get('/play/{level}', [WhatsMissingGameController::class, 'play'])->name('play');
        Route::post('/submit/{level}', [WhatsMissingGameController::class, 'submit'])->name('submit');
    });
});
