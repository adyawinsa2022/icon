<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\CopierController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EncyclopediaController;

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
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    // Menu
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');

    // Copier
    Route::get('/copier', [CopierController::class, 'index'])->name('copier.index');

    // Encyclopedia
    Route::get('/encyclopedia', [EncyclopediaController::class, 'index'])->name('encyclopedia.index');
    Route::get('/encyclopedia/{id}', [EncyclopediaController::class, 'show'])->name('encyclopedia.article');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/reset-password', [ProfileController::class, 'showResetPassword'])->name('profile.reset_password');
    Route::post('/profile/reset-password', [ProfileController::class, 'resetPassword'])->name('profile.reset_password.process');

    // Ticket
    Route::get('/ticket/create/{deviceName?}', [TicketController::class, 'create'])->name('ticket.create');
    Route::post('/ticket/store', [TicketController::class, 'store'])->name('ticket.store');
    Route::get('/ticket/{id}', [TicketController::class, 'show'])->name('ticket.show');
    Route::get('/ticket/history/{deviceName}', [TicketController::class, 'history'])->name('ticket.device');
    Route::get('/ticket', [TicketController::class, 'index'])->name('ticket.index');
});
