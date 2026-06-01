<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AdminCompanyController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminPlanController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ClientRelationController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::middleware(['auth:sanctum', 'super.admin'])->prefix('admin')->group(function () {
    Route::get('dashboard', AdminDashboardController::class);
    Route::apiResource('companies', AdminCompanyController::class);
    Route::patch('companies/{company}/activate', [AdminCompanyController::class, 'activate']);
    Route::patch('companies/{company}/suspend', [AdminCompanyController::class, 'suspend']);
    Route::patch('companies/{company}/deactivate', [AdminCompanyController::class, 'deactivate']);
    Route::post('companies/{company}/reset-password', [AdminCompanyController::class, 'resetPassword']);
    Route::apiResource('plans', AdminPlanController::class)->except(['show']);
});

Route::middleware(['auth:sanctum', 'current.company'])->group(function () {
    Route::get('search', SearchController::class);
    Route::get('dashboard', DashboardController::class);

    Route::apiResource('clients', ClientController::class);
    Route::get('clients/{client}/activities', [ClientRelationController::class, 'activities']);
    Route::get('clients/{client}/tasks', [ClientRelationController::class, 'tasks']);
    Route::post('clients/{client}/tasks', [ClientRelationController::class, 'storeTask']);
    Route::get('clients/{client}/appointments', [ClientRelationController::class, 'appointments']);
    Route::post('clients/{client}/appointments', [ClientRelationController::class, 'storeAppointment']);
    Route::get('clients/{client}/payments', [ClientRelationController::class, 'payments']);
    Route::post('clients/{client}/payments', [ClientRelationController::class, 'storePayment']);
    Route::get('clients/{client}/documents', [ClientRelationController::class, 'documents']);
    Route::post('clients/{client}/documents', [ClientRelationController::class, 'storeDocument']);
    Route::get('clients/{client}/notes', [ClientRelationController::class, 'notes']);
    Route::post('clients/{client}/notes', [ClientRelationController::class, 'storeNote']);

    Route::apiResource('tasks', TaskController::class);
    Route::patch('tasks/{task}/complete', [TaskController::class, 'complete']);

    Route::apiResource('appointments', AppointmentController::class);
    Route::patch('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::patch('appointments/{appointment}/complete', [AppointmentController::class, 'complete']);

    Route::apiResource('payments', PaymentController::class);
    Route::patch('payments/{payment}/paid', [PaymentController::class, 'paid']);
    Route::apiResource('expenses', ExpenseController::class);
    Route::patch('expenses/{expense}/paid', [ExpenseController::class, 'paid']);

    Route::apiResource('documents', DocumentController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::get('documents/{document}/download', [DocumentController::class, 'download']);
    Route::apiResource('notes', NoteController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('settings', [SettingController::class, 'show']);
    Route::put('settings', [SettingController::class, 'update']);
    Route::patch('settings/theme', [SettingController::class, 'theme']);
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'read']);
    Route::patch('notifications/read-all', [NotificationController::class, 'readAll']);
    Route::get('profile', [UserController::class, 'profile']);
    Route::put('profile', [UserController::class, 'updateProfile']);
    Route::apiResource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('company', [CompanyController::class, 'show']);
    Route::put('company', [CompanyController::class, 'update']);
});
