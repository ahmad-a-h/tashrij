<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Cycle;
use App\Models\Bundle;
use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $currentUser = auth()->user();
        $cards = [];
        
        // Add user stats
        $cards[] = Card::make('Balance', 'LBP ' . number_format($currentUser->balance, 0, '.', ','))
    ->description('Raw value: ' . $currentUser->balance) // Add this line to see the raw value
    ->descriptionIcon('heroicon-s-credit-card')
    ->chart([7, 3, 4, 5, 6, 3, 5, 3])
    ->color('success');

        $activeSubscriptions = Subscription::where('user_id', $currentUser->id)
            ->whereIn('cycle_id', function ($query) {
                $query->select('id')
                    ->from('cycles')
                    ->where('end_date', '>', now());
            })
            ->where('is_approve', 1)
            ->count();

        $cards[] = Card::make('Subscriptions', $activeSubscriptions)
            ->description('Active plans')
            ->descriptionIcon('heroicon-s-collection')
            ->chart([2, 3, 3, 3, 4, 3, 4, $activeSubscriptions])
            ->color('primary');

        $totalCost = Subscription::query()
            ->join('bundles', 'subscriptions.bundle_id', '=', 'bundles.id')
            ->where('subscriptions.user_id', $currentUser->id)
            ->where('subscriptions.is_approve', 1)
            ->whereIn('subscriptions.cycle_id', function ($query) {
                $query->select('id')
                    ->from('cycles')
                    ->where('end_date', '>', now());
            })
            ->sum('bundles.price');

        $cards[] = Card::make('Total Cost', number_format($totalCost, 2))
            ->description('Current cycle')
            ->descriptionIcon('heroicon-s-currency-dollar')
            ->chart([0, 2, 4, 6, 8, 10, 8, $totalCost])
            ->color('warning');

        return $cards;
    }
    
    /**
     * Define columns layout for different screen sizes
     */
    // protected function getColumns(): int|array
    // {
    //     return [
    //         'sm' => 1,    // 1 column on mobile
    //         'md' => 2,    // 2 columns on tablet
    //         'lg' => 3,    // 3 columns on desktop
    //     ];
    // }
}
