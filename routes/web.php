<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\TellerController;
use App\Http\Controllers\SupervisorController;

Route::get('/', function () { return redirect('/login'); });
Route::get('/login', function () { return view('auth.login'); })->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =========================================================================
// RUTE ADMIN
// =========================================================================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index']);
    Route::get('/admin/tambah-pegawai', [AdminController::class, 'create']);
    Route::post('/admin/tambah-pegawai', [AdminController::class, 'store']);
    Route::get('/admin/edit-pegawai/{id}', [AdminController::class, 'edit']);
    Route::post('/admin/edit-pegawai/{id}', [AdminController::class, 'update']);
    Route::get('/admin/hapus-pegawai/{id}', [AdminController::class, 'destroy']);

    Route::get('/admin/data-nasabah', [NasabahController::class, 'index']);
    Route::get('/admin/tambah-nasabah', [NasabahController::class, 'create']);
    Route::post('/admin/tambah-nasabah', [NasabahController::class, 'store']);
    Route::get('/admin/edit-nasabah/{id}', [NasabahController::class, 'edit']);
    Route::post('/admin/edit-nasabah/{id}', [NasabahController::class, 'update']);
    Route::get('/admin/hapus-nasabah/{id}', [NasabahController::class, 'destroy']);

    Route::post('/admin/import-nasabah', [NasabahController::class, 'importCsv']);
    Route::get('/admin/template-nasabah', [NasabahController::class, 'downloadTemplate']);

    Route::get('/admin/profil', [AdminController::class, 'profil']);
    Route::post('/admin/profil/update', [AdminController::class, 'updateProfil']);

    // FITUR BARU: LOG AUDIT & EKSPOR CSV
    Route::get('/admin/audit', [AdminController::class, 'auditLog']);
    Route::get('/admin/audit/export', [AdminController::class, 'exportAuditCsv']);
});

// =========================================================================
// RUTE TELLER
// =========================================================================
Route::middleware(['auth', 'role:teller'])->group(function () {
    Route::get('/teller/dashboard', [TellerController::class, 'index']);
    Route::get('/teller/transaksi/{id}', [TellerController::class, 'formTransaksi']);
    Route::post('/teller/transaksi/proses', [TellerController::class, 'prosesTransaksi']);
    Route::get('/teller/riwayat', [TellerController::class, 'riwayat']);
    Route::get('/teller/scan', [TellerController::class, 'scanQr']);
    Route::get('/teller/cari-nis/{nis}', [TellerController::class, 'cariByNis']);
    Route::get('/teller/profil', [TellerController::class, 'profil']);
    Route::post('/teller/profil/update', [TellerController::class, 'updateProfil']);
    Route::post('/teller/transaksi/void/{id}', [TellerController::class, 'voidTransaksi']);
});

// =========================================================================
// RUTE SUPERVISOR
// =========================================================================
Route::middleware(['auth', 'role:supervisor'])->group(function () {
    Route::get('/supervisor/dashboard', [SupervisorController::class, 'index']);
    Route::get('/supervisor/laporan', [SupervisorController::class, 'laporan']);
    Route::get('/supervisor/approve/{id}', [SupervisorController::class, 'approve']);
    Route::get('/supervisor/cetak-harian', [SupervisorController::class, 'cetakHarian']);
    Route::get('/supervisor/profil', [SupervisorController::class, 'profil']);
    Route::post('/supervisor/profil/update', [SupervisorController::class, 'updateProfil']);
});

// HELPER DATABASE & STORAGE
Route::get('/fix-database-pegawai', function () { /* ... */ });
Route::get('/fix-database-void', function () { /* ... */ });
Route::get('/fix-foto', function () { Artisan::call('storage:link'); return 'OK'; });
