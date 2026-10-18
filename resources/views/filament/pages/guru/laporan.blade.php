<x-filament-panels::page>
    {{-- Card 1: Filter + tombol cetak --}}
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
                        wire:model.live="dari"
                        :max="$sampai"
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
                        wire:model.live="sampai"
                        :min="$dari"
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        @if ($this->adaKelas())
            <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                <x-filament::button
                    tag="a"
                    :href="route('guru.laporan.cetak', ['dari' => $dari, 'sampai' => $sampai])"
                    target="_blank"
                    rel="noopener"
                    icon="heroicon-o-printer"
                >
                    Cetak Laporan
                </x-filament::button>
            </div>
        @endif
    </x-filament::section>

    {{-- Card 2: Tabel --}}
    {{ $this->table }}
</x-filament-panels::page>