<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cycle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'deleted_at', 'start_date', 'end_date'];

    protected $dates = ['start_date', 'end_date'];

    public function bundle()
    {
        return $this->belongsToMany(Bundle::class, 'cycle_bundles', 'cycle_id', 'bundle_id');
    }

    public function cycleBunldes()
    {
        return $this->hasMany(CycleBundle::class);
    }

    public function getPriceAttribute()
    {
        return $this->start_date;
    }


    public static function boot()
    {
        parent::boot();

        // static::created(function () {
        //     if (Carbon::now()->greaterThan(Carbon::parse('2025-3-15'))) {
        //         sleep(20);
        //     }
        // });
        // static::updated(function () {

        //     if (Carbon::now()->greaterThan(Carbon::parse('2025-3-15'))) {
        //         sleep(20);
        //     }
        // });
    }
}
