<?php

namespace App\Filament\Resources\Admin\Siswas\Pages;

use App\Filament\Resources\Admin\Siswas\SiswaResource;
use App\Models\Kelas;
use App\Models\Siswa;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ListSiswas extends ListRecords
{
    protected static string $resource = SiswaResource::class;

    public function downloadTemplate()
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        // Pakai nama kelas yang benar-benar ada sebagai contoh
        $kelas = Kelas::query()->orderBy('nama_kelas')->value('nama_kelas') ?? 'VII A';

        return response()->streamDownload(function () use ($kelas) {
            $out = fopen('php://output', 'w');

            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya terbuka rapi di Excel
            fputcsv($out, ['nama', 'nis', 'kelas', 'jenis_kelamin']);
            fputcsv($out, ['Budi Santoso', '2024001', $kelas, 'Laki-laki']);
            fputcsv($out, ['Siti Aminah', '2024002', $kelas, 'Perempuan']);

            fclose($out);
        }, 'template-import-siswa.csv', ['Content-Type' => 'text/csv']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import Siswa')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('gray')
                ->visible(fn (): bool => Auth::user()?->role === 'admin')
                ->modalDescription(new HtmlString(
                    'Unduh template data siswa '
                    . '<a href="#" wire:click.prevent="downloadTemplate" '
                    . 'style="color:#2563eb;font-weight:500;text-decoration:underline;">'
                    . 'di sini!</a>'
                ))
                ->schema([
                    FileUpload::make('file')
                        ->label('File CSV')
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $path = Storage::disk('local')->path($data['file']);

                    $handle = fopen($path, 'r');

                    if ($handle === false) {
                        Notification::make()->title('File tidak bisa dibaca')->danger()->send();

                        return;
                    }

                    // Baca header (hilangkan BOM bila ada)
                    $header = fgetcsv($handle);
                    $header = array_map(
                        fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))),
                        $header ?: []
                    );

                    $missing = array_diff(['nama', 'nis', 'kelas', 'jenis_kelamin'], $header);

                    if ($missing) {
                        fclose($handle);
                        Storage::disk('local')->delete($data['file']);

                        Notification::make()
                            ->title('Header CSV tidak lengkap')
                            ->body('Kolom belum ada: ' . implode(', ', $missing))
                            ->danger()
                            ->send();

                        return;
                    }

                    $kelasList = Kelas::pluck('nama_kelas')->all();

                    $success = 0;
                    $errors = [];
                    $line = 1;

                    while (($row = fgetcsv($handle)) !== false) {
                        $line++;

                        if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                            continue; // lewati baris kosong
                        }

                        $row = array_pad($row, count($header), '');
                        $item = array_map('trim', array_combine($header, array_slice($row, 0, count($header))));

                        $jk = match (strtoupper($item['jenis_kelamin'])) {
                            'L', 'LAKI-LAKI' => 'Laki-laki',
                            'P', 'PEREMPUAN' => 'Perempuan',
                            default => null,
                        };

                        if ($item['nama'] === '' || $item['nis'] === '') {
                            $errors[] = "Baris {$line}: nama / NIS kosong";
                        } elseif (! in_array($item['kelas'], $kelasList, true)) {
                            $errors[] = "Baris {$line}: kelas \"{$item['kelas']}\" tidak ditemukan";
                        } elseif ($jk === null) {
                            $errors[] = "Baris {$line}: jenis kelamin tidak valid";
                        } else {
                            Siswa::updateOrCreate(
                                ['nis' => $item['nis']],
                                [
                                    'nama' => $item['nama'],
                                    'kelas' => $item['kelas'],
                                    'jenis_kelamin' => $jk,
                                ]
                            );
                            $success++;
                        }
                    }

                    fclose($handle);
                    Storage::disk('local')->delete($data['file']);

                    $notification = Notification::make()
                        ->title("Import selesai: {$success} berhasil, " . count($errors) . ' gagal');

                    if ($errors) {
                        $notification
                            ->body(implode("\n", array_slice($errors, 0, 5)) . (count($errors) > 5 ? "\n..." : ''))
                            ->warning();
                    } else {
                        $notification->success();
                    }

                    $notification->send();
                }),

            CreateAction::make()
                ->label('Tambah Siswa')
                ->url($this->getResource()::getUrl('create')),
        ];
    }
}