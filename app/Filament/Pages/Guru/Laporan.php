<?php

namespace App\Filament\Pages\Guru;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use BackedEnum;

class Laporan extends Page
{
    protected string $view = 'filament.pages.guru.laporan';
    
    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedPrinter;

    protected static string|\UnitEnum|null $navigationGroup = 'Menu';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $title = 'Laporan';

    //hanya guru yang bisa mengakses halaman ini
    public static function canAccess(): bool
    {
        return Auth::check()
            && Auth::user()->role === 'guru';
    }
}
