<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Bundle;
use Pages\ViewCycleBundle;
use App\Models\CycleBundle;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CycleBundleResource\Pages;
use App\Filament\Resources\CycleBundleResource\RelationManagers;

class CycleBundleResource extends Resource
{
    protected static ?string $model = CycleBundle::class;
    protected static ?string $navigationGroup = 'Subscriptions';
    protected static ?string $navigationIcon = 'heroicon-o-collection';
    
        public static function getEloquentQuery(): Builder
    {
    return static::getModel()::query()->orderBy("id", 'desc');
        
    }
    
    
    
    // public static function getEloquentQuery(): Builder
    // {
    // return static::getModel()::query()->whereHas('bundle', function ($query) {
    //     $query->where('is_active', 1);
    // })->whereHas('cycle', function ($query) {
    //     $query->where('end_date', '<=', now())->where('stock', '>', 0);
    // });
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('cycle_id')
                            ->required()
                            ->relationship('cycle', 'name', fn (Builder $query) => $query->where('end_date', '>', Carbon::now()))
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name} : {$record->start_date->format('d/m/Y')} - {$record->end_date->format('d/m/Y')}"),
                        Select::make('bundle_id')
                            ->relationship('bundle', 'capacity')
                            ->options(function () {
                                return Bundle::where('is_active',true)->pluck('name', 'id');
                            }),
                        TextInput::make('stock'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->searchable(),
                Tables\Columns\TextColumn::make('bundle_id')->searchable(),
                Tables\Columns\TextColumn::make('bundle.name')->searchable(),
                Tables\Columns\TextColumn::make('cycle_id')->searchable(),
                Tables\Columns\TextColumn::make('cycle.name')->searchable(),
                Tables\Columns\TextColumn::make('stock')->searchable()
            ])
            ->filters([
                Tables\Filters\Filter::make('ActiveCycleBundle')
                    ->default('ActiveCycleBundle') // Set the default value to 'active'
                    ->query(function (Builder $query) {
                        $query->whereHas('bundle', function ($query) {
                            $query->where('is_active', 1);
                        })->whereHas('cycle', function ($query) {
                            $query->where('end_date', '>=', now())->where('stock', '>', 0);
                        });
                    }),
                Tables\Filters\Filter::make('NotActiveCycleBundle')
                    ->query(function (Builder $query) {
                        $query->whereHas('cycle', function ($query) {
                            $query->where('end_date', '<=', now())->where('stock', '<=', 0);
                        });
                    }),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {

        return [
            'index' => Pages\ListCycleBundles::route('/'),
            'create' => Pages\CreateCycleBundle::route('/create'),
            'edit' => Pages\EditCycleBundle::route('/{record}/edit'),
        ];
    }
}
