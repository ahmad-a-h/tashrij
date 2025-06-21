<?php

namespace App\Observers;

use App\Models\Refund;
use Illuminate\Support\Facades\DB;

class RefundObserver
{
    public function creating(Refund $refund): void
    {
        $refund->processed_by = auth()->id();
    }

    public function created(Refund $refund): void
    {
        DB::transaction(function () use ($refund) {
            $user = $refund->user;
            $user->decrement('balance', $refund->amount);
        });
    }
} 