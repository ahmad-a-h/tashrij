<?php

namespace App\Filament\Resources\SubscriptionCostResource\Pages;

use App\Filament\Resources\SubscriptionCostResource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\SubscriptionCostResource\Widgets\StatsOverview;

class ListSubscriptionCosts extends ListRecords
{
    protected static string $resource = SubscriptionCostResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }
} 