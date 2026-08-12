<?php


use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\Performance\PerformanceAssessment\PerformanceAssessmentController;

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
   Route::get('/performance-assessments', [PerformanceAssessmentController::class, 'index'])
    ->name('performance-assessments.index');

Route::post('/performance-targets/{performance_target}/start-assessment', [PerformanceAssessmentController::class, 'start'])
    ->name('performance-assessments.start');

Route::get('/performance-assessments/{performance_assessment}', [PerformanceAssessmentController::class, 'show'])
    ->name('performance-assessments.show');

Route::post('/performance-assessments/{performance_assessment}/save-self', [PerformanceAssessmentController::class, 'saveSelfAssessment'])
    ->name('performance-assessments.save-self');

Route::post('/performance-assessments/{performance_assessment}/submit-self', [PerformanceAssessmentController::class, 'submitSelfAssessment'])
    ->name('performance-assessments.submit-self');

Route::get('/performance-assessments/{performance_assessment}/assessor', [PerformanceAssessmentController::class, 'assessor'])
    ->name('performance-assessments.assessor');

Route::post('/performance-assessments/{performance_assessment}/assessor/save', [PerformanceAssessmentController::class, 'saveAssessorAssessment'])
    ->name('performance-assessments.assessor.save');

Route::post('/performance-assessments/{performance_assessment}/assessor/submit', [PerformanceAssessmentController::class, 'submitAssessorAssessment'])
    ->name('performance-assessments.assessor.submit');
Route::get('/performance-assessments/{performance_assessment}/print', [PerformanceAssessmentController::class, 'print'])
    ->name('performance-assessments.print');

Route::get('/performance-assessments/{performance_assessment}/reviewer', [PerformanceAssessmentController::class, 'reviewer'])
    ->name('performance-assessments.reviewer');

Route::post('/performance-assessments/{performance_assessment}/reviewer/save', [PerformanceAssessmentController::class, 'saveReviewerAssessment'])
    ->name('performance-assessments.reviewer.save');

Route::post('/performance-assessments/{performance_assessment}/reviewer/submit', [PerformanceAssessmentController::class, 'submitReviewerAssessment'])
    ->name('performance-assessments.reviewer.submit');

Route::post('/performance-assessments/{performance_assessment}/complete', [PerformanceAssessmentController::class, 'completeAssessment'])
    ->name('performance-assessments.complete');
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