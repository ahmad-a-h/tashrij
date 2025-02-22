<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use Filament\Pages\Actions;
use App\Models\Subscription;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SubscriptionResource;
use App\Filament\Resources\SubscriptionResource\Widgets\StatsOverview;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }
  
}
