<?php

namespace App\Modules\Inventory\Filament\Resources;

use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Filament\Resources\WarehouseResource\Pages;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Resources\Resource;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Warehouse Information')->schema([
                TextInput::make('code')
                    ->label('Warehouse Code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->placeholder('e.g. WH-01')
                    ->columnSpan(1),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                Textarea::make('address')
                    ->rows(3)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->default(true)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address')
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code', 'asc');
    }

    public static function getRelationManagers(): array
    {
        return [
            // Will add StockMovementsRelationManager here in a later phase
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => WarehouseResource\Pages\ListWarehouses::route('/'),
            'create' => WarehouseResource\Pages\CreateWarehouse::route('/create'),
            'edit'   => WarehouseResource\Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}