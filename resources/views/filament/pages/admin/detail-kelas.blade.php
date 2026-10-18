<x-filament-panels::page>
    @php
        $namaKelas = trim((string) $this->kelas->nama_kelas);
        $labelKelas = ctype_digit($namaKelas) ? 'Kelas ' . $namaKelas : $namaKelas;
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            Informasi Kelas
        </x-slot>

        <div style="display: flex; flex-direction: column; gap: 16px;">

            {{-- Nama Kelas --}}
            <div style="display: grid; grid-template-columns: 200px minmax(0, 1fr); align-items: center; gap: 16px;">
                <label class="fi-fo-field-wrp-label" style="font-size: 14px;">
                    Nama Kelas
                </label>

                <input
                    type="text"
                    value="{{ $labelKelas }}"
                    readonly
                    class="fi-input"
                    style="width: 100%; font-size: 14px; font-weight: bold;"
                >
            </div>

            {{-- Jumlah Siswa --}}
            <div style="display: grid; grid-template-columns: 200px minmax(0, 1fr); align-items: center; gap: 16px;">
                <label class="fi-fo-field-wrp-label" style="font-size: 14px;">
                    Jumlah Siswa
                </label>

                <input
                    type="text"
                    value="{{ $this->kelas->siswas()->count() }} Siswa"
                    readonly
                    class="fi-input"
                    style="width: 100%; font-size: 14px; font-weight: bold;"
                >
            </div>

            {{-- Wali Kelas --}}
            <div style="display: grid; grid-template-columns: 200px minmax(0, 1fr); align-items: center; gap: 16px;">
                <label class="fi-fo-field-wrp-label" style="font-size: 14px;">
                    Wali Kelas
                </label>

                <input
                    type="text"
                    value="{{ $this->kelas->waliKelas?->name ?? 'Belum ditentukan' }}"
                    readonly
                    class="fi-input"
                    style="width: 100%; font-size: 14px; font-weight: bold;"
                >
            </div>

        </div>
    </x-filament::section>

    {{-- Card petunjuk (warna berbeda) --}}
    <div
        style="
            display: flex;
            align-items: center;
            gap: 12px;
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

        <span style="font-size: 14px; font-weight: 600; color: rgb(223, 138, 9);">
            Pilih Siswa untuk Pindah Kelas, Lulus, atau Naik Kelas.
        </span>
    </div>

    {{ $this->table }}
</x-filament-panels::page>