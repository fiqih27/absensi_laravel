<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuthController;

// Halaman utama - Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Attendance Routes
Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::post('/sync', [AttendanceController::class, 'sync'])->name('sync');
    Route::get('/check-device/{id}', [AttendanceController::class, 'checkDevice'])->name('check-device');
});

// Report Routes
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
    Route::get('/notifications', [ReportController::class, 'notifications'])->name('notifications');
    Route::get('/devices', [ReportController::class, 'devices'])->name('devices');
    Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');
    Route::post('/export', [ReportController::class, 'export'])->name('export');
});

// Notification Routes
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
    Route::post('/{notification}/resend', [NotificationController::class, 'resend'])->name('resend');
    Route::post('/resend-all', [NotificationController::class, 'resendAll'])->name('resend-all');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/delete-old', [NotificationController::class, 'deleteOld'])->name('delete-old');
    Route::get('/broadcast/create', [NotificationController::class, 'broadcast'])->name('broadcast');
    Route::post('/broadcast/send', [NotificationController::class, 'sendBroadcast'])->name('send-broadcast');
   Route::get('/broadcast/history', [NotificationController::class, 'broadcastHistory'])->name('broadcast.history');
    Route::get('/broadcast/{broadcastId}', [NotificationController::class, 'broadcastDetail'])->name('broadcast.detail');
   Route::delete('/broadcast/delete-selected', [NotificationController::class, 'deleteSelectedBroadcasts'])->name('broadcast.delete-selected');
    Route::delete('/broadcast/delete-all', [NotificationController::class, 'deleteAllBroadcasts'])->name('broadcast.delete-all');
    Route::delete('/broadcast/{broadcastId}', [NotificationController::class, 'deleteBroadcast'])->name('broadcast.delete');
    Route::get('/broadcast/{broadcastId}', [NotificationController::class, 'broadcastDetail'])->name('broadcast.detail');
    Route::post('/broadcast/{broadcastId}/resend', [NotificationController::class, 'resendFailedBroadcast'])->name('broadcast.resend');
});

// Setting Routes
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingController::class, 'index'])->name('index');
    Route::put('/general', [SettingController::class, 'updateGeneral'])->name('update-general');
    Route::put('/whatsapp', [SettingController::class, 'updateWhatsApp'])->name('update-whatsapp');
    Route::put('/sync', [SettingController::class, 'updateSync'])->name('update-sync');
    Route::post('/test-whatsapp', [SettingController::class, 'testWhatsApp'])->name('test-whatsapp');
    Route::post('/test-device', [SettingController::class, 'testDevice'])->name('test-device');
    Route::post('/clear-cache', [SettingController::class, 'clearCache'])->name('clear-cache');
    Route::post('/backup', [SettingController::class, 'backup'])->name('backup');
});

// Resource Routes
Route::resource('devices', DeviceController::class);
Route::resource('students', StudentController::class);
Route::resource('parents', ParentController::class);

// Auth Routes (opsional)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
