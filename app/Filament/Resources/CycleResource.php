<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Cycle;
use App\Models\Bundle;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\FormsComponent;
use Filament\Forms\Components\Card;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\CycleResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Relations\Relation;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\CycleResource\RelationManagers;
use Awcodes\FilamentTableRepeater\Components\TableRepeater;

class CycleResource extends Resource
{

    protected static ?string $model = Cycle::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Subscriptions';

    public static function form(Form $form): Form
    {
        $repeaterArr = array();
        $bundlesColl = Bundle::get()->where('is_active', 1);
        foreach ($bundlesColl as $bundle) {
            array_push($repeaterArr, ['bundle_id' => $bundle->id,'is_In_Stock' => false]);
        }
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan('full'),
                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('end_date')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                TableRepeater::make('Bundles')
                    ->relationship('cycleBunldes')
                    ->schema([
                        Forms\Components\Select::make('bundle_id')
                            ->relationship('bundle', 'capacity')
                            ->disabled(),
                        // Forms\Components\TextInput::make('stock')
                        //     ->numeric()
                        //     ->required(),
                        Forms\Components\Toggle::make('is_In_Stock')
                            ->label('In Stock') // Added label name
                            // ->required()
                            ->default(false),
                    ])
                    ->columnSpan('full')
                    ->disableItemCreation()
                    ->disableItemDeletion()
                    ->default(fn (callable $set) => $set('Bundles', $repeaterArr))

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('id')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('start_date')->searchable()
                    ->date(),
                Tables\Columns\TextColumn::make('end_date')->searchable()
                    ->date(),
            ])
            ->filters([

                Tables\Filters\Filter::make('ActiveCycle')
                    ->default('ActiveCycle')
                    ->query(function (Builder $query) {
                        $query->where('end_date', '>=', now());
                    }),
                Tables\Filters\Filter::make('NotActiveCycle')
                    ->query(function (Builder $query) {
                        $query->where('end_date', '<', now());
                    }),
                TrashedFilter::make(),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCycles::route('/'),
            'create' => Pages\CreateCycle::route('/create'),
            'view' => Pages\ViewCycle::route('/{record}'),
            'edit' => Pages\EditCycle::route('/{record}/edit'),
        ];
    }
}
