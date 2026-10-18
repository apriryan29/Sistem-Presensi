<?php

namespace App\Filament\Pages\Guru;

use App\Models\Kelas;
use App\Models\Presensi as PresensiModel;
use App\Models\Siswa;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Laporan extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.guru.laporan';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedPrinter;

    protected static string|\UnitEnum|null $navigationGroup = 'Menu';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $title = 'Laporan';
    public function getBreadcrumbs(): array
    {
        return [
            url('/admin/laporan') => 'Laporan',
            '#' => 'Data',
        ];
    }

    public string $dari = '';

    public string $sampai = '';

    protected ?array $rekapMap = null;

    protected ?Kelas $kelasCache = null;

    protected const STATUS = [
        'hadir' => ['label' => 'Hadir', 'color' => 'success'],
        'alpa'  => ['label' => 'Alpa',  'color' => 'danger'],
        'sakit' => ['label' => 'Sakit', 'color' => 'warning'],
        'izin'  => ['label' => 'Izin',  'color' => 'info'],
    ];

    public function mount(): void
    {
        $this->dari = now()->startOfMonth()->toDateString();
        $this->sampai = now()->toDateString();
    }

    // Hanya guru yang bisa mengakses halaman ini
    public static function canAccess(): bool
    {
        return Auth::check()
            && Auth::user()->role === 'guru';
    }

        public function adaKelas(): bool
    {
        return $this->getKelasGuru() !== null;
    }
    protected function getKelasGuru(): ?Kelas
    {
        return $this->kelasCache ??= Kelas::query()
            ->where('wali_kelas_id', Auth::id())
            ->first();
    }

    protected function getTableQuery(): Builder
    {
        $kelas = $this->getKelasGuru();

        if (! $kelas) {
            return Siswa::query()->whereRaw('1 = 0');
        }

        return Siswa::query()->where('kelas', $kelas->nama_kelas);
    }

    /** @return array{0: string, 1: string} [dari, sampai], otomatis ditukar jika terbalik */
    protected function rentang(): array
    {
        $dari = filled($this->dari)
            ? $this->dari
            : now()->startOfMonth()->toDateString();

        $sampai = filled($this->sampai)
            ? $this->sampai
            : now()->toDateString();

        if ($dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        return [$dari, $sampai];
    }

    protected function rekapData(): array
    {
        if ($this->rekapMap !== null) {
            return $this->rekapMap;
        }

        [$dari, $sampai] = $this->rentang();

        $this->rekapMap = [];

        PresensiModel::query()
            ->whereIn('siswa_id', $this->getTableQuery()->select('id'))
            ->whereDate('tanggal', '>=', $dari)
            ->whereDate('tanggal', '<=', $sampai)
            ->selectRaw('siswa_id, status, COUNT(*) as total')
            ->groupBy('siswa_id', 'status')
            ->get()
            ->each(function ($baris) {
                $this->rekapMap[$baris->siswa_id][$baris->status] = (int) $baris->total;
            });

        return $this->rekapMap;
    }

    protected function jumlah(Siswa $record, string $status): int
    {
        return $this->rekapData()[$record->id][$status] ?? 0;
    }

    public function table(Table $table): Table
    {
        $kolomStatus = [];

        foreach (self::STATUS as $nilai => $info) {
            $kolomStatus[] = TextColumn::make("rekap_{$nilai}")
                ->label($info['label'])
                ->alignCenter()
                ->color($info['color'])
                ->state(fn (Siswa $record) => $this->jumlah($record, $nilai));
        }

        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                ...$kolomStatus,

                TextColumn::make('persen_hadir')
                    ->label('Persentase')
                    ->alignCenter()
                    ->state(function (Siswa $record) {
                        $total = 0;

                        foreach (array_keys(self::STATUS) as $status) {
                            $total += $this->jumlah($record, $status);
                        }

                        return $total > 0
                            ? round($this->jumlah($record, 'hadir') / $total * 100, 1)
                            : 0;
                    })
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->description(function ($state) {
                        $persen = max(0, min(100, (float) $state));

                        return new \Illuminate\Support\HtmlString(
                            '<div style="width:100%;height:6px;background:#e5e7eb;border-radius:999px;overflow:hidden;margin-top:6px;">
                                <div style="
                                    width:' . $persen . '%;
                                    height:100%;
                                    background:linear-gradient(to right, #000000, #ef4444, #f59e0b);
                                    border-radius:999px;
                                    transition:width 0.3s ease;
                                "></div>
                            </div>'
                        );
                    }),
            ])
            ->defaultSort('nama')
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Belum ada siswa di kelas ini');
    }
}