<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameRouletteController;
use App\Http\Controllers\GameSessionController;
use App\Http\Controllers\GameSuggestionController;
use App\Http\Controllers\SessionCompletionController;
use App\Http\Controllers\SuggestionVoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('sessions', GameSessionController::class);
    Route::put('/sessions/{session}/attendance', [AttendanceController::class, 'update'])
        ->name('sessions.attendance.update');
    Route::post('/sessions/{session}/roulette', GameRouletteController::class)
        ->name('sessions.roulette');
    Route::get('/sessions/{session}/complete', [SessionCompletionController::class, 'create'])
        ->name('sessions.complete.create');
    Route::post('/sessions/{session}/complete', [SessionCompletionController::class, 'store'])
        ->name('sessions.complete.store');

    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::get('/games/{igdbId}', [GameController::class, 'show'])->whereNumber('igdbId')->name('games.show');
    Route::post('/games/{igdbId}/suggestions', [GameSuggestionController::class, 'store'])
        ->whereNumber('igdbId')
        ->name('games.suggestions.store');
    Route::delete('/suggestions/{suggestion}', [GameSuggestionController::class, 'destroy'])
        ->name('suggestions.destroy');
    Route::put('/suggestions/{suggestion}/vote', [SuggestionVoteController::class, 'update'])
        ->name('suggestions.vote');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
