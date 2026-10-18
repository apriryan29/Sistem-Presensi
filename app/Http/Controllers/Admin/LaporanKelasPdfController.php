<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Presensi as PresensiModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaporanKelasPdfController extends Controller
{
    public function __invoke(Request $request, Kelas $kelas)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $validated = $request->validate([
            'tanggal_awal'  => ['nullable', 'date', 'required_with:tanggal_akhir'],
            'tanggal_akhir' => ['nullable', 'date', 'required_with:tanggal_awal', 'after_or_equal:tanggal_awal'],
            'aksi'          => ['nullable', 'in:lihat'],
        ]);

        $tanggalAwal  = $validated['tanggal_awal'] ?? null;
        $tanggalAkhir = $validated['tanggal_akhir'] ?? null;

        $siswas = Siswa::query()
            ->where('kelas', $kelas->nama_kelas)
            ->orderBy('nama')
            ->get();

        $query = PresensiModel::query()
            ->whereIn('siswa_id', $siswas->pluck('id'));

        if ($tanggalAwal && $tanggalAkhir) {
            $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
        }

        $rows = $query
            ->selectRaw('siswa_id, status, COUNT(*) as total')
            ->groupBy('siswa_id', 'status')
            ->get();

        $statuses = ['hadir', 'izin', 'sakit', 'alpa'];

        $perSiswa = [];
        foreach ($siswas as $siswa) {
            $perSiswa[$siswa->id] = array_fill_keys($statuses, 0);
        }

        foreach ($rows as $row) {
            $status = strtolower((string) $row->status);
            if (isset($perSiswa[$row->siswa_id][$status])) {
                $perSiswa[$row->siswa_id][$status] += (int) $row->total;
            }
        }

        $rekap = array_fill_keys($statuses, 0);
        foreach ($perSiswa as $baris) {
            foreach ($baris as $status => $jumlah) {
                $rekap[$status] += $jumlah;
            }
        }

        $data = [
            'kelas'        => $kelas->load('waliKelas'),
            'siswas'       => $siswas,
            'jumlahSiswa'  => $siswas->count(),
            'perSiswa'     => $perSiswa,
            'rekap'        => $rekap,
            'tanggalAwal'  => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
        ];

        $pdf = Pdf::loadView('filament.pages.admin.cetak-laporan-kelas', $data)
            ->setPaper('a4', 'portrait');

        $namaKelas = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $kelas->nama_kelas);
        $namaFile  = 'laporan-presensi-kelas-' . $namaKelas . '.pdf';

        if (($validated['aksi'] ?? null) === 'lihat') {
            return $pdf->stream($namaFile);
        }

        return $pdf->download($namaFile);
    }
}