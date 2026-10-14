<?php

namespace App\Filament\Pages\Admin;

use App\Models\Kelas;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\User;
use App\Filament\Pages\Admin\DetailKelas;

class ManajemenKelas extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.admin.manajemen-kelas';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Siswa';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Kelas';

    protected static ?string $title = 'Daftar Kelas';
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
                        ->placeholder('Contoh: 1')
                        ->required()
                        ->unique(table: Kelas::class, column: 'nama_kelas'),

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
        return $table
            ->query(Kelas::query()->withCount('siswas'))
            ->columns([
                TextColumn::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->formatStateUsing(fn ($state) => 'Kelas ' . $state)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('waliKelas.name')
                    ->label('Wali Kelas')
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
            ])
            ->defaultSort('nama_kelas')
            ->paginated(false);
    }
}