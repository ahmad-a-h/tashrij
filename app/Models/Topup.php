<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topup extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'description',
        'admin_id', // ID of admin who created the topup
        'status'    // e.g., 'pending', 'completed', 'failed'
    ];

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