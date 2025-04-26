<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\GroupMemberController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Groupes
    Route::resource('groups', GroupController::class);
    Route::post('groups/{group}/members/add', [GroupController::class, 'addMember'])->name('groups.members.add');
    Route::delete('groups/{group}/members/remove', [GroupController::class, 'removeMember'])->name('groups.members.remove');
    Route::put('groups/{group}/members/{user}/update-role', [GroupController::class, 'updateMemberRole'])->name('groups.members.update-role');

    // Utilisateurs
    Route::get('users/search', [UserController::class, 'search'])->name('users.search');

    // Saisons
    Route::get('groups/{group}/seasons/create', [SeasonController::class, 'create'])->name('seasons.create');
    Route::post('groups/{group}/seasons', [SeasonController::class, 'store'])->name('seasons.store');
    Route::get('groups/{group}/seasons/{season}', [SeasonController::class, 'show'])->name('seasons.show');
    Route::get('groups/{group}/seasons/{season}/edit', [SeasonController::class, 'edit'])->name('seasons.edit');
    Route::put('groups/{group}/seasons/{season}', [SeasonController::class, 'update'])->name('seasons.update');
    Route::delete('groups/{group}/seasons/{season}', [SeasonController::class, 'destroy'])->name('seasons.destroy');
    Route::post('groups/{group}/seasons/{season}/start', [SeasonController::class, 'start'])->name('seasons.start');
    Route::post('groups/{group}/seasons/{season}/complete', [SeasonController::class, 'complete'])->name('seasons.complete');

    // Défis
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('groups/{group}/seasons/{season}/challenges', [ChallengeController::class, 'index'])->name('challenges.index');
        Route::get('groups/{group}/seasons/{season}/challenges/create', [ChallengeController::class, 'create'])->name('challenges.create');
        Route::post('groups/{group}/seasons/{season}/challenges', [ChallengeController::class, 'store'])->name('challenges.store');
        Route::get('groups/{group}/seasons/{season}/challenges/{challenge}', [ChallengeController::class, 'show'])->name('challenges.show');
        Route::get('groups/{group}/seasons/{season}/challenges/{challenge}/edit', [ChallengeController::class, 'edit'])->name('challenges.edit');
        Route::put('groups/{group}/seasons/{season}/challenges/{challenge}', [ChallengeController::class, 'update'])->name('challenges.update');
        Route::delete('groups/{group}/seasons/{season}/challenges/{challenge}', [ChallengeController::class, 'destroy'])->name('challenges.destroy');
        Route::get('groups/{group}/seasons/{season}/members/{member}/challenges', [ChallengeController::class, 'index'])->name('challenges.member');
        Route::post('groups/{group}/seasons/{season}/challenges/{challenge}/mark-failed', [ChallengeController::class, 'markAsFailed'])->name('challenges.mark-failed');
    });
});
