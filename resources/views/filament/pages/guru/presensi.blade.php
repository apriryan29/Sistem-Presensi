<x-filament-panels::page>
    <style>
        .presensi-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            background: #fff;
            box-shadow: 0 1px 2px rgb(0 0 0 / .05);
            border: 1px solid rgb(3 7 18 / .06);
        }
        .dark .presensi-bar {
            background: rgb(var(--gray-900));
            border-color: rgb(255 255 255 / .1);
        }
        .presensi-info {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }
        .presensi-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            background: rgb(var(--primary-500) / .12);
            color: rgb(var(--primary-600));
            flex-shrink: 0;
        }
        .dark .presensi-icon { color: rgb(var(--primary-400)); }
        .presensi-icon svg { width: 1.5rem; height: 1.5rem; }
        .presensi-caption {
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: rgb(var(--gray-500));
        }
        .presensi-title {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            font-size: 1.125rem;
            font-weight: 600;
            color: rgb(var(--gray-950));
        }
        .dark .presensi-title { color: #fff; }
        .presensi-badge {
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            background: rgb(var(--success-500) / .12);
            color: rgb(var(--success-600));
        }
        .presensi-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .presensi-input {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.5rem;
            border: 1px solid rgb(var(--gray-300));
            background: #fff;
            color: rgb(var(--gray-950));
        }
        .presensi-input:focus {
            outline: 2px solid rgb(var(--primary-600));
            outline-offset: -1px;
        }
        .dark .presensi-input {
            background: rgb(255 255 255 / .05);
            border-color: rgb(255 255 255 / .2);
            color: #fff;
            color-scheme: dark;
        }
        @media (max-width: 640px) {
            .presensi-bar { flex-direction: column; align-items: stretch; }
            .presensi-controls { justify-content: space-between; }
            .presensi-input { flex: 1; min-width: 0; }
        }
    </style>

    <div
        class="presensi-bar"
        x-data="{
            get label() {
                if (! $wire.tanggal) return '-'
                const d = new Date($wire.tanggal + 'T00:00:00')
                return d.toLocaleDateString('id-ID', {
                    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
                })
            },
            get isToday() {
                return $wire.tanggal === new Date().toLocaleDateString('en-CA')
            },
            shift(days) {
                const d = new Date($wire.tanggal + 'T00:00:00')
                d.setDate(d.getDate() + days)
                $wire.set('tanggal', d.toLocaleDateString('en-CA'))
            },
            today() {
                $wire.set('tanggal', new Date().toLocaleDateString('en-CA'))
            },
        }"
    >
        <div class="presensi-info">
            <div class="presensi-icon">
                <x-filament::icon icon="heroicon-o-calendar-days" />
            </div>

            <div>
                <div class="presensi-caption">Tanggal Presensi</div>
                <div class="presensi-title">
                    <span x-text="label"></span>
                    <span class="presensi-badge" x-show="isToday" x-cloak>Hari ini</span>
                </div>
            </div>
        </div>

        <div class="presensi-controls">
            <x-filament::icon-button
                icon="heroicon-m-chevron-left"
                color="gray"
                label="Hari sebelumnya"
                x-on:click="shift(-1)"
            />

            <input
                type="date"
                class="presensi-input"
                wire:model.live="tanggal"
            />

            <x-filament::icon-button
                icon="heroicon-m-chevron-right"
                color="gray"
                label="Hari berikutnya"
                x-on:click="shift(1)"
            />

            <x-filament::button
                size="sm"
                outlined
                x-on:click="today()"
                x-bind:disabled="isToday"
            >
                Hari ini
            </x-filament::button>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>