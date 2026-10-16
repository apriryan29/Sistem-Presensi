<?php

namespace App\Filament\Resources\Admin\Users\Pages;

use App\Filament\Resources\Admin\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => $this->canDeleteRecord()),

            RestoreAction::make(),

            Action::make('kembali')
                ->label('Kembali')
                ->url(UserResource::getUrl('index'))
                ->color('gray'),
        ];
    }

    protected function canDeleteRecord(): bool
    {
        $record = $this->getRecord();

        // Tidak boleh menghapus akun sendiri.
        if ($record->is(Auth::user())) {
            return false;
        }

        // Tidak boleh menghapus Admin terakhir.
        if (
            $record instanceof User &&
            $record->role === 'admin' &&
            User::where('role', 'admin')->count() <= 1
        ) {
            return false;
        }

        return Auth::user()?->role === 'admin';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Perbarui'),

            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}