<?php

namespace App\Filament\Pages\Guru;

use App\Models\Siswa;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class DataSiswa extends Page
{
    protected string $view = 'filament.pages.guru.data-siswa';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'Menu';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Siswa';

    protected static ?string $title = 'Siswa';

    //hanya guru yang bisa mengakses halaman ini
    public static function canAccess(): bool
    {
        return Auth::check()
            && Auth::user()->role === 'guru';
    }
}