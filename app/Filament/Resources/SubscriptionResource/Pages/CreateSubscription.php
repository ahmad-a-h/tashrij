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

            // Deduct the balance
            $user->addBalance(-$bundle->price);
            
            // Set the paid_with_balance in the data
            $data['paid_with_balance'] = true;
            $data['transaction_id'] = 'BAL-' . time();  // We'll append the ID after creation
            $this->data = $data;  // Update the form data
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!auth()->user()->hasRole(['admin', 'super-admin'])) {
            $data['paid_with_balance'] = true;
            $data['transaction_id'] = 'BAL-' . time();  // We'll update this with the ID after creation
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        $subscription = $this->record;
        
        // Update transaction ID with the subscription ID if paid with balance
        if ($subscription->paid_with_balance) {
            $subscription->transaction_id = $subscription->transaction_id . '-' . $subscription->id;
            $subscription->save();
            
            // Log the transaction
            \Illuminate\Support\Facades\Log::info('Payment processed with balance', [
                'user_id' => auth()->id(),
                'subscription_id' => $subscription->id,
                'amount' => $subscription->bundle->price,
                'transaction_id' => $subscription->transaction_id
            ]);
        }
    }
}
































