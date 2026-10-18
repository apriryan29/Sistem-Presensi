<x-filament-panels::page>
    <x-filament::section
        heading="Presensi Hari Ini"
        icon="heroicon-o-calendar-days"
    >
        <x-slot name="afterHeader">
            <span style="font-size: 1rem; line-height: 1.5rem; font-weight: 600;">
                {{ $tanggalAktif }}
            </span>
        </x-slot>

        <div
            x-data="{
                open: false,
                judul: '',
                data: [],
                semua: @js($detail),
                buka(kunci, judul) {
                    this.judul = judul;
                    this.data = this.semua[kunci] ?? [];
                    this.open = true;
                },
            }"
        >
            {{-- Kotak status (bisa diklik) --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem;">
                @foreach ([
                    ['hadir', 'Hadir', $rekapHariIni['hadir'], '#16a34a'],
                    ['izin',  'Izin',  $rekapHariIni['izin'],  '#2563eb'],
                    ['sakit', 'Sakit', $rekapHariIni['sakit'], '#d97706'],
                    ['alpa',  'Alpa',  $rekapHariIni['alpa'],  '#dc2626'],
                ] as [$kunci, $label, $jumlah, $warna])
                    <button
                        type="button"
                        x-on:click="buka('{{ $kunci }}', 'Siswa {{ $label }}')"
                        title="Klik untuk melihat rincian"
                        style="text-align: left; cursor: pointer; border: 0; background: {{ $warna }}; color: #fff; border-radius: 0.5rem; padding: 0.75rem 1rem;"
                    >
                        <div style="font-size: 0.875rem; opacity: .9;">{{ $label }}</div>
                        <div style="font-size: 1.75rem; font-weight: 700;">{{ $jumlah }}</div>
                        <div style="font-size: 0.75rem; opacity: .85;">Lihat rincian</div>
                    </button>
                @endforeach
            </div>

            {{-- Banner belum presensi --}}
            <div
                style="
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    margin-top: 1rem;
                    padding: 16px 20px;
                    border-radius: 12px;
                    border: 1px solid rgba(223, 138, 9, 0.35);
                    background-color: rgba(223, 138, 9, 0.10);
                "
            >
                <x-filament::icon
                    icon="heroicon-o-information-circle"
                    style="width: 24px; height: 24px; flex-shrink: 0; color: rgb(223, 138, 9);"
                />

                <span style="flex: 1; font-size: 14px; font-weight: 600; color: rgb(223, 138, 9);">
                    Dari total {{ $totalSiswa }} siswa, {{ $belumPresensi }} siswa belum tercatat presensinya hari ini.
                </span>

                <button
                    type="button"
                    x-on:click="buka('belum', 'Siswa yang Belum Hadir')"
                    style="font-size: 13px; font-weight: 600; color: rgb(223, 138, 9); text-decoration: underline; cursor: pointer; background: none; border: 0;"
                >
                    Lihat rincian
                </button>
            </div>

            {{-- Modal --}}
            <template x-teleport="body">
                <div
                    x-show="open"
                    x-cloak
                    x-on:keydown.escape.window="open = false"
                    style="position: fixed; inset: 0; z-index: 50;"
                >
                    {{-- Latar gelap --}}
                    <div
                        x-on:click="open = false"
                        style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.5);"
                    ></div>

                    {{-- Pembungkus pemusat --}}
                    <div style="position: relative; display: flex; align-items: center; justify-content: center; min-height: 100%; padding: 1rem; pointer-events: none;">
                        <div
                            x-show="open"
                            x-transition
                            style="pointer-events: auto; width: 100%; max-width: 720px; max-height: 85vh; display: flex; flex-direction: column; background: #fff; color: #111827; border-radius: 0.75rem; box-shadow: 0 20px 40px rgba(0,0,0,.25);"
                        >
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(128,128,128,.3);">
                                <div>
                                    <p style="font-size: 1rem; font-weight: 600; margin: 0;" x-text="judul"></p>
                                    <p style="font-size: 0.8rem; opacity: .7; margin: 0;">
                                        {{ $tanggalAktif }} · <span x-text="data.length"></span> siswa
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    x-on:click="open = false"
                                    style="font-size: 1.5rem; line-height: 1; cursor: pointer; background: none; border: 0; color: inherit;"
                                    aria-label="Tutup"
                                >&times;</button>
                            </div>

                            <div style="overflow: auto; padding: 0.5rem 1.25rem 1rem;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                                    <thead>
                                        <tr style="text-align: left; border-bottom: 1px solid rgba(128,128,128,.4);">
                                            <th style="padding: 8px; width: 48px;">No</th>
                                            <th style="padding: 8px;">NIS</th>
                                            <th style="padding: 8px;">Nama Siswa</th>
                                            <th style="padding: 8px;">Kelas</th>
                                            <th style="padding: 8px;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(s, i) in data" :key="i">
                                            <tr style="border-bottom: 1px solid rgba(128,128,128,.2);">
                                                <td style="padding: 8px;" x-text="i + 1"></td>
                                                <td style="padding: 8px;" x-text="s.nis"></td>
                                                <td style="padding: 8px;" x-text="s.nama"></td>
                                                <td style="padding: 8px;" x-text="s.kelas"></td>
                                                <td style="padding: 8px;" x-text="s.status"></td>
                                            </tr>
                                        </template>
                                        <tr x-show="data.length === 0">
                                            <td colspan="5" style="padding: 12px; text-align: center;">Tidak ada siswa.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </x-filament::section>
</x-filament-panels::page>