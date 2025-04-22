<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topup extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'amount',
        'description',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        // When creating a new topup
        static::created(function ($topup) {
            if ($topup->status === 'completed') {
                $user = $topup->user;
                $user->balance += $topup->amount;
                $user->save();
            }
        });

        // Before updating, store the original amount
        static::updating(function ($topup) {
            if ($topup->status === 'completed') {
                // Store the original amount to use in updated event
                $topup->original_amount = $topup->getOriginal('amount');
            }
        });

        // After updating
        static::updated(function ($topup) {
            if ($topup->status === 'completed') {
                $user = $topup->user;
                // Subtract the old amount and add the new amount
                if (isset($topup->original_amount)) {
                    $user->balance -= $topup->original_amount;
                    $user->balance += $topup->amount;
                    $user->save();
                }
            }
        });

        // When deleting a topup
        static::deleting(function ($topup) {
            if ($topup->status === 'completed') {
                $user = $topup->user;
                $user->balance -= $topup->amount;
                $user->save();
            }
        });
    }

    /**
     * Get the user that owns the topup.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who created the topup.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
} 