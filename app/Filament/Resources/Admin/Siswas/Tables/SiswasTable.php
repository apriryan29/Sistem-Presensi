<?php

namespace App\Filament\Resources\Admin\Siswas\Tables;

use Dom\Text;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Siswa')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nis')
                    ->label('NIS')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('kelas')
                    ->label('Kelas')
                    ->formatStateUsing(fn ($state) => 'Kelas ' . $state)
                    ->sortable()
                    ->searchable(query: function ($query, string $search) {
                        $search = preg_replace('/^kelas\s*/i', '', trim($search));

                        $query->where('kelas', 'like', "%{$search}%");
                    }),
                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label(''),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->label('')
                    ->modalHeading('Hapus Siswa')
                    ->modalSubheading('Apakah Anda yakin ingin menghapus siswa ini? Tindakan ini tidak dapat dibatalkan.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
