<?php

namespace App\Modules\Inventory\Filament\Resources\ProductResource\Pages;

use App\Modules\Inventory\Filament\Resources\ProductResource;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\StockService;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Quick stock-check action from the edit page
            Action::make('viewStock')
                ->label('View Stock')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->modalHeading('Current Stock Levels')
                ->modalContent(function () {
                    $product = $this->record;
                    $rows    = $product->warehouses()
                        ->withPivot('quantity_on_hand')
                        ->get()
                        ->map(fn ($w) => [
                            'warehouse' => $w->name,
                            'stock'     => $w->pivot->quantity_on_hand,
                        ]);

                    return view('filament.modals.product-stock', [
                        'rows'    => $rows,
                        'product' => $product,
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

            Action::make('stockIn')
                ->label('Stock In')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->form([
                    Select::make('warehouse_id')
                        ->label('Warehouse')
                        ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->searchable(),

                    \Filament\Forms\Components\TextInput::make('quantity')
                        ->label('Quantity')
                        ->numeric()
                        ->minValue(0.0001)
                        ->required(),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    $warehouse = Warehouse::findOrFail($data['warehouse_id']);

                    app(StockService::class)->recordMovement(
                        product:   $this->record,
                        warehouse: $warehouse,
                        type:      'in',
                        quantity:  (float) $data['quantity'],
                        notes:     $data['notes'] ?? null,
                    );

                    Notification::make()
                        ->title('Stock recorded successfully')
                        ->success()
                        ->send();
                }),

            DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Product updated successfully';
    }
}