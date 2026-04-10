<?php

namespace App\Modules\Inventory\Filament\Pages;

use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\StockService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class StockAdjustmentPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Stock Adjustment';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int    $navigationSort  = 3;
    protected static string  $view            = 'filament.pages.stock-adjustment';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Stock Adjustment')
                    ->description('Set the actual counted quantity. The system will calculate and record the difference automatically.')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(
                                Product::where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('warehouse_id', null)),

                        Select::make('warehouse_id')
                            ->label('Warehouse')
                            ->options(
                                Warehouse::where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->required()
                            ->searchable()
                            ->live(),

                        Placeholder::make('current_stock')
                            ->label('Current Stock on Record')
                            ->content(function (callable $get): string {
                                $productId   = $get('product_id');
                                $warehouseId = $get('warehouse_id');

                                if (! $productId || ! $warehouseId) {
                                    return '— select product and warehouse —';
                                }

                                $product   = Product::find($productId);
                                $warehouse = Warehouse::find($warehouseId);

                                if (! $product || ! $warehouse) {
                                    return '—';
                                }

                                $stock = app(StockService::class)->getStock($product, $warehouse);
                                return number_format($stock, 2);
                            }),

                        TextInput::make('new_quantity')
                            ->label('Actual Counted Quantity')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->suffix('units'),

                        Textarea::make('notes')
                            ->label('Reason for Adjustment')
                            ->required()
                            ->rows(3)
                            ->placeholder('e.g. Cycle count on 2025-01-10')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    // ↓ Removed getFormActions() entirely — caused the count(null) crash

    public function submit(): void
    {
        $data = $this->form->getState();

        $product   = Product::findOrFail($data['product_id']);
        $warehouse = Warehouse::findOrFail($data['warehouse_id']);

        $movement = app(StockService::class)->adjustStock(
            $product,
            $warehouse,
            (float) $data['new_quantity'],
            $data['notes']
        );

        if ($movement === null) {
            Notification::make()
                ->title('No adjustment needed')
                ->body('The counted quantity matches the current stock on record.')
                ->warning()
                ->send();
        } else {
            Notification::make()
                ->title('Stock adjusted successfully')
                ->body("New quantity on hand: {$data['new_quantity']}")
                ->success()
                ->send();
        }

        $this->form->fill();
    }
}