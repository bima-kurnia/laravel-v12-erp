<?php

namespace App\Modules\Inventory\Filament\Resources;

use App\Modules\Inventory\Filament\Resources\ProductResource\Pages;
use App\Modules\Inventory\Models\Product;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon  = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Product Information')->schema([
                TextInput::make('sku')
                    ->label('SKU')
                    ->unique(ignoreRecord: true)
                    ->placeholder('Auto-generated if left blank')
                    ->maxLength(50),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Select::make('product_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Select::make('unit_of_measure_id')
                    ->label('Unit of Measure')
                    ->relationship('unitOfMeasure', 'name')
                    ->required()
                    ->preload(),

                Textarea::make('description')
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Pricing & Stock')->schema([
                TextInput::make('cost_price')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                TextInput::make('selling_price')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                TextInput::make('reorder_point')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Toggle::make('is_active')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                TextColumn::make('unitOfMeasure.abbreviation')
                    ->label('UoM'),

                TextColumn::make('selling_price')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('reorder_point')
                    ->label('Reorder At')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All Products')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),

                SelectFilter::make('product_category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload(),

                Filter::make('low_stock')
                    ->label('Low Stock Only')
                    ->query(fn (Builder $q) => $q->whereExists(
                        fn ($sub) => $sub
                            ->from('product_warehouse')
                            ->whereColumn('product_warehouse.product_id', 'products.id')
                            ->whereColumn('product_warehouse.quantity_on_hand', '<=', 'products.reorder_point')
                    ))
                    ->toggle(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}