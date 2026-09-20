<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthNasabahController;
use App\Http\Controllers\Api\ProfilNasabahController;
use App\Http\Controllers\Api\TransaksiNasabahController;

// ==========================================
// 1. AUTENTIKASI & KEAMANAN AKUN MOBILE
// ======== ==================================
Route::post('/nasabah/login', [AuthNasabahController::class, 'login']);
Route::post('/nasabah/logout', [AuthNasabahController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/nasabah/verify-pin', [AuthNasabahController::class, 'verifyPin']);
Route::post('/nasabah/ganti-password', [AuthNasabahController::class, 'gantiPassword']);
Route::post('/nasabah/ganti-pin', [AuthNasabahController::class, 'gantiPin']);

// ==========================================
// 2. PROFIL & INQUIRY REKENING
// ==========================================
Route::get('/nasabah/saldo', [ProfilNasabahController::class, 'saldo']);
Route::get('/nasabah/cek-rekening', [ProfilNasabahController::class, 'cekRekening']);

// ==========================================
// 3. TRANSAKSI & KEUANGAN
// ==========================================
Route::post('/nasabah/transfer', [TransaksiNasabahController::class, 'transfer']);
Route::get('/nasabah/riwayat', [TransaksiNasabahController::class, 'riwayat']);
Route::get('/nasabah/ringkasan', [TransaksiNasabahController::class, 'ringkasan']);
