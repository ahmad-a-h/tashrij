<?php

namespace App\Filament\Widgets;

use App\Models\Bundle;
use App\Models\CycleBundle;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Models\Cycle;


class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $currentCycleId = Cycle::where('end_date', '>', now())->value('id');
    
        $outOfStockBundles = CycleBundle::where('cycle_id', $currentCycleId)
            ->where('is_In_Stock', false) 
            ->with('bundle')
            ->get();
    
        $cards = [];
        foreach ($outOfStockBundles as $cycleBundle) {
            $cards[] = Card::make(
                'Out of Stock',
                $cycleBundle->bundle->name
            );
        }
    
        return $cards;
    }
    
    /**
     * Define a 4-column layout for the cards
     */
    protected function getColumns(): int
    {
        return 4; // Set 4 cards per row
    }
    
}
