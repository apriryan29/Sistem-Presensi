<?php

namespace App\Filament\Pages\Admin;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class Profil extends Page
{
    protected string $view = 'filament.pages.admin.profil';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedUser;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Profil';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Profil Saya';
    public function getBreadcrumbs(): array
    {
        return [
            url('/admin/profil') => 'Profil',
            '#' => 'Profil Saya',
        ];
    }

    public ?array $data = [];

    public bool $isEditing = false;

    public function mount(): void
    {
        $this->form->fill([
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'jabatan' => Auth::user()->jabatan,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Profil')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->readOnly(fn (): bool => ! $this->isEditing),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->readOnly(fn (): bool => ! $this->isEditing),

                        TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->readOnly(fn (): bool => ! $this->isEditing),
                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->maxLength(255)
                            ->dehydrated(fn ($state) => $this->isEditing && filled($state))
                            ->hidden(fn (): bool => ! $this->isEditing),
                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password Baru')
                            ->password()
                            ->maxLength(255)
                            ->dehydrated(false)
                            ->hidden(fn (): bool => ! $this->isEditing)
                            ->same('password'),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }
    protected function getHeaderActions(): array
    {
        return [
            Action::make('ubah')
                ->label('Ubah')
                ->icon('heroicon-o-pencil-square')
                ->visible(fn (): bool => ! $this->isEditing)
                ->action(fn () => $this->isEditing = true),

            Action::make('simpan')
                ->label('Simpan')
                ->color('success')
                ->visible(fn (): bool => $this->isEditing)
                ->action(function (): void {
                    $data = $this->form->getState();

                    /** @var \App\Models\User $user */
                    $user = Auth::user();

                    $user->fill([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'jabatan' => $data['jabatan'],
                    ]);

                    $user->save();

                    $this->isEditing = false;

                    Notification::make()
                        ->title('Profil berhasil diperbarui')
                        ->success()
                        ->send();
                }),

            Action::make('batal')
                ->label('Batal')
                ->color('gray')
                ->visible(fn (): bool => $this->isEditing)
                ->action(function (): void {
                    $this->isEditing = false;
                    $this->mount();
                }),
        ];
    }
}