<?php

namespace App\Filament\Resources\CycleBundleResource\Pages;

use App\Filament\Resources\CycleBundleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCycleBundle extends EditRecord
{
    protected static string $resource = CycleBundleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
