<?php

namespace App\Filament\Resources\TopupResource\Pages;

use App\Models\User;
use App\Filament\Resources\TopupResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTopup extends CreateRecord
{
    protected static string $resource = TopupResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['admin_id'] = auth()->id();
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Get the user and update their balance
        $user = User::find($this->record->user_id);
        if ($user && $this->record->status === 'completed') {
            $user->balance += $this->record->amount;
            $user->save();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
} 