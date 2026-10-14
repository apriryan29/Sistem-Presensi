<?php

namespace App\Filament\Resources\Admin\Users\Tables;

use Dom\Text;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Peran Sistem')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('password')
                    ->label('Kata Sandi')
                    ->formatStateUsing(fn () => '••••••••')
                    ->sortable(false)
                    ->searchable(false),
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
                ->modalHeading('Hapus Pengguna')
                ->modalSubheading('Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
