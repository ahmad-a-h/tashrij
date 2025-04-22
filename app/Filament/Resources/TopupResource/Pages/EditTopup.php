<?php

namespace App\Filament\Resources\TopupResource\Pages;

use App\Models\User;
use App\Filament\Resources\TopupResource;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTopup extends EditRecord
{
    protected static string $resource = TopupResource::class;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Get the user
        $user = User::find($this->record->user_id);
        
        if ($user) {
            // If status changed to completed, add to balance
            if ($this->record->status === 'completed' && $this->record->wasChanged('status')) {
                $user->balance += $this->record->amount;
                $user->save();
            }
            // If status changed from completed to something else, subtract from balance
            elseif ($this->record->status !== 'completed' && 
                    $this->record->wasChanged('status') && 
                    $this->record->getOriginal('status') === 'completed') {
                $user->balance -= $this->record->amount;
                $user->save();
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
} 