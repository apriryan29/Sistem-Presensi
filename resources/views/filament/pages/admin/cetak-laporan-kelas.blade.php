<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Presensi Kelas {{ $kelas->nama_kelas }}</title>
    <style>
        @page { margin: 40px 36px; }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            color: #111;
        }
        h1 { font-size: 14px; text-align: center; margin: 0 0 4px; text-transform: uppercase; }
        .sub { text-align: center; margin-bottom: 16px; color: #444; }
        table.meta { margin-bottom: 12px; border-collapse: collapse; }
        table.meta td { padding: 2px 10px 2px 0; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #333; padding: 5px 6px; }
        table.data th { background: #eeeeee; }
        table.data tfoot td { font-weight: bold; background: #f5f5f5; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        .c { text-align: center; }
        table.ttd { margin-top: 36px; width: 100%; border-collapse: collapse; }
        table.ttd td { width: 50%; text-align: center; vertical-align: top; }
    </style>
</head>
<body>
    @php
        $guru = $kelas->waliKelas?->name ?? '-';

        $status = [
            'hadir' => 'Hadir',
            'izin'  => 'Izin',
            'sakit' => 'Sakit',
            'alpa'  => 'Alpa',
        ];

        $semua = array_sum($rekap);
        $persenTotal = $semua > 0 ? round(($rekap['hadir'] ?? 0) / $semua * 100, 1) : 0;
    @endphp

    <h1>Laporan Rekap Presensi Siswa</h1>
    <div class="sub">{{ config('app.name') }}</div>

    <table class="meta">
        <tr><td>Kelas</td><td>: {{ $kelas->nama_kelas }}</td></tr>
        <tr><td>Wali Kelas</td><td>: {{ $guru }}</td></tr>
        <tr>
            <td>Periode</td>
            <td>:
                @if ($tanggalAwal && $tanggalAkhir)
                    {{ \Carbon\Carbon::parse($tanggalAwal)->translatedFormat('d F Y') }}
                    – {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') }}
                @else
                    Semua periode
                @endif
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th class="c" style="width: 28px;">No</th>
                <th style="width: 80px;">NIS</th>
                <th>Nama Siswa</th>
                @foreach ($status as $label)
                    <th class="c" style="width: 42px;">{{ $label }}</th>
                @endforeach
                <th class="c" style="width: 52px;">Presentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($siswas as $i => $s)
                @php
                    $baris = $perSiswa[$s->id] ?? [];
                    $jumlahBaris = array_sum($baris);
                    $persen = $jumlahBaris > 0 ? round(($baris['hadir'] ?? 0) / $jumlahBaris * 100, 1) : 0;
                @endphp
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ $s->nis }}</td>
                    <td>{{ $s->nama }}</td>
                    @foreach ($status as $kode => $label)
                        <td class="c">{{ $baris[$kode] ?? 0 }}</td>
                    @endforeach
                    <td class="c">{{ $persen }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 4 + count($status) }}" class="c">Belum ada siswa di kelas ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($siswas->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="3" class="c">Total</td>
                    @foreach ($status as $kode => $label)
                        <td class="c">{{ $rekap[$kode] ?? 0 }}</td>
                    @endforeach
                    <td class="c">{{ $persenTotal }}%</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <table class="ttd">
        <tr>
            <td></td>
            <td>
                {{ now()->translatedFormat('d F Y') }}<br>
                Wali Kelas,<br><br><br><br>
                <strong>{{ $guru }}</strong>
            </td>
        </tr>
    </table>
</body>
</html>