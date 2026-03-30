<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\GameAttemptsController;
use App\Http\Controllers\StatsController;
use App\Http\Middleware\EnsureUserIsApproved;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/approval-pending', function () {
        if (auth()->user()->isApproved()) {
            return redirect()->route('home');
        }

        return Inertia::render('Auth/ApprovalPending');
    })->name('approval.pending');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::middleware(EnsureUserIsApproved::class)->group(function () {
        Route::get('/', function () {
            return Inertia::render('Game');
        })->name('home');

        Route::post('/attempts', [GameAttemptsController::class, 'store'])->name('attempts.store');
        Route::get('/stats', StatsController::class)->name('stats');
    });
});
