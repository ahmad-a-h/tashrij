<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use App\Models\Bundle;
use App\Models\User;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected $messages = [
        'data.phone_number.unique' => 'This phone number already have a subscription in this cycle.',
    ];

    protected function beforeValidate(): void
    {
        $data = $this->data;
        
        // Get the original bundle ID from the database
        $originalBundleId = $this->record->bundle_id;
        $newBundleId = $data['bundle_id'] ?? null;
        
        Log::info('EditSubscription beforeValidate called', [
            'original_bundle_id' => $originalBundleId,
            'new_bundle_id' => $newBundleId,
            'user_id' => $this->record->user_id
        ]);
        
        // Check if bundle is being changed
        if ($newBundleId && $newBundleId != $originalBundleId) {
            Log::info('Bundle is being changed', [
                'old_bundle_id' => $originalBundleId,
                'new_bundle_id' => $newBundleId
            ]);

            $oldBundle = Bundle::find($originalBundleId);
            $newBundle = Bundle::find($newBundleId);

            Log::info('Bundle details', [
                'old_bundle' => $oldBundle ? ['id' => $oldBundle->id, 'price' => $oldBundle->price] : null,
                'new_bundle' => $newBundle ? ['id' => $newBundle->id, 'price' => $newBundle->price] : null
            ]);
            
            if (!$oldBundle || !$newBundle) {
                Log::error('Failed to find bundle(s)', [
                    'old_bundle_id' => $originalBundleId,
                    'new_bundle_id' => $newBundleId
                ]);
                
                Notification::make()
                    ->title('Error')
                    ->danger()
                    ->body('Failed to process bundle change. Please try again.')
                    ->persistent()
                    ->send();
                
                $this->halt();
                return;
            }
            
            // Calculate price difference
            $priceDifference = $newBundle->price - $oldBundle->price;
            
            Log::info('Price difference calculated', [
                'old_price' => $oldBundle->price,
                'new_price' => $newBundle->price,
                'difference' => $priceDifference
            ]);
            
            // Get the user
            $user = User::find($this->record->user_id);
            
            if (!$user) {
                Log::error('User not found', [
                    'user_id' => $this->record->user_id
                ]);
                
                Notification::make()
                    ->title('Error')
                    ->danger()
                    ->body('User not found. Please try again.')
                    ->persistent()
                    ->send();
                
                $this->halt();
                return;
            }
            
            // If new bundle is more expensive
            if ($priceDifference > 0) {
                Log::info('Checking user balance for upgrade', [
                    'user_id' => $user->id,
                    'current_balance' => $user->balance,
                    'required_difference' => $priceDifference
                ]);
                
                // Check if user has enough balance
                if (!$user->hasEnoughBalance($priceDifference)) {
                    $formattedBalance = number_format($user->balance, 2) . ' LBP';
                    $formattedDifference = number_format($priceDifference, 2) . ' LBP';
                    $formattedOldPrice = number_format($oldBundle->price, 2) . ' LBP';
                    $formattedNewPrice = number_format($newBundle->price, 2) . ' LBP';
                    
                    Log::warning('Insufficient balance for bundle upgrade', [
                        'user_id' => $user->id,
                        'balance' => $user->balance,
                        'required' => $priceDifference
                    ]);
                    
                    Notification::make()
                        ->title('Insufficient Balance')
                        ->warning()
                        ->body("Cannot change bundle. User's current balance is {$formattedBalance}.\n\nOld bundle price: {$formattedOldPrice}\nNew bundle price: {$formattedNewPrice}\nPrice difference needed: {$formattedDifference}")
                        ->persistent()
                        ->send();
                    
                    $this->halt();
                    return;
                }
                
                // Deduct the difference from user's balance
                Log::info('Deducting balance difference', [
                    'user_id' => $user->id,
                    'old_balance' => $user->balance,
                    'deduction' => $priceDifference
                ]);
                
                $user->addBalance(-$priceDifference);
                $user->save();
                
                Log::info('Balance updated after deduction', [
                    'user_id' => $user->id,
                    'new_balance' => $user->balance
                ]);
                
                // Show success notification
                $formattedNewBalance = number_format($user->balance, 2) . ' LBP';
                Notification::make()
                    ->title('Bundle Changed Successfully')
                    ->success()
                    ->body("Bundle has been upgraded and {$formattedDifference} LBP has been deducted. New balance: {$formattedNewBalance}")
                    ->persistent()
                    ->send();
                
            } elseif ($priceDifference < 0) {
                // If new bundle is cheaper, refund the difference
                Log::info('Refunding balance difference', [
                    'user_id' => $user->id,
                    'old_balance' => $user->balance,
                    'refund' => abs($priceDifference)
                ]);
                
                $user->addBalance(abs($priceDifference));
                $user->save();
                
                Log::info('Balance updated after refund', [
                    'user_id' => $user->id,
                    'new_balance' => $user->balance
                ]);
                
                // Show success notification
                $formattedRefund = number_format(abs($priceDifference), 2) . ' LBP';
                $formattedNewBalance = number_format($user->balance, 2) . ' LBP';
                Notification::make()
                    ->title('Bundle Changed Successfully')
                    ->success()
                    ->body("Bundle has been downgraded and {$formattedRefund} LBP has been refunded. New balance: {$formattedNewBalance}")
                    ->persistent()
                    ->send();
            }
            
            // Log the bundle change
            Log::info('Admin changed subscription bundle', [
                'subscription_id' => $this->record->id,
                'old_bundle_id' => $originalBundleId,
                'new_bundle_id' => $newBundleId,
                'price_difference' => $priceDifference,
                'admin_id' => auth()->user()->id
            ]);
        } else {
            Log::info('No bundle change detected in this update');
        }
    }

    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }
}
