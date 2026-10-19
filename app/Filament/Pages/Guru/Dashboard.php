<?php

namespace App\Filament\Pages\Guru;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Support\Carbon;

class Dashboard extends Page
{
    protected string $view = 'filament.pages.guru.dashboard';

    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $slug = 'guru-dashboard';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 1;
    public function getBreadcrumbs(): array
    {
        return [
            url('/admin/guru-dashboard') => 'Dashboard',
            '#' => 'View',
        ];
    }
    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'guru';
    }

    /**
     * Kelas yang diampu guru yang sedang login (dari tabel users).
     */
    protected function kelasDiampu(): array
    {
        return \App\Models\Kelas::query()
        ->where('wali_kelas_id', Auth::id())
        ->pluck('nama_kelas') // kolom nama kelas yang nilainya sama dengan siswa.kelas, mis. "7A"
        ->map(fn ($k) => (string) $k)
        ->all();
    }

    protected function getViewData(): array
    {
        $hari = Carbon::now('Asia/Jakarta')->locale('id');

        // Siswa hanya dari kelas yang diampu guru
        $semuaSiswa = Siswa::query()
            ->whereIn('kelas', $this->kelasDiampu())
            ->orderByRaw("kelas NOT REGEXP '^[0-9]+$'")
            ->orderByRaw('CAST(kelas AS UNSIGNED)')
            ->orderBy('kelas')
            ->orderBy('nama')
            ->get(['id', 'nis', 'nama', 'kelas']);

        // Presensi hanya untuk siswa-siswa tersebut
        $statusHariIni = Presensi::query()
            ->whereDate('tanggal', $hari->toDateString())
            ->whereIn('siswa_id', $semuaSiswa->pluck('id'))
            ->get(['siswa_id', 'status'])
            ->groupBy('siswa_id')
            ->map(fn ($g) => $g->pluck('status')
                ->map(fn ($s) => strtolower(trim((string) $s)))
                ->unique()
                ->values()
                ->all());

        $rekap  = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];
        $detail = ['hadir' => [], 'izin' => [], 'sakit' => [], 'alpa' => [], 'belum' => []];
        $belumPresensi = 0;

        foreach ($semuaSiswa as $s) {
            $daftar = $statusHariIni[$s->id] ?? [];

            $baris = [
                'nis'   => $s->nis,
                'nama'  => $s->nama,
                'kelas' => ctype_digit(trim((string) $s->kelas)) ? 'Kelas ' . $s->kelas : (string) $s->kelas,
            ];

            if (empty($daftar)) {
                $belumPresensi++;
            }

            foreach (array_keys($rekap) as $kode) {
                if (in_array($kode, $daftar, true)) {
                    $rekap[$kode]++;
                    $detail[$kode][] = $baris + ['status' => ucfirst($kode)];
                }
            }

            // Daftar "belum hadir": semua yang hari ini tidak berstatus hadir
            if (! in_array('hadir', $daftar, true)) {
                $status = 'Belum presensi';
                foreach (['alpa', 'sakit', 'izin'] as $prioritas) {
                    if (in_array($prioritas, $daftar, true)) {
                        $status = ucfirst($prioritas);
                        break;
                    }
                }

                $detail['belum'][] = $baris + ['status' => $status];
            }
        }

        return [
            'tanggalAktif'  => $hari->translatedFormat('l, d F Y'),
            'kelasDiampu'   => implode(', ', $this->kelasDiampu()),
            'rekapHariIni'  => $rekap,
            'totalSiswa'    => $semuaSiswa->count(),
            'belumPresensi' => $belumPresensi,
            'detail'        => $detail,
        ];
    }
}