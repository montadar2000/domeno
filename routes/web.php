<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\ScoreController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'createRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/', [MatchController::class, 'index'])->name('home');
    Route::get('/history', [MatchController::class, 'history'])->name('history');

    Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
    Route::patch('/matches/{match}', [MatchController::class, 'update'])->name('matches.update');
    Route::post('/matches/{match}/close', [MatchController::class, 'close'])->name('matches.close');

    Route::post('/matches/{match}/scores', [ScoreController::class, 'store'])->name('scores.store');
    Route::delete('/matches/{match}/scores/latest', [ScoreController::class, 'destroyLatest'])->name('scores.undo');
    Route::post('/matches/{match}/rounds', [ScoreController::class, 'storeRound'])->name('rounds.store');
});
