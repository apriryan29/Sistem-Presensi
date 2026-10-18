<x-filament-panels::page>
    {{-- Card 1: Filter periode --}}
    <x-filament::section
        heading="Buat Laporan"
        icon="heroicon-o-calendar-days"
    >
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <div>
                <label for="dari" style="display: block; margin-bottom: 0.375rem; font-size: 0.875rem; font-weight: 500;">
                    Dari tanggal
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="date"
                        id="dari"
                        wire:model.live="tanggalAwal"
                        :max="$tanggalAkhir"
                    />
                </x-filament::input.wrapper>
            </div>

            <div>
                <label for="sampai" style="display: block; margin-bottom: 0.375rem; font-size: 0.875rem; font-weight: 500;">
                    Sampai tanggal
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="date"
                        id="sampai"
                        wire:model.live="tanggalAkhir"
                        :min="$tanggalAwal"
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        @if (! $this->periodeValid())
            <p style="margin-top: 0.75rem; font-size: 0.875rem; color: #dc2626;">
                Isi kedua tanggal, dan tanggal selesai tidak boleh lebih awal dari tanggal mulai.
            </p>
        @endif

        <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
            <x-filament::button
                color="gray"
                icon="heroicon-o-arrow-path"
                wire:click="resetTanggal"
            >
                Reset
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- Card 2: Daftar kelas --}}
    {{ $this->table }}
</x-filament-panels::page>