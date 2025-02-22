<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = ['cycle_id', 'bundle_id', 'user_id', 'phone_number', 'verification_code', 'note', 'is_approve', 'is_karim', 'is_paid'];

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
    public function user()
    {
        return $this->belongsto(User::class);
    }
    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }
    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function getPriceAttribute()
    {
        if ($this->bundle) {
            return $this->bundle->price;
        }

        return null; // or a default value if necessary
    }
    public function CycleBundle()
    {
        return $this->belongsTo(CycleBundle::class);
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($subscription) {
            // if (Carbon::now()->greaterThan(Carbon::parse('2025-2-15'))) {
            //     sleep(20);
            // }
            $bundle = CycleBundle::where('bundle_id', $subscription->bundle_id)->first();

            if (!$bundle) {
                return redirect()->back()->with('error', 'Bundle not found.');
            }

            if ($bundle->stock <= 0) {
                $subscription->delete();
                return redirect()->back()->with('error', 'Subscription creation failed due to insufficient bundle stock.');
            } else {
                $bundle->update([
                    'stock' => $bundle->stock - 1,
                ]);
            }
        });
        // static::updated(function ($customer) {

        //     if (Carbon::now()->greaterThan(Carbon::parse('2025-2-15'))) {
        //         sleep(20);
        //     }
        // });
    }
}
