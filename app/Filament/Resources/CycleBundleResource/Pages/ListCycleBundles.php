<?php

namespace App\Filament\Resources\CycleBundleResource\Pages;

use App\Filament\Resources\CycleBundleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCycleBundles extends ListRecords
{
    protected static string $resource = CycleBundleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
