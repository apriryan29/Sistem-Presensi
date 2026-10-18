<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Presensi Kelas {{ $kelas->nama_kelas }}</title>
    <style>
        @page { margin: 40px 36px; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
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
    <h1>Laporan Rekap Presensi Siswa</h1>
    <div class="sub">{{ config('app.name') }}</div>

    <table class="meta">
        <tr><td>Kelas</td><td>: {{ $kelas->nama_kelas }}</td></tr>
        <tr><td>Wali Kelas</td><td>: {{ $guru }}</td></tr>
        <tr>
            <td>Periode</td>
            <td>: {{ $dari->translatedFormat('d F Y') }} – {{ $sampai->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    @php
        $total = array_fill_keys(array_keys($status), 0);
    @endphp

    <table class="data">
        <thead>
            <tr>
                <th class="c" style="width: 28px;">No</th>
                <th style="width: 80px;">NIS</th>
                <th>Nama Siswa</th>
                @foreach ($status as $label)
                    <th class="c" style="width: 42px;">{{ $label }}</th>
                @endforeach
                <th class="c" style="width: 52px;">% Hadir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($siswa as $i => $s)
                @php
                    $baris = $rekap[$s->id] ?? [];
                    $jumlahBaris = array_sum($baris);
                    $persen = $jumlahBaris > 0 ? round(($baris['hadir'] ?? 0) / $jumlahBaris * 100, 1) : 0;
                @endphp
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ $s->nis }}</td>
                    <td>{{ $s->nama }}</td>
                    @foreach ($status as $kode => $label)
                        @php $total[$kode] += $baris[$kode] ?? 0; @endphp
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
        @if ($siswa->isNotEmpty())
            @php
                $semua = array_sum($total);
                $persenTotal = $semua > 0 ? round($total['hadir'] / $semua * 100, 1) : 0;
            @endphp
            <tfoot>
                <tr>
                    <td colspan="3" class="c">Total</td>
                    @foreach ($status as $kode => $label)
                        <td class="c">{{ $total[$kode] }}</td>
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