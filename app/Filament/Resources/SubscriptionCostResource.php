<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Illuminate\Support\Facades\DB;
use App\Models\Subscription;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use App\Filament\Resources\SubscriptionCostResource\Pages;
use App\Filament\Resources\SubscriptionCostResource\Widgets\StatsOverview;

class SubscriptionCostResource extends Resource
{
    protected static ?string $model = Subscription::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Subscriptions';
    protected static ?string $navigationLabel = 'Cost Summary';
    protected static ?string $slug = 'subscription-costs';
    protected static ?int $navigationSort = 3;

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super-admin', 'admin']);
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super-admin', 'admin']);
    }

    public static function getEloquentQuery(): Builder
    {
        return Subscription::query()
            ->join('bundles', 'subscriptions.bundle_id', '=', 'bundles.id')
            ->select([
                'subscriptions.user_id',
                'subscriptions.cycle_id',
                DB::raw('CONCAT(subscriptions.user_id, "-", subscriptions.cycle_id) as id'),
                DB::raw('COUNT(subscriptions.id) as total_subscriptions'),
                DB::raw('SUM(bundles.price) as total_cost')
            ])
            ->with(['user', 'cycle'])
            ->whereIn('subscriptions.cycle_id', function ($query) {
                $query->select('id')
                    ->from('cycles')
                    ->where('end_date', '>', now());
            })
            ->where('subscriptions.is_approve', 1)
            ->groupBy(['subscriptions.user_id', 'subscriptions.cycle_id']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cycle.name')
                    ->label('Cycle')
                    ->sortable(),
                TextColumn::make('total_subscriptions')
                    ->label('Total Subscriptions')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('Total Cost')
                    ->formatStateUsing(fn ($state) => number_format(floatval($state)))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cycle_id')
                    ->relationship('cycle', 'name')
                    ->label('Cycle'),
            ])
            ->defaultSort('total_cost', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionCosts::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }

    public static function getRecordTitle(?Model $record): string
    {
        return $record?->user?->name . ' - ' . $record?->cycle?->name ?? 'Cost Summary';
    }
}