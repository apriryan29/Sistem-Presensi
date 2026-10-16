<?php

namespace App\Filament\Pages\Guru;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use BackedEnum;

class Presensi extends Page
{
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

    //hanya guru yang bisa mengakses halaman ini
    public static function canAccess(): bool
    {
        return Auth::check()
            && Auth::user()->role === 'guru';
    }
}
