<?php

//web admin
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminWatchmanController;
use App\Http\Controllers\AdminTaskController;
use App\Http\Controllers\AdminTaskReportController;
use App\Http\Controllers\AdminWatchmanTaskController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminTaskAnalysisController;
use App\Http\Controllers\AdminUnitController;
use App\Http\Controllers\AdminUnitTypeController;
use App\Http\Controllers\AdminSocietySummaryController;
use App\Http\Controllers\AdminWingController;
use App\Http\Controllers\AdminVisitorLogController;
use App\Http\Controllers\AdminMaidController;
use App\Http\Controllers\AdminMaidLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
});
// Authenticated, non-owner routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('watchman', AdminWatchmanController::class);
    Route::resource('unit', AdminUnitController::class);
    Route::resource('unit-type', AdminUnitTypeController::class);
    Route::resource('wing', AdminWingController::class);
    Route::get('society-summary', [AdminSocietySummaryController::class, 'index'])->name('society-summary.index');
    Route::post('unit/{unit}/members',              [AdminUnitController::class, 'addMember'])->name('unit.member.add');
    Route::patch('unit/{unit}/members/{member}',    [AdminUnitController::class, 'updateMember'])->name('unit.member.update');
    Route::delete('unit/{unit}/members/{member}',   [AdminUnitController::class, 'removeMember'])->name('unit.member.remove');
    Route::get('visitor/log',    [AdminVisitorLogController::class,   'index'])->name('visitor.log');
    Route::get('maid/log',       [AdminMaidLogController::class,      'index'])->name('maid.log');
    Route::resource('maid', AdminMaidController::class);
    Route::post('maid/{maid}/assign-unit',           [AdminMaidController::class, 'assignUnit'])->name('maid.unit.assign');
    Route::patch('maid-unit/{assignment}/toggle',    [AdminMaidController::class, 'toggleAssignment'])->name('maid.unit.toggle');
    Route::patch('maid-unit/{assignment}/dates',     [AdminMaidController::class, 'updateAssignment'])->name('maid.unit.update');
    Route::delete('maid-unit/{assignment}',          [AdminMaidController::class, 'removeAssignment'])->name('maid.unit.remove');
    Route::get('task/report',    [AdminTaskReportController::class,   'index'])->name('task.report');
    Route::get('task/analysis',  [AdminTaskAnalysisController::class, 'index'])->name('task.analysis');
    Route::get('task/log',       [AdminTaskAnalysisController::class, 'dailyLog'])->name('task.log');
    Route::resource('task', AdminTaskController::class);

    // Task–Watchman assignments
    Route::post('task/{task}/assign',                    [AdminWatchmanTaskController::class, 'store'])->name('watchman-task.store');
    Route::post('watchman/{watchman}/assign-task',       [AdminWatchmanTaskController::class, 'storeForWatchman'])->name('watchman-task.store-for-watchman');
    Route::patch('watchman-task/{watchmanTask}/toggle',  [AdminWatchmanTaskController::class, 'update'])->name('watchman-task.update');
    Route::delete('watchman-task/{watchmanTask}',        [AdminWatchmanTaskController::class, 'destroy'])->name('watchman-task.destroy');
});

Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout')->middleware('auth');
