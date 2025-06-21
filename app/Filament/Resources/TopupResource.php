<?php

namespace App\Filament\Resources;

use App\Models\Topup;
use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\TopupResource\Pages;

class TopupResource extends Resource
{
    protected static ?string $model = Topup::class;
    protected static ?string $navigationIcon = 'heroicon-o-cash';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Topups';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->options(User::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        
                        TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('LBP')
                            ->mask(fn (TextInput\Mask $mask) => $mask
                                ->numeric()
                                ->thousandsSeparator(',')
                            ),
                        
                        TextInput::make('description')
                            ->label('Description')
                            ->placeholder('Reason for topup')
                            ->maxLength(255),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'completed' => 'Completed',
                                'pending' => 'Pending',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('completed')
                            ->required(),

                        TextInput::make('admin_id')
                            ->label('Admin ID')
                            ->default(fn () => auth()->id())
                            ->hidden(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),
                
                    TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => 'LBP ' . number_format($state, 0, '.', ','))
                    ->sortable(),
                
                TextColumn::make('admin.name')
                    ->label('Added By')
                    ->sortable(),
                
                TextColumn::make('description')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'danger' => 'cancelled',
                    ]),
                
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'completed' => 'Completed',
                        'pending' => 'Pending',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('user')
                    ->relationship('user', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListTopups::route('/'),
            'create' => Pages\CreateTopup::route('/create'),
            'edit' => Pages\EditTopup::route('/{record}/edit'),
        ];
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super-admin', 'admin']);
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super-admin', 'admin']);
    }

    protected static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    protected static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}