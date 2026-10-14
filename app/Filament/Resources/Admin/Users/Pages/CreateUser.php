<?php

namespace App\Filament\Resources\Admin\Users\Pages;

use App\Filament\Resources\Admin\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    public function getTitle(): string
    {
        return 'Tambah Pengguna';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan'),

            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }
     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
