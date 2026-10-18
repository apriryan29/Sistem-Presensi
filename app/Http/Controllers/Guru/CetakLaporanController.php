<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CetakLaporanController extends Controller
{
    /** Nama view PDF. Sesuaikan dengan lokasi file laporan-cetak.blade.php Anda. */
    private const VIEW = 'filament.pages.guru.laporan-cetak';

    private const STATUS = [
        'hadir' => 'Hadir',
        'alpa'  => 'Alpa',
        'sakit' => 'Sakit',
        'izin'  => 'Izin',
    ];

    public function __invoke(Request $request)
    {
        abort_unless(Auth::check() && Auth::user()->role === 'guru', 403);

        $data = $request->validate([
            'dari'   => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
        ]);

        $dari = Carbon::parse($data['dari'] ?? now()->startOfMonth())->startOfDay();
        $sampai = Carbon::parse($data['sampai'] ?? now())->startOfDay();

        if ($dari->gt($sampai)) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        // Hanya kelas milik guru yang sedang login.
        $kelas = Kelas::query()
            ->where('wali_kelas_id', Auth::id())
            ->first();

        abort_if(! $kelas, 404, 'Anda belum terdaftar sebagai wali kelas.');

        $siswa = Siswa::query()
            ->where('kelas', $kelas->nama_kelas)
            ->orderBy('nama')
            ->get(['id', 'nis', 'nama']);

        $rekap = [];

        Presensi::query()
            ->whereIn('siswa_id', $siswa->pluck('id'))
            ->whereDate('tanggal', '>=', $dari->toDateString())
            ->whereDate('tanggal', '<=', $sampai->toDateString())
            ->selectRaw('siswa_id, status, COUNT(*) as total')
            ->groupBy('siswa_id', 'status')
            ->get()
            ->each(function ($baris) use (&$rekap) {
                $rekap[$baris->siswa_id][$baris->status] = (int) $baris->total;
            });

        $pdf = Pdf::loadView(self::VIEW, [
            'kelas'  => $kelas,
            'siswa'  => $siswa,
            'rekap'  => $rekap,
            'status' => self::STATUS,
            'dari'   => $dari,
            'sampai' => $sampai,
            'guru'   => Auth::user()->name ?? Auth::user()->username ?? '-',
        ])->setPaper('a4', 'portrait');

        $namaFile = sprintf(
            'Laporan-Presensi-Kelas-%s-%s_sd_%s.pdf',
            Str::slug($kelas->nama_kelas),
            $dari->format('Y-m-d'),
            $sampai->format('Y-m-d'),
        );

        // stream(): PDF terbuka di tab baru (bisa diunduh atau dicetak dari sana).
        // Ganti ke ->download($namaFile) jika ingin langsung terunduh.
        return $pdf->stream($namaFile);
    }
}