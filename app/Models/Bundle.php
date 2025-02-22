<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bundle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'deleted_at', 'capacity', 'price', 'id', 'is_active'];

    public function cycles()
    {
        return $this->belongsToMany(Bundle::class, 'cycle_bundles', 'bundle_id', 'cycle_id');
    }
    public function cycleBundles()
    {
        return $this->hasMany(CycleBundle::class);
    }
}
