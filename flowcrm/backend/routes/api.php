<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\KanbanController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'current.company'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('dashboard', DashboardController::class);

    Route::apiResource('clients', ClientController::class);
    Route::apiResource('leads', LeadController::class);
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert']);
    Route::patch('leads/{lead}/lost', [LeadController::class, 'lost']);

    Route::get('kanban', [KanbanController::class, 'index']);
    Route::patch('kanban/leads/{lead}/move', [KanbanController::class, 'move']);

    Route::apiResource('tasks', TaskController::class);
    Route::patch('tasks/{task}/complete', [TaskController::class, 'complete']);

    Route::apiResource('appointments', AppointmentController::class);
    Route::patch('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::patch('appointments/{appointment}/complete', [AppointmentController::class, 'complete']);

    Route::apiResource('payments', PaymentController::class);
    Route::patch('payments/{payment}/paid', [PaymentController::class, 'paid']);
    Route::apiResource('expenses', ExpenseController::class);

    Route::apiResource('documents', DocumentController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::get('documents/{document}/download', [DocumentController::class, 'download']);
    Route::apiResource('notes', NoteController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('reports', ReportController::class);
    Route::get('settings', [SettingController::class, 'show']);
    Route::put('settings', [SettingController::class, 'update']);
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'read']);
    Route::get('profile', [UserController::class, 'profile']);
    Route::put('profile', [UserController::class, 'updateProfile']);
    Route::apiResource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('company', [CompanyController::class, 'show']);
    Route::put('company', [CompanyController::class, 'update']);
});
