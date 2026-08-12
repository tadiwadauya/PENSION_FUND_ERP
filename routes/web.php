<?php

use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Performance\PerformanceTarget\PerformancePeriodController;
use App\Http\Controllers\Performance\PerformanceTarget\PerformanceTargetApprovalController;
use App\Http\Controllers\Performance\PerformanceTarget\PerformanceTargetController;
use App\Http\Controllers\Performance\PerformanceRatingScaleController;
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

    /*
    |--------------------------------------------------------------------------
    | HR / Admin only
    |--------------------------------------------------------------------------
    */
    Route::middleware(['hr.access'])->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/deleted-users', [UserController::class, 'deleted'])->name('users.deleted');
        Route::post('/deleted-users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');

        Route::resource('departments', DepartmentController::class);
        Route::resource('sections', SectionController::class);

        // Only HR or Admin can manage performance periods
        Route::resource('performance-target-periods', PerformancePeriodController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Performance Targets - all logged in users
    |--------------------------------------------------------------------------
    */


    Route::resource('performance-targets', PerformanceTargetController::class);

    Route::post('performance-targets/{performance_target}/submit', [PerformanceTargetApprovalController::class, 'submit'])
        ->name('performance-targets.submit');

    Route::post('performance-targets/{performance_target}/approve', [PerformanceTargetApprovalController::class, 'approve'])
        ->name('performance-targets.approve');

    Route::post('performance-targets/{performance_target}/reject', [PerformanceTargetApprovalController::class, 'reject'])
        ->name('performance-targets.reject');

    Route::post('performance-targets/{performance_target}/hr-review', [PerformanceTargetApprovalController::class, 'hrReview'])
        ->name('performance-targets.hr-review');

    Route::get('performance-targets/{performance_target}/print', [PerformanceTargetController::class, 'print'])
        ->name('performance-targets.print');



    /*
    peformance ratings
    */
    Route::middleware(['auth'])->group(function () {
    Route::resource('performance-rating-scales', PerformanceRatingScaleController::class);
});

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';