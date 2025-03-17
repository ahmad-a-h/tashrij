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
        'verification_code', 'note', 'is_approve', 'is_karim', 'is_paid'
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
    }
}
