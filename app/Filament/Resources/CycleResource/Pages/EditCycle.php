<?php

namespace App\Filament\Resources\CycleResource\Pages;

use App\Filament\Resources\CycleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCycle extends EditRecord
{
    protected static string $resource = CycleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
        protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }
}
