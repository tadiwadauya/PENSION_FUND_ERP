<?php

use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
})->name('welcome');

Route::middleware('auth')->group(function () {
    Route::get('/force-change-password', [ForcePasswordChangeController::class, 'edit'])->name('password.force.change');
    Route::post('/force-change-password', [ForcePasswordChangeController::class, 'update'])->name('password.force.update');
});

Route::middleware(['auth', 'force.password.change'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['hr.access'])->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/deleted-users', [UserController::class, 'deleted'])->name('users.deleted');
        Route::post('/deleted-users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');

        Route::resource('departments', DepartmentController::class);
        Route::resource('sections', SectionController::class);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';