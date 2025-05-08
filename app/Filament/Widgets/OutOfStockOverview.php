<?php

namespace App\Filament\Widgets;

use App\Models\Cycle;
use App\Models\CycleBundle;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class OutOfStockOverview extends BaseWidget
{
    // Set a lower sort order to display after StatsOverview
    protected static ?int $sort = 2;

    protected function getCards(): array
    {
        $cards = [];
        
        // Get out of stock bundles
        $currentCycleId = Cycle::where('end_date', '>', now())->value('id');
        $outOfStockBundles = CycleBundle::where('cycle_id', $currentCycleId)
            ->where('is_in_stock', false)
            ->with('bundle')
            ->get();

        // If there are no out-of-stock items, show a single "All in Stock" card
        if ($outOfStockBundles->isEmpty()) {
            return [
                Card::make('Stock Status', 'All Bundles Available')
                    ->description('All bundles are currently in stock')
                    ->descriptionIcon('heroicon-s-check-circle')
                    ->color('success')
            ];
        }

        // Add a card for each out-of-stock bundle
        foreach ($outOfStockBundles as $cycleBundle) {
            $cards[] = Card::make('Out of Stock', $cycleBundle->bundle->name)
                ->description('Currently unavailable')
                ->descriptionIcon('heroicon-s-x-circle')
                ->color('danger');
        }

        return $cards;
    }

    /**
     * Define columns layout for different screen sizes
     */
    protected function getColumns(): int
    {
        // Since the parent class only accepts int, we'll use a fixed number of columns
        // that works well for most screens
        return 4; // Show 4 columns
    }

    /**
     * Set the maximum width of the widget
     */
    protected static function getMaxWidth(): string
    {
        return 'full';  // Changed to full width to accommodate more columns
    }
}