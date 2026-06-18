<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AdminCompanyController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminPlanController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AutomationController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CustomFieldController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfessionalCaseController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ClientRelationController;
use App\Http\Controllers\Api\ImportExportController;
use App\Http\Controllers\Api\InsightsController;
use App\Http\Controllers\Api\IntegrationController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\LeadStageController;
use App\Http\Controllers\Api\LeadStageManageController;
use App\Http\Controllers\Api\MessageTemplateController;
use App\Http\Controllers\Api\MyDashboardController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OpenApiController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PipelineController;
use App\Http\Controllers\Api\PipelineBoardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReportPdfController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TimelineController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WebhookConfigController;
use App\Http\Controllers\Api\WhatsappController;
use App\Http\Controllers\Api\WhatsappWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('openapi.json', OpenApiController::class);
Route::get('integrations/google-calendar/callback', [IntegrationController::class, 'googleCalendarCallback']);
Route::post('login', [AuthController::class, 'login']);
Route::post('webhooks/whatsapp/{company}', WhatsappWebhookController::class);
Route::post('webhooks/stripe', StripeWebhookController::class);

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
    Route::get('my-dashboard', MyDashboardController::class);
    Route::get('pipelines/board', PipelineBoardController::class);
    Route::get('clients/{client}/insights', [InsightsController::class, 'client']);
    Route::get('leads/{lead}/insights', [InsightsController::class, 'lead']);

    Route::apiResource('pipelines', PipelineController::class)->except(['show']);
    Route::post('lead-stages', [LeadStageManageController::class, 'store']);
    Route::put('lead-stages/reorder', [LeadStageManageController::class, 'reorder']);
    Route::put('lead-stages/{stage}', [LeadStageManageController::class, 'update']);
    Route::delete('lead-stages/{stage}', [LeadStageManageController::class, 'destroy']);

    Route::apiResource('cases', ProfessionalCaseController::class);

    Route::apiResource('automations', AutomationController::class)->except(['show']);
    Route::apiResource('message-templates', MessageTemplateController::class)->except(['show']);
    Route::apiResource('tags', TagController::class)->except(['show']);
    Route::apiResource('custom-fields', CustomFieldController::class)->except(['show']);
    Route::post('custom-fields/values', [CustomFieldController::class, 'saveValues']);
    Route::get('custom-fields/values', [CustomFieldController::class, 'getValues']);
    Route::get('audit-logs', [AuditLogController::class, 'index']);
    Route::apiResource('webhooks', WebhookConfigController::class)->except(['show']);

    Route::get('clients/{client}/timeline', [TimelineController::class, 'client']);
    Route::get('leads/{lead}/timeline', [TimelineController::class, 'lead']);

    Route::get('export/clients', [ImportExportController::class, 'exportClients']);
    Route::get('export/leads', [ImportExportController::class, 'exportLeads']);
    Route::post('import/clients', [ImportExportController::class, 'importClients']);
    Route::post('import/leads', [ImportExportController::class, 'importLeads']);

    Route::get('integrations', [IntegrationController::class, 'index']);
    Route::post('integrations', [IntegrationController::class, 'upsert']);
    Route::get('integrations/google-calendar/connect', [IntegrationController::class, 'connectGoogleCalendar']);
    Route::delete('integrations/google-calendar', [IntegrationController::class, 'disconnectGoogleCalendar']);
    Route::patch('integrations/google-calendar', [IntegrationController::class, 'updateGoogleCalendar']);
    Route::post('integrations/google-calendar/sync', [IntegrationController::class, 'syncGoogleCalendar']);

    Route::get('reports/pdf', ReportPdfController::class);

    Route::get('whatsapp/conversations', [WhatsappController::class, 'index']);
    Route::post('whatsapp/conversations/start', [WhatsappController::class, 'start']);
    Route::get('whatsapp/conversations/{conversation}/messages', [WhatsappController::class, 'messages']);
    Route::post('whatsapp/conversations/{conversation}/messages', [WhatsappController::class, 'send']);
    Route::patch('whatsapp/conversations/{conversation}/read', [WhatsappController::class, 'read']);

    Route::get('reports', ReportController::class);
    Route::get('lead-stages', [LeadStageController::class, 'index']);

    Route::apiResource('clients', ClientController::class);
    Route::get('clients/{client}/export-data', [ClientController::class, 'exportData']);
    Route::post('clients/{client}/anonymize', [ClientController::class, 'anonymize']);
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

    Route::apiResource('leads', LeadController::class);
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert']);
    Route::patch('leads/{lead}/lost', [LeadController::class, 'lost']);

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
