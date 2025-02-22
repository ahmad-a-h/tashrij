<?php

namespace App\Filament\Resources\CycleResource\Pages;

use App\Filament\Resources\CycleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCycle extends CreateRecord
{
    protected static string $resource = CycleResource::class;
        protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }
}
