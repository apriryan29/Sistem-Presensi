
<x-filament-panels::page>
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
                    value="Kelas {{ $this->kelas->nama_kelas }}"
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
                    value="{{ \App\Models\Siswa::where('kelas', (int) $this->kelas->nama_kelas)->count() }} Siswa"
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

    {{ $this->table }}
</x-filament-panels::page>
