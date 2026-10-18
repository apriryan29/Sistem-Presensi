<?php

namespace App\Filament\Widgets\Admin;

use App\Models\Kelas;
use App\Models\Siswa;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class StatistikSiswa extends Widget
{
    private const KOLOM = 'jenis_kelamin';

    protected string $view = 'filament.widgets.admin.statistik-siswa';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    protected function getViewData(): array
    {
        // Jumlah siswa per kelas dan jenis kelamin
        $rows = Siswa::query()
            ->selectRaw('kelas, ' . self::KOLOM . ' as jk, COUNT(*) as total')
            ->groupBy('kelas', self::KOLOM)
            ->get();

        $perKelas = [];
        foreach ($rows as $row) {
            $kode = strtoupper(substr(trim((string) $row->jk), 0, 1));
            $perKelas[$row->kelas] ??= ['L' => 0, 'P' => 0];

            if (isset($perKelas[$row->kelas][$kode])) {
                $perKelas[$row->kelas][$kode] += (int) $row->total;
            }
        }

        $namaKelas = Kelas::query()
            ->orderByRaw("nama_kelas NOT REGEXP '^[0-9]+$'")
            ->orderByRaw('CAST(nama_kelas AS UNSIGNED)')
            ->orderBy('nama_kelas')
            ->pluck('nama_kelas');

        $kelas = [];
        $laki = 0;
        $perempuan = 0;

        foreach ($namaKelas as $nama) {
            $l = $perKelas[$nama]['L'] ?? 0;
            $p = $perKelas[$nama]['P'] ?? 0;

            $kelas[] = [
                'label' => ctype_digit(trim((string) $nama)) ? 'Kelas ' . $nama : (string) $nama,
                'laki' => $l,
                'perempuan' => $p,
                'total' => $l + $p,
            ];

            $laki += $l;
            $perempuan += $p;
        }

        $total = $laki + $perempuan;

        return [
            'total' => $total,
            'laki' => $laki,
            'perempuan' => $perempuan,
            'persenLaki' => $total > 0 ? round($laki / $total * 100, 1) : 0,
            'persenPerempuan' => $total > 0 ? round($perempuan / $total * 100, 1) : 0,
            'kelas' => $kelas,
        ];
    }
}