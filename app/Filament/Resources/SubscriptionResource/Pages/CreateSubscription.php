<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected $messages = [
        'data.phone_number.unique' => 'This phone number already have a subscription in this cycle.',
    ];
    
    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();
        
        // Get the user and bundle
        $user = auth()->user();
        $bundle = \App\Models\Bundle::find($data['bundle_id']);
        
        // Skip balance check for admin and super-admin
        if (!$user->hasRole(['admin', 'super-admin'])) {
            // Check if user has enough balance
            if (!$user->hasEnoughBalance($bundle->price)) {
                $formattedBalance = number_format($user->balance, 2) . ' LBP';
                $formattedPrice = number_format($bundle->price, 2) . ' LBP';
                
                Notification::make()
                    ->title('Insufficient Balance')
                    ->warning()
                    ->body("Your current balance is {$formattedBalance}, and the bundle price is {$formattedPrice}. Please top up your balance to continue.")
                    ->persistent()
                    ->send();

                $this->halt();
            }

            // Deduct the balance if validation passes
            $user->addBalance(-$bundle->price);
        }
    }
}
































