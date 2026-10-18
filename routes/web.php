
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Guru\CetakLaporanController;

Route::redirect('/', '/admin/login');

Route::middleware('auth')
    ->get('/cetak/laporan-presensi', CetakLaporanController::class)
    ->name('guru.laporan.cetak');