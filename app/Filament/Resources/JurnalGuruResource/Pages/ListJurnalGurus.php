<?php

namespace App\Filament\Resources\JurnalGuruResource\Pages;

use App\Filament\Resources\JurnalGuruResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJurnalGurus extends ListRecords
{
    protected static string $resource = JurnalGuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
