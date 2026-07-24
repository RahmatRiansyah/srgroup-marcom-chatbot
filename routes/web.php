<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\DataSourceController;
use App\Http\Controllers\Admin\ScrapeLogController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes - SR Group Marcom Analytics & AI
|--------------------------------------------------------------------------
*/

// Halaman Utama / Landing Page
Route::get('/', function () {
    return view('welcome');
});

// Halaman Dashboard (Memerlukan Login & Email Verified)

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'active'])
    ->name('dashboard');

// Group Route khusus Pengguna yang Sudah Login (Authenticated Users)
Route::middleware(['auth', 'active'])->group(function () {

    // ==========================================
    // 1. FITUR AI CHATBOT MARCOM
    // ==========================================
    // ✅ UBAH KEDUANYA MENGARAH KE index
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{id}', [ChatController::class, 'index'])->name('chat.show');
    Route::post('/chat/new', [ChatController::class, 'newSession'])->name('chat.new');
    
    // DIPERBAIKI: Dipisah menjadi 2 route tegas agar /chat/send dan /chat/{id}/send terdeteksi POST
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
    Route::post('/chat/{id}/send', [ChatController::class, 'send'])->name('chat.send.session');
    
    Route::delete('/chat/{id}', [ChatController::class, 'destroy'])->name('chat.destroy');


    // ==========================================
    // 2. MANAJEMEN ADMIN DATA SOURCE / TARGET KOMPETITOR
    //    (Route::middleware('admin') di sini WAJIB dipasang setelah 'auth'
    //    dari group luar -- lihat EnsureUserIsAdmin, cek $user->isAdmin())
    // ==========================================
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/datasource', [DataSourceController::class, 'index'])->name('datasource.index');
        Route::post('/datasource', [DataSourceController::class, 'store'])->name('datasource.store');
        Route::get('/datasource/{id}/edit', [DataSourceController::class, 'edit'])->name('datasource.edit');
        Route::put('/datasource/{id}', [DataSourceController::class, 'update'])->name('datasource.update');
        Route::delete('/datasource/{id}', [DataSourceController::class, 'destroy'])->name('datasource.destroy');

        Route::get('/scrape-log', [ScrapeLogController::class, 'index'])->name('scrapelog.index');
        Route::post('/scrape-log/run', [ScrapeLogController::class, 'runNow'])->name('scrapelog.run');

        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.role');
        Route::put('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('users.toggle-active');
    });


    // ==========================================
    // 3. MANAJEMEN PROFIL PENGGUNA
    // ==========================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

// Auth Routes (Login, Register, Password Reset dari Breeze/Fortify)
require __DIR__.'/auth.php';