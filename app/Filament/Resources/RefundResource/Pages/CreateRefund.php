<?php

namespace App\Filament\Resources\RefundResource\Pages;

use App\Filament\Resources\RefundResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class CreateRefund extends CreateRecord
{
    protected static string $resource = RefundResource::class;
    
    // Override the default validation behavior
    protected function beforeCreate(): void
    {
        $state = $this->form->getState();
        $userId = $state['user_id'] ?? null;
        $amount = $state['amount'] ?? 0;
        
        if ($userId && $amount) {
            $user = User::find($userId);
            
            if ($user && $amount > $user->balance) {
                $message = "The refund amount (LBP " . number_format($amount, 0, '.', ',') . 
                          ") exceeds the user's available balance (LBP " . 
                          number_format($user->balance, 0, '.', ',') . ").";
                
                Notification::make('amount-exceeded')
                    ->warning()
                    ->title('Amount Exceeds Balance')
                    ->body($message)
                    ->persistent()
                    ->actions([
                        Action::make('cancel')
                            ->label('Cancel')
                            ->color('secondary'),
                    ])
                    ->send();
                    
                $this->halt();
            }
        }
    }

    // We don't need this anymore as we're using beforeCreate instead
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['processed_by'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
