<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'cycle_id', 'bundle_id', 'user_id', 'phone_number', 
        'verification_code', 'note', 'is_approve', 'is_karim', 'is_paid',
        'paid_with_balance',  // boolean to track if paid with account balance
        'transaction_id',     // reference to transaction record if needed
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

    // ✅ Fixed: Use 'creating' instead of 'created' to prevent inserting invalid records
    public static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            Log::info('Creating subscription', $subscription->toArray());

            $bundle = CycleBundle::where('bundle_id', $subscription->bundle_id)->first();

            if (!$bundle) {
                Log::error('Subscription creation failed: Bundle not found.', ['bundle_id' => $subscription->bundle_id]);
                throw new \Exception('Bundle not found.');
            }

            // if ($bundle->stock <= 0) {
            //     Log::error('Subscription creation failed: Insufficient bundle stock.', ['bundle_id' => $subscription->bundle_id]);
            //     throw new \Exception('Subscription creation failed due to insufficient bundle stock.');
            // }

            // Reduce stock
            $bundle->update([
                'stock' => $bundle->stock - 1,
            ]);

            Log::info('Subscription successfully created', ['subscription_id' => $subscription->id]);
        });

        static::updating(function ($subscription) {
            $changes = $subscription->getDirty();
            Log::info('Subscription update started', [
                'id' => $subscription->id,
                'changes' => $changes,
                'original' => $subscription->getOriginal(),
                'current_user' => auth()->user()->id,
                'is_admin' => auth()->user()->hasRole(['admin', 'super-admin']),
                'bundle_changed' => isset($changes['bundle_id'])
            ]);

            if (isset($changes['bundle_id'])) {
                Log::info('Bundle change detected', [
                    'old_bundle_id' => $subscription->getOriginal('bundle_id'),
                    'new_bundle_id' => $subscription->bundle_id,
                    'user_id' => $subscription->user_id
                ]);
            }
        });

        static::updated(function ($subscription) {
            Log::info('Subscription successfully updated', [
                'subscription_id' => $subscription->id,
                'final_state' => $subscription->toArray()
            ]);
        });
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
        
        // Deduct balance
        $user->balance -= $price;
        $user->save();
        
        $this->paid_with_balance = true;
        $this->save();
        
        return true;
    }
}
