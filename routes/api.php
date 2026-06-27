<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\MaintenancePlanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SocietyController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UnitTypeController;
use App\Http\Controllers\UserRoleController;
//watchman module
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WatchmanController;
use App\Http\Controllers\WatchmanAuthController;
use App\Http\Controllers\WatchmanTaskController;
use App\Http\Controllers\WatchmanMaidController;
use App\Http\Controllers\WatchmanNotificationController;
use App\Http\Controllers\WatchmanVisitorController;
use App\Http\Controllers\VisitorController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/societies', [SocietyController::class, 'index']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Password reset (forgot) flow
Route::post('/password/forgot', [AuthController::class, 'sendResetOtp']);
Route::post('/password/verify', [AuthController::class, 'verifyResetOtp']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/units', [UnitController::class, 'index']);
    Route::post('/units', [UnitController::class, 'store']);
    Route::patch('/units/{unit}', [UnitController::class, 'update']);
    Route::delete('/units/{unit}', [UnitController::class, 'destroy']);
    Route::post('/units/{unit}/members', [UnitController::class, 'addMember']);
    Route::get('/unit-types', [UnitTypeController::class, 'index']);
    Route::post('/unit-types', [UnitTypeController::class, 'store']);
    Route::patch('/unit-types/{unitType}', [UnitTypeController::class, 'update']);
    Route::delete('/unit-types/{unitType}', [UnitTypeController::class, 'destroy']);
    Route::get('/billing-cycles', [BillingController::class, 'indexCycles']);
    Route::post('/billing-cycles', [BillingController::class, 'storeCycle']);
    Route::post('/billing-cycles/{billingCycle}/generate-bills', [BillingController::class, 'generateBills']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);
    Route::get('/my-due-summary', [SummaryController::class, 'myDueSummary']);
    Route::get('/society-maintenance-summary', [SummaryController::class, 'societyMaintenanceSummary']);
    Route::get('/income-expense-summary', [FinanceController::class, 'summary']);
    Route::get('/finance/report', [FinanceController::class, 'report']);
    Route::get('/income-categories', [FinanceController::class, 'incomeCategories']);
    Route::post('/income-categories', [FinanceController::class, 'storeIncomeCategory']);
    Route::patch('/income-categories/{incomeCategory}', [FinanceController::class, 'updateIncomeCategory']);
    Route::delete('/income-categories/{incomeCategory}', [FinanceController::class, 'destroyIncomeCategory']);
    Route::get('/expense-categories', [FinanceController::class, 'expenseCategories']);
    Route::post('/expense-categories', [FinanceController::class, 'storeExpenseCategory']);
    Route::patch('/expense-categories/{expenseCategory}', [FinanceController::class, 'updateExpenseCategory']);
    Route::delete('/expense-categories/{expenseCategory}', [FinanceController::class, 'destroyExpenseCategory']);
    Route::get('/vendors', [FinanceController::class, 'vendors']);
    Route::post('/income-entries', [FinanceController::class, 'storeIncome']);
    Route::patch('/income-entries/{incomeEntry}', [FinanceController::class, 'updateIncome']);
    Route::delete('/income-entries/{incomeEntry}', [FinanceController::class, 'destroyIncome']);
    Route::post('/expense-entries', [FinanceController::class, 'storeExpense']);
    Route::patch('/expense-entries/{expenseEntry}', [FinanceController::class, 'updateExpense']);
    Route::delete('/expense-entries/{expenseEntry}', [FinanceController::class, 'destroyExpense']);
    Route::get('/maintenance-plan', [MaintenancePlanController::class, 'index']);
    Route::post('/maintenance-plan', [MaintenancePlanController::class, 'store']);
    Route::get('/society-users', [UserRoleController::class, 'index']);
    Route::patch('/society-users/{user}/role', [UserRoleController::class, 'updateRole']);
    Route::get('/units/{unit}/due-summary', [SummaryController::class, 'unitDueSummary']);
    Route::get('/units/{unit}/payments', [SummaryController::class, 'unitPayments']);
    Route::get('/my-ledger', [LedgerController::class, 'myLedger']);
    Route::get('/units/{unit}/ledger', [LedgerController::class, 'unitLedger']);

    // Complaints
    Route::get('/complaints', [ComplaintController::class, 'index']);
    Route::post('/complaints', [ComplaintController::class, 'store']);
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show']);
    Route::patch('/complaints/{complaint}', [ComplaintController::class, 'update']);
    Route::post('/complaints/{complaint}/close', [ComplaintController::class, 'close']);
    Route::post('/complaints/{complaint}/rate', [ComplaintController::class, 'rate']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/fcm-token', [NotificationController::class, 'saveFcmToken']);

    // Watchmen & Visitors
    Route::get('/watchmen', [WatchmanController::class, 'index']);
    Route::post('/watchmen', [WatchmanController::class, 'store']);

    // Task management (admin)
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::patch('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    Route::post('/tasks/{task}/assign', [TaskController::class, 'assign']);
    Route::delete('/tasks/{task}/watchmen/{watchmanId}', [TaskController::class, 'unassign']);
    //Tasks Added Sameer
    Route::get('/visitors', [VisitorController::class, 'index']);
    Route::get('/visitors/my-unit', [VisitorController::class, 'myUnit']);
    Route::post('/visitors', [VisitorController::class, 'store']);
    Route::post('/visitors/{visitor}/checkout', [VisitorController::class, 'checkout']);

    // Temporary test route — remove after confirming push works
    Route::post('/notifications/test-push', function (Request $request) {
        \App\Services\NotificationService::notify(
            $request->user()->id,
            'Test Notification 🎉',
            'Push notifications are working!',
            'general'
        );
        return response()->json(['message' => 'Notification sent']);
    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Watchman auth + visitor CRUD (Sanctum tokens, watchman-only guard)
Route::prefix('watchman')->group(function () {
    Route::post('/login', [WatchmanAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'watchman.only'])->group(function () {
        Route::get('/me', [WatchmanAuthController::class, 'me']);
        Route::post('/logout', [WatchmanAuthController::class, 'logout']);
        Route::post('/change-password', [WatchmanAuthController::class, 'changePassword']);

        // Task management (watchman)
        Route::get('/tasks', [WatchmanTaskController::class, 'index']);
        Route::post('/tasks/{task}/complete', [WatchmanTaskController::class, 'complete']);
        Route::post('/tasks/{task}/uncomplete', [WatchmanTaskController::class, 'uncomplete']);
        Route::get('/tasks/{task}/logs', [WatchmanTaskController::class, 'logs']);

        // Maid attendance
        Route::get('/maids', [WatchmanMaidController::class, 'maids']);
        Route::get('/maid-logs', [WatchmanMaidController::class, 'index']);
        Route::post('/maid-logs/enter', [WatchmanMaidController::class, 'enter']);
        Route::post('/maid-logs/{log}/exit', [WatchmanMaidController::class, 'exit']);
        Route::get('/maid-logs/{log}', [WatchmanMaidController::class, 'show']);
        Route::post('/maid-logs/test-notification', [WatchmanMaidController::class, 'testNotification']);

        // Notifications (watchman)
        Route::get('/notifications', [WatchmanNotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [WatchmanNotificationController::class, 'unreadCount']);
        Route::patch('/notifications/read-all', [WatchmanNotificationController::class, 'markAllRead']);
        Route::patch('/notifications/{notification}/read', [WatchmanNotificationController::class, 'markRead']);
        Route::post('/notifications/fcm-token', [WatchmanNotificationController::class, 'saveFcmToken']);

        // Visitor management
        Route::get('/visitors', [WatchmanVisitorController::class, 'index']);
        Route::get('/visitors/get-unit', [WatchmanVisitorController::class, 'getUnit']);
        Route::get('/visitors/get-wings', [WatchmanVisitorController::class, 'getWings']);
        Route::get('/visitors/get-units-by-wing', [WatchmanVisitorController::class, 'getUnitsByWing']);
        Route::post('/visitors', [WatchmanVisitorController::class, 'store']);
        Route::get('/visitors/{visitor}', [WatchmanVisitorController::class, 'show']);
        Route::post('/visitors/{visitor}/update', [WatchmanVisitorController::class, 'update']);
        Route::post('/visitors/{visitor}/checkout', [WatchmanVisitorController::class, 'checkout']);
    });
});
