<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'cycle_id', 'bundle_id', 'user_id', 'phone_number', 
        'verification_code', 'note', 'is_approve', 'is_karim', 'is_paid',
        'paid_with_balance',  // boolean to track if paid with account balance
        'transaction_id',     // reference to transaction record if needed
        'is_deleted'         // for soft deletes
    ];

    // ✅ Fixed: Corrected belongsTo method name
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function cycleBundle()
    {
        return $this->belongsTo(CycleBundle::class);
    }
 
    // Get price attribute dynamically
    public function getPriceAttribute()
    {
        return $this->bundle ? $this->bundle->price : null;
    }

    // Add scope to exclude deleted records by default
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('not_deleted', function ($builder) {
            $builder->where('is_deleted', false);
        });

        static::creating(function ($subscription) {
            Log::info('Creating subscription', $subscription->toArray());

            $bundle = CycleBundle::where('bundle_id', $subscription->bundle_id)->first();

            if (!$bundle) {
                Log::error('Subscription creation failed: Bundle not found.', ['bundle_id' => $subscription->bundle_id]);
                throw new \Exception('Bundle not found.');
            }

            // Reduce stock
            $bundle->update([
                'stock' => $bundle->stock - 1,
            ]);

            Log::info('Subscription successfully created', ['subscription_id' => $subscription->id]);
        });

        static::updating(function ($subscription) {
            Log::info('Updating subscription', [
                'id' => $subscription->id,
                'changes' => $subscription->getDirty()
            ]);
        });

        static::updated(function ($subscription) {
            Log::info('Subscription successfully updated', ['subscription_id' => $subscription->id]);
        });

        // When "deleting" a subscription (now soft delete)
        static::deleting(function ($subscription) {
            $user = $subscription->user;
            
            // Only process refund for non-admin users
            if ($subscription->paid_with_balance && !$user->hasRole(['admin', 'super-admin'])) {
                try {
                    DB::transaction(function () use ($subscription, $user) {
                        $price = $subscription->bundle->price;
                        
                        // Create a record of the refund
                        Topup::create([
                            'user_id' => $user->id,
                            'admin_id' => auth()->id(),
                            'amount' => $price,
                            'description' => "Refund for deleted subscription #{$subscription->id}",
                            'status' => 'completed'
                        ]);
                        
                        Log::info('Balance refunded for deleted subscription', [
                            'user_id' => $user->id,
                            'subscription_id' => $subscription->id,
                            'amount' => $price
                        ]);

                        // Show success notification
                        Notification::make()
                            ->title('Refund Successful')
                            ->success()
                            ->body("LBP " . number_format($price, 0, '.', ',') . " has been refunded to your balance.")
                            ->send();

                        // Mark as deleted instead of actually deleting
                        $subscription->is_deleted = true;
                        $subscription->save();
                    });
                } catch (\Exception $e) {
                    Log::error('Failed to process refund', [
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    Notification::make()
                        ->title('Refund Failed')
                        ->danger()
                        ->body('There was an error processing your refund. Please contact support.')
                        ->send();
                        
                    throw $e;
                }
            } else {
                // Just mark as deleted without refund
                $subscription->is_deleted = true;
                $subscription->save();
            }

            // Prevent actual deletion
            return false;
        });
    }

    // Method to force hard delete if needed
    public function forceDelete()
    {
        return parent::delete();
    }

    // Override the default delete method to use soft deletes
    public function delete()
    {
        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        $this->is_deleted = true;
        $this->save();

        $this->fireModelEvent('deleted', false);

        return true;
    }

    // Scope to include deleted records when needed
    public function scopeWithDeleted($query)
    {
        return $query->withoutGlobalScope('not_deleted');
    }

    // Scope to get only deleted records
    public function scopeOnlyDeleted($query)
    {
        return $query->withoutGlobalScope('not_deleted')->where('is_deleted', true);
    }

    /**
     * Process payment using user balance
     */
    public function payWithBalance()
    {
        $user = $this->user;
        $price = $this->price; // Assuming subscription has a price field
        
        if (!$user->hasEnoughBalance($price)) {
            return false;
        }
        
        DB::transaction(function () use ($user, $price) {
            // Deduct balance
            $user->balance -= $price;
            $user->save();
            
            // Update subscription
            $this->paid_with_balance = true;
            $this->transaction_id = 'BAL-' . time() . '-' . $this->id; // Optional: Add a transaction reference
            $this->save();
            
            Log::info('Payment processed with balance', [
                'user_id' => $user->id,
                'subscription_id' => $this->id,
                'amount' => $price,
                'transaction_id' => $this->transaction_id
            ]);
        });
        
        return true;
    }
}
