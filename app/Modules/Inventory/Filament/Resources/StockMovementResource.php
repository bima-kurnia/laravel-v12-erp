<?php

namespace App\Modules\Inventory\Filament\Resources;

use App\Modules\Inventory\Filament\Resources\StockMovementResource\Pages;
use App\Modules\Inventory\Models\StockMovement;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Resources\Resource;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    protected static ?string $navigationIcon  = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int    $navigationSort  = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in', 'transfer_in', 'return_in'       => 'success',
                        'out', 'transfer_out', 'return_out'     => 'danger',
                        'adjustment'                            => 'warning',
                        default                                 => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in'           => 'Stock In',
                        'out'          => 'Stock Out',
                        'transfer_in'  => 'Transfer In',
                        'transfer_out' => 'Transfer Out',
                        'return_in'    => 'Return In',
                        'return_out'   => 'Return Out',
                        'adjustment'   => 'Adjustment',
                        default        => ucfirst($state),
                    }),

                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('creator.name')
                    ->label('Created By'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'in'           => 'Stock In',
                        'out'          => 'Stock Out',
                        'transfer_in'  => 'Transfer In',
                        'transfer_out' => 'Transfer Out',
                        'return_in'    => 'Return In',
                        'return_out'   => 'Return Out',
                        'adjustment'   => 'Adjustment',
                    ]),

                SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label('Warehouse'),

                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Product')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
        ];
    }
}