<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;


// Auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Assets
Route::get('/assets/{deviceName}', [AssetsController::class, 'show'])->name('device.show');
Route::get('/assets-info/{deviceName}', [AssetsController::class, 'info'])->name('device.info');

// Documents
Route::get('/document/{id}', [DocumentController::class, 'show'])->name('document.show');

// Middleware Group (harus ada GLPI session)
Route::middleware(['glpi.session'])->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/reset-password', [ProfileController::class, 'showResetPassword'])->name('profile.reset_password');
    Route::post('/profile/reset-password', [ProfileController::class, 'resetPassword'])->name('profile.reset_password.process');

    // Ticket
    Route::get('/ticket/create/{deviceName?}', [TicketController::class, 'create'])->name('ticket.create');
    Route::post('/ticket/store', [TicketController::class, 'store'])->name('ticket.store');
    Route::get('/ticket/{id}', [TicketController::class, 'show'])->name('ticket.show');
    Route::get('/ticket/{id}/take', [TicketController::class, 'take'])->name('ticket.take');
    Route::post('/ticket/{id}/followup', [TicketController::class, 'followup'])->name('ticket.followup');
    Route::post('/ticket/{id}/task', [TicketController::class, 'task'])->name('ticket.task');
    Route::post('/ticket/{id}/solution', [TicketController::class, 'solution'])->name('ticket.solution');
    Route::post('/ticket/{ticketId}/approval/{solutionId?}', [TicketController::class, 'approval'])->name('ticket.approval');
    Route::get('/ticket/history/{deviceName}', [TicketController::class, 'history'])->name('ticket.device');
    Route::get('/', [TicketController::class, 'index'])->name('ticket.index');
});
