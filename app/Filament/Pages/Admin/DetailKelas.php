<?php

namespace App\Filament\Pages\Admin;

use App\Models\Kelas;
use App\Models\Siswa;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DetailKelas extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.admin.detail-kelas';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedBuildingOffice2;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'detail-kelas/{record}';

    public Kelas $kelas;

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('Kembali')
                ->color('gray')
                ->url(ManajemenKelas::getUrl()),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            url('/admin/manajemen-kelas') => 'Kelas',
            '#' => 'Detail',
        ];
    }

    public function mount(int|string $record): void
    {
        $this->kelas = Kelas::with('waliKelas')
            ->findOrFail($record);
    }

    public function getTitle(): string
    {
        return 'Detail ' . self::labelKelas((string) $this->kelas->nama_kelas);
    }

    protected static function labelKelas(?string $nama): string
    {
        $nama = trim((string) $nama);

        return ctype_digit($nama) ? 'Kelas ' . $nama : $nama;
    }

    protected function namaKelasSaatIni(): string
    {
        return (string) $this->kelas->nama_kelas;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Siswa::query()
                    ->where('kelas', $this->namaKelasSaatIni())
            )
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->searchable(),

                TextColumn::make('kelas')
                    ->label('Kelas')
                    ->formatStateUsing(
                        fn ($state): string => self::labelKelas((string) $state)
                    ),
            ])
            ->defaultSort('nama')
            ->paginated([10, 25, 50])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('pindahKelas')
                        ->label('Naik / Pindah Kelas')
                        ->modalHeading('Naik / Pindah Kelas')
                        ->modalSubmitActionLabel('Ya, Pindahkan')
                        ->modalCancelActionLabel('Batal')
                        ->deselectRecordsAfterCompletion()
                        ->schema([
                            Select::make('kelas_tujuan')
                                ->label('Kelas Tujuan')
                                ->required()
                                ->searchable()
                                ->options(function (): array {
                                    return Kelas::query()
                                        ->where('nama_kelas', '!=', $this->namaKelasSaatIni())
                                        ->pluck('nama_kelas')
                                        ->sort(SORT_NATURAL)
                                        ->mapWithKeys(fn (string $nama): array => [
                                            $nama => self::labelKelas($nama),
                                        ])
                                        ->all();
                                })
                                ->default(function (): ?string {
                                    $nama = trim($this->namaKelasSaatIni());

                                    if (! ctype_digit($nama)) {
                                        return null;
                                    }

                                    $berikutnya = (string) ((int) $nama + 1);

                                    return Kelas::query()
                                        ->where('nama_kelas', $berikutnya)
                                        ->exists()
                                            ? $berikutnya
                                            : null;
                                }),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $sekarang = $this->namaKelasSaatIni();
                            $tujuan = (string) ($data['kelas_tujuan'] ?? '');

                            if ($tujuan === '' || $tujuan === $sekarang) {
                                Notification::make()
                                    ->title('Kelas tujuan tidak valid')
                                    ->body('Pilih kelas tujuan yang berbeda dari kelas saat ini.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            if (! Kelas::query()->where('nama_kelas', $tujuan)->exists()) {
                                Notification::make()
                                    ->title('Kelas tujuan tidak tersedia')
                                    ->body('Buat kelas ' . $tujuan . ' terlebih dahulu.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $jumlahSiswa = DB::transaction(
                                fn (): int => Siswa::query()
                                    ->whereIn('id', $records->modelKeys())
                                    ->where('kelas', $sekarang)
                                    ->update(['kelas' => $tujuan])
                            );

                            if ($jumlahSiswa === 0) {
                                Notification::make()
                                    ->title('Tidak ada siswa yang dipindahkan')
                                    ->body('Periksa kembali siswa yang dipilih.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Berhasil dipindahkan')
                                ->body(
                                    $jumlahSiswa . ' siswa dipindahkan ke '
                                    . self::labelKelas($tujuan) . '.'
                                )
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}