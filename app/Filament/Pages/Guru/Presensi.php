<?php

namespace App\Filament\Pages\Guru;

use App\Models\Kelas;
use App\Models\Presensi as PresensiModel;
use App\Models\Siswa;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Presensi extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.guru.presensi';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Menu';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Presensi';

    protected static ?string $title = 'Presensi Siswa';
    public function getBreadcrumbs(): array
    {
        return [
            url('/admin/presensi') => 'Presensi',
            '#' => 'Data',
        ];
    }
    public string $tanggal = '';

    protected ?array $presensiMap = null;

    protected const STATUS = [
        'hadir' => 'Hadir',
        'alpa'  => 'Alpa',
        'sakit' => 'Sakit',
        'izin'  => 'Izin',
    ];

    public function mount(): void
    {
        $this->tanggal = now()->toDateString();
    }

    public static function canAccess(): bool
    {
        return Auth::check()
            && Auth::user()->role === 'guru';
    }

    protected function getKelasGuru(): ?Kelas
    {
        return Kelas::query()
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

    public function updatedTanggal(): void
    {
        $this->presensiMap = null;
        $this->resetTable();
    }

    protected function presensiData(): array
    {
        return $this->presensiMap ??= PresensiModel::query()
            ->whereDate('tanggal', $this->tanggal)
            ->get(['siswa_id', 'status', 'keterangan'])
            ->keyBy('siswa_id')
            ->map(fn ($p) => [
                'status'     => $p->status,
                'keterangan' => $p->keterangan,
            ])
            ->all();
    }

    protected function simpanStatus(Siswa $record, string $status, bool $dicentang): void
    {
        $query = PresensiModel::query()
            ->where('siswa_id', $record->id)
            ->whereDate('tanggal', $this->tanggal);

        if ($dicentang) {
            PresensiModel::updateOrCreate(
                ['siswa_id' => $record->id, 'tanggal' => $this->tanggal],
                ['status' => $status]
            );
        } else {
            $query->where('status', $status)->delete();
        }

        $this->presensiMap = null;
    }

    public function table(Table $table): Table
    {
        $kolomStatus = [];

        foreach (self::STATUS as $nilai => $label) {
            $kolomStatus[] = CheckboxColumn::make("status_{$nilai}")
                ->label($label)
                ->alignCenter()
                ->getStateUsing(fn (Siswa $record) =>
                    ($this->presensiData()[$record->id]['status'] ?? null) === $nilai
                )
                ->updateStateUsing(function (Siswa $record, $state) use ($nilai) {
                    $this->simpanStatus($record, $nilai, (bool) $state);

                    return $state;
                })
                ->afterStateUpdated(fn ($livewire) => $livewire->dispatch('$refresh'));
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

                TextInputColumn::make('keterangan_presensi')
                    ->label('Keterangan')
                    ->placeholder('Opsional')
                    ->getStateUsing(fn (Siswa $record) =>
                        $this->presensiData()[$record->id]['keterangan'] ?? null
                    )
                    ->updateStateUsing(function (Siswa $record, $state) {
                        $ada = PresensiModel::query()
                            ->where('siswa_id', $record->id)
                            ->whereDate('tanggal', $this->tanggal)
                            ->exists();

                        if (! $ada) {
                            Notification::make()
                                ->title('Pilih status presensi terlebih dahulu')
                                ->warning()
                                ->send();

                            return null;
                        }

                        PresensiModel::query()
                            ->where('siswa_id', $record->id)
                            ->whereDate('tanggal', $this->tanggal)
                            ->update(['keterangan' => $state]);

                        $this->presensiMap = null;

                        return $state;
                    }),
            ])
            ->defaultSort('nama')
            ->paginated([10, 25, 50]);
    }
}