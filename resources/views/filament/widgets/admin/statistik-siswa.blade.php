<x-filament-widgets::widget>
    <x-filament::section
        heading="Statistik Siswa"
        description="Total {{ $total }} siswa di {{ count($kelas) }} kelas"
        icon="heroicon-o-academic-cap"
    >
        {{-- Dua pie chart --}}
        <div
            wire:ignore
            x-data="{
                async init() {
                    window.__chartJs ??= new Promise((resolve, reject) => {
                        if (window.Chart) return resolve();
                        const s = document.createElement('script');
                        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4';
                        s.onload = resolve;
                        s.onerror = reject;
                        document.head.appendChild(s);
                    });
                    await window.__chartJs;

                    Chart.defaults.color = getComputedStyle(this.$el).color;

                    const opsi = {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                    };

                    new Chart(this.$refs.jk, {
                        type: 'pie',
                        data: {
                            labels: ['Laki-laki', 'Perempuan'],
                            datasets: [{
                                data: @js([$laki, $perempuan]),
                                backgroundColor: ['#3b82f6', '#ec4899'],
                            }],
                        },
                        options: opsi,
                    });

                    const kelas = @js($kelas);
                    new Chart(this.$refs.kelas, {
                        type: 'pie',
                        data: {
                            labels: kelas.map(k => k.label),
                            datasets: [{
                                data: kelas.map(k => k.total),
                                backgroundColor: kelas.map((k, i) =>
                                    `hsl(${Math.round(i * 360 / Math.max(kelas.length, 1))}, 70%, 55%)`),
                            }],
                        },
                        options: opsi,
                    });
                },
            }"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;"
        >
            <div>
                <p style="text-align: center; font-weight: 600; margin-bottom: 0.5rem;">Jenis Kelamin</p>
                <div style="position: relative; height: 280px;">
                    <canvas x-ref="jk"></canvas>
                </div>
            </div>

            <div>
                <p style="text-align: center; font-weight: 600; margin-bottom: 0.5rem;">Siswa per Kelas</p>
                <div style="position: relative; height: 280px;">
                    <canvas x-ref="kelas"></canvas>
                </div>
            </div>
        </div>

        {{-- Rincian --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-top: 2rem;">
            <div>
                <p style="font-weight: 600; margin-bottom: 0.5rem;">Rincian Jenis Kelamin</p>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid rgba(128,128,128,.4);">
                            <th style="padding: 6px;">Jenis Kelamin</th>
                            <th style="padding: 6px; text-align: right;">Jumlah</th>
                            <th style="padding: 6px; text-align: right;">Persen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 6px;">Laki-laki</td>
                            <td style="padding: 6px; text-align: right;">{{ $laki }}</td>
                            <td style="padding: 6px; text-align: right;">{{ $persenLaki }}%</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px;">Perempuan</td>
                            <td style="padding: 6px; text-align: right;">{{ $perempuan }}</td>
                            <td style="padding: 6px; text-align: right;">{{ $persenPerempuan }}%</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: 600; border-top: 1px solid rgba(128,128,128,.4);">
                            <td style="padding: 6px;">Total</td>
                            <td style="padding: 6px; text-align: right;">{{ $total }}</td>
                            <td style="padding: 6px; text-align: right;">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div>
                <p style="font-weight: 600; margin-bottom: 0.5rem;">Rincian per Kelas</p>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid rgba(128,128,128,.4);">
                            <th style="padding: 6px;">Kelas</th>
                            <th style="padding: 6px; text-align: right;">L</th>
                            <th style="padding: 6px; text-align: right;">P</th>
                            <th style="padding: 6px; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kelas as $k)
                            <tr>
                                <td style="padding: 6px;">{{ $k['label'] }}</td>
                                <td style="padding: 6px; text-align: right;">{{ $k['laki'] }}</td>
                                <td style="padding: 6px; text-align: right;">{{ $k['perempuan'] }}</td>
                                <td style="padding: 6px; text-align: right;">{{ $k['total'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 6px; text-align: center;">Belum ada data kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (count($kelas))
                        <tfoot>
                            <tr style="font-weight: 600; border-top: 1px solid rgba(128,128,128,.4);">
                                <td style="padding: 6px;">Total</td>
                                <td style="padding: 6px; text-align: right;">{{ $laki }}</td>
                                <td style="padding: 6px; text-align: right;">{{ $perempuan }}</td>
                                <td style="padding: 6px; text-align: right;">{{ $total }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>