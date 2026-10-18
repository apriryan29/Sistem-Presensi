
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Guru\CetakLaporanController;
use App\Http\Controllers\Admin\LaporanKelasPdfController;

Route::redirect('/', '/admin/login');

Route::middleware('auth')
    ->get('/cetak/laporan-presensi', CetakLaporanController::class)
    ->name('guru.laporan.cetak');

Route::get(
    '/admin/laporan-kelas/{kelas}/pdf',
    LaporanKelasPdfController::class
)
    ->middleware('auth')
    ->name('admin.laporan-kelas.pdf');