<?php

use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\LoginHistoryController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AppointmentIndexController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/webhooks/whatsapp/meta', [WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.meta.verify');
Route::post('/webhooks/whatsapp/meta', [WhatsAppWebhookController::class, 'handle'])->name('webhooks.whatsapp.meta');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/agenda', 'agenda.index')->name('agenda.index');
    Route::view('/clients', 'clients.index')->name('clients.index');
    Route::view('/clients/list', 'clients.list')->name('clients.list');
    Route::view('/clients/create', 'clients.form')->name('clients.create');
    Route::view('/clients/{client}/edit', 'clients.form')->name('clients.edit');
    Route::view('/clients/{client}/appointments', 'appointments.client')
        ->whereNumber('client')
        ->name('clients.appointments');
    Route::get('/appointments', AppointmentIndexController::class)->name('appointments.index');
    Route::view('/appointments/enviadas', 'appointments.sent')->name('appointments.sent');
    Route::view('/appointments/create', 'appointments.form')->name('appointments.create');
    Route::view('/appointments/{appointment}/edit', 'appointments.form')->name('appointments.edit');

    Route::middleware('admin')->group(function () {
        Route::get('/admin/users', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
        Route::get('/admin/login-history', [LoginHistoryController::class, 'index'])->name('admin.login-history');

        Route::view('/admin/tools', 'admin.tools.index')->name('admin.tools');
        Route::view('/admin/settings', 'settings.index')->name('settings.index');
        Route::view('/admin/imports', 'imports.index')->name('imports.index');
        Route::get('/admin/export/appointments', [ExportController::class, 'appointments'])->name('admin.export.appointments');
        Route::get('/admin/export/clients', [ExportController::class, 'clients'])->name('admin.export.clients');
        Route::get('/admin/export/users', [ExportController::class, 'users'])->name('admin.export.users');
        Route::get('/admin/export/database', [ExportController::class, 'database'])->name('admin.export.database');
    });
});
