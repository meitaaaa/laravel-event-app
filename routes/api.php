<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\AdminReportController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/request-reset', [AuthController::class, 'requestReset']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// publik
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);
Route::get('/certificates/search', [CertificateController::class, 'search']);
Route::get('/events/{event}/attendance/status', [AttendanceController::class, 'status']);
Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download']);

Route::middleware(['auth:sanctum', 'inactivity'])->group(function () {
    // peserta
    Route::post('/events/{event}/register', [RegistrationController::class, 'register']);
    Route::post('/events/{event}/attendance', [AttendanceController::class, 'submit']);
    Route::get('/me/history', [RegistrationController::class, 'myHistory']);
    Route::get('/me/certificates', [CertificateController::class, 'myCertificates']);
    Route::post('/registrations/{registration}/generate-certificate', [CertificateController::class, 'generate']);
    Route::get('/registrations/{registration}/certificate-status', [CertificateController::class, 'status']);

    // admin-only
    Route::middleware('can:admin')->group(function () {
        Route::post('/admin/events', [EventController::class, 'store']);
        Route::put('/admin/events/{event}', [EventController::class, 'update']);
        Route::post('/admin/events/{event}/publish', [EventController::class, 'publish']);
        Route::delete('/admin/events/{event}', [EventController::class, 'destroy']);

        Route::get('/admin/reports/monthly-events', [AdminReportController::class, 'monthlyEvents']);
        Route::get('/admin/reports/monthly-attendees', [AdminReportController::class, 'monthlyAttendees']);
        Route::get('/admin/reports/top10-events', [AdminReportController::class, 'top10Events']);
        Route::get('/admin/events/{event}/export', [AdminReportController::class, 'exportParticipants']);
        Route::get('/admin/reports/export-all-participants', [AdminReportController::class, 'exportAllParticipants']);
    });
});
