<?php

namespace App\Filament\Resources\CycleResource\Pages;

use App\Filament\Resources\CycleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCycles extends ListRecords
{
    protected static string $resource = CycleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
