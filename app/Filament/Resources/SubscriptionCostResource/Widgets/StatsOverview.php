<?php

namespace App\Filament\Resources\SubscriptionCostResource\Widgets;

use App\Models\User;
use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $currentUser = auth()->user();
        
        return [
            Card::make('Your Current Balance', number_format($currentUser->balance, 2))
                ->description('Available credit')
                ->descriptionIcon('heroicon-s-credit-card')
                ->color('success'),

            Card::make('Your Total Subscriptions', Subscription::where('user_id', $currentUser->id)
                ->whereIn('cycle_id', function ($query) {
                    $query->select('id')
                        ->from('cycles')
                        ->where('end_date', '>', now());
                })
                ->where('is_approve', 1)
                ->count())
                ->description('Active subscriptions')
                ->descriptionIcon('heroicon-s-collection')
                ->color('primary'),

            Card::make('Your Total Cost', function() use ($currentUser) {
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
                
                return number_format($totalCost, 2);
            })
                ->description('Total spending')
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->color('warning'),
        ];
    }
} 