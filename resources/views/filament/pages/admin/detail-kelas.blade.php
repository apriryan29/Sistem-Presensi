<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">
                Informasi Kelas
            </x-slot>

            <div class="space-y-2">
                <p>
                    <strong>Nama Kelas:</strong>
                    Kelas {{ $this->kelas->nama_kelas }}
                </p>

                <p>
                    <strong>Wali Kelas:</strong>
                    {{ $this->kelas->waliKelas?->name ?? 'Belum ditentukan' }}
                </p>

                <p>
                    <strong>Jumlah Siswa:</strong>
                    {{ \App\Models\Siswa::where('kelas', (int) $this->kelas->nama_kelas)->count() }}
                </p>
            </div>
        </x-filament::section>
    </div>

    {{ $this->table }}
</x-filament-panels::page>