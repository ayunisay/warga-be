<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CitizenController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransferVerificationController;
use App\Http\Controllers\Api\SuratController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth & Users
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/user', [AuthController::class, 'me']);
Route::get('/users', [AuthController::class, 'users']);
Route::post('/users', [AuthController::class, 'storeUser']);
Route::put('/users/{id}', [AuthController::class, 'updateUser']);
Route::delete('/users/{id}', [AuthController::class, 'destroyUser']);

// Citizens (Data Warga)
Route::get('/citizens', [CitizenController::class, 'index']);
Route::post('/citizens', [CitizenController::class, 'store']);
Route::get('/citizens/{id}', [CitizenController::class, 'show']);
Route::put('/citizens/{id}', [CitizenController::class, 'update']);
Route::delete('/citizens/{id}', [CitizenController::class, 'destroy']);
Route::patch('/citizens/{id}/toggle-iuran', [CitizenController::class, 'toggleIuran']);

// Keuangan & Kas
Route::get('/transactions', [TransactionController::class, 'index']);
Route::post('/transactions', [TransactionController::class, 'store']);
Route::get('/transactions/summary', [TransactionController::class, 'summary']);

// Verifikasi Transfer Iuran (Manual Transfer)
Route::get('/transfers', [TransferVerificationController::class, 'index']);
Route::post('/transfers', [TransferVerificationController::class, 'store']);
Route::post('/transfers/verify/{id}', [TransferVerificationController::class, 'verify']);

// Payment Gateway Otomatis (Midtrans / Tripay: Dynamic QRIS & Virtual Account)
Route::get('/payments', [PaymentController::class, 'index']);
Route::post('/payments/charge', [PaymentController::class, 'createCharge']);
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
Route::get('/payments/status/{orderId}', [PaymentController::class, 'checkStatus']);
Route::post('/payments/simulate/{orderId}', [PaymentController::class, 'simulatePayment']);

// Permohonan Surat Pengantar
Route::get('/surat', [SuratController::class, 'index']);
Route::post('/surat', [SuratController::class, 'store']);
Route::patch('/surat/{id}/status', [SuratController::class, 'updateStatus']);
Route::get('/surat/verify/{token}', [SuratController::class, 'verifyToken']);

// Papan Informasi & Agenda
Route::get('/announcements', [AnnouncementController::class, 'index']);
Route::post('/announcements', [AnnouncementController::class, 'store']);
Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);

// Dashboard & RT Info
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
Route::get('/rt-info', [DashboardController::class, 'rtInfo']);
