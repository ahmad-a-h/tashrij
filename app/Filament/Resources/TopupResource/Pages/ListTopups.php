<?php

namespace App\Filament\Resources\TopupResource\Pages;

use App\Filament\Resources\TopupResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;

class ListTopups extends ListRecords
{
    protected static string $resource = TopupResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Topup'),
        ];
    }
} 