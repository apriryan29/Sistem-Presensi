<?php

namespace App\Filament\Pages\Admin;

use App\Filament\Widgets\Admin\StatistikSiswa;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Support\Carbon;

class Dashboard extends Page
{
    protected string $view = 'filament.pages.admin.dashboard';

    protected static ?string $title = 'Dashboard';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $slug = 'admin-dashboard';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
    protected function getViewData(): array
    {
        $hari = Carbon::now('Asia/Jakarta')->locale('id');

        $statusHariIni = Presensi::query()
            ->whereDate('tanggal', $hari->toDateString())
            ->get(['siswa_id', 'status'])
            ->groupBy('siswa_id')
            ->map(fn ($g) => $g->pluck('status')
                ->map(fn ($s) => strtolower(trim((string) $s)))
                ->unique()
                ->values()
                ->all());

        $semuaSiswa = Siswa::query()
            ->orderByRaw("kelas NOT REGEXP '^[0-9]+$'")
            ->orderByRaw('CAST(kelas AS UNSIGNED)')
            ->orderBy('kelas')
            ->orderBy('nama')
            ->get(['id', 'nis', 'nama', 'kelas']);

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
            'rekapHariIni'  => $rekap,
            'totalSiswa'    => $semuaSiswa->count(),
            'belumPresensi' => $belumPresensi,
            'detail'        => $detail,
        ];
    }
    protected function getFooterWidgets(): array
    {
        return [
            StatistikSiswa::class,
        ];
    }
}