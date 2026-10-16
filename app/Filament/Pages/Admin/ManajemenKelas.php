<?php

namespace App\Filament\Pages\Admin;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ManajemenKelas extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.admin.manajemen-kelas';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Menu';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Kelas';

    protected static ?string $title = 'Daftar Kelas';

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function canDeleteAny(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public function getBreadcrumbs(): array
    {
        return [
            url('/admin/manajemen-kelas') => 'Kelas',
            '#' => 'Daftar',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kelas')
                ->model(Kelas::class)
                ->createAnother(false)
                ->modalHeading('Tambah Kelas')
                ->modalSubmitActionLabel('Buat')
                ->modalCancelActionLabel('Batal')
                ->schema([
                    TextInput::make('nama_kelas')
                        ->label('Nama Kelas')
                        ->placeholder('Contoh: 1, atau Lulusan 2026')
                        ->required()
                        ->maxLength(30)
                        ->dehydrateStateUsing(
                            fn (?string $state): string => trim((string) $state)
                        )
                        ->unique(
                            table: Kelas::class,
                            column: 'nama_kelas',
                        ),

                    Select::make('wali_kelas_id')
                        ->label('Wali Kelas')
                        ->options(
                            User::query()
                                ->where('role', 'guru')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->nullable(),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        $kelasTable = (new Kelas())->getTable();
        $siswaTable = (new Siswa())->getTable();

        return $table
            ->query(
                Kelas::query()
                    ->select($kelasTable . '.*')
                    ->selectSub(
                        Siswa::query()
                            ->selectRaw('count(*)')
                            ->whereColumn(
                                $siswaTable . '.kelas',
                                $kelasTable . '.nama_kelas'
                            ),
                        'siswas_count'
                    )
                    ->orderByRaw($kelasTable . ".nama_kelas NOT REGEXP '^[0-9]+$'")
                    ->orderByRaw('CAST(' . $kelasTable . '.nama_kelas AS UNSIGNED)')
                    ->orderBy($kelasTable . '.nama_kelas')
            )
            ->columns([
                TextColumn::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->formatStateUsing(
                        fn ($state): string => ctype_digit(trim((string) $state))
                            ? 'Kelas ' . $state
                            : (string) $state
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('waliKelas.name')
                    ->label('Wali Kelas')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('siswas_count')
                    ->label('Jumlah Siswa')
                    ->numeric()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('lihat')
                    ->label('')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('success')
                    ->tooltip('Lihat Detail Kelas')
                    ->url(
                        fn (Kelas $record): string => DetailKelas::getUrl([
                            'record' => $record->getKey(),
                        ])
                    ),

                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Kelas')
                    ->modalHeading('Edit Kelas')
                    ->modalSubmitActionLabel('Simpan')
                    ->schema([
                        TextInput::make('nama_kelas')
                            ->label('Nama Kelas')
                            ->required()
                            ->maxLength(30)
                            ->dehydrateStateUsing(
                                fn (?string $state): string => trim((string) $state)
                            )
                            ->unique(
                                table: Kelas::class,
                                column: 'nama_kelas',
                                ignoreRecord: true,
                            ),

                        Select::make('wali_kelas_id')
                            ->label('Wali Kelas')
                            ->options(
                                User::query()
                                    ->where('role', 'guru')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),

                DeleteAction::make()
                    ->label('')
                    ->tooltip('Hapus Kelas')
                    ->modalHeading('Hapus Kelas')
                    ->modalDescription(
                        'Apakah Anda yakin ingin menghapus kelas ini?'
                    )
                    ->modalSubmitActionLabel('Hapus')
                    ->modalCancelActionLabel('Batal')
                    ->before(function (Kelas $record, DeleteAction $action): void {
                        if (Siswa::query()
                            ->where('kelas', (string) $record->nama_kelas)
                            ->exists()
                        ) {
                            Notification::make()
                                ->title('Kelas tidak bisa dihapus')
                                ->body('Masih ada siswa di kelas ini.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->paginated(false);
    }
}