<?php

namespace App\Filament\Resources\SiswaResource\Pages;

use App\Filament\Resources\SiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSiswa extends CreateRecord
{
    protected static string $resource = SiswaResource::class;

    // --- TAMBAHKAN KODE INI ---
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
