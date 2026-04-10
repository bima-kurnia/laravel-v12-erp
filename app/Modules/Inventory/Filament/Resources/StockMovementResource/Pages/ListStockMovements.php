<?php

namespace App\Modules\Inventory\Filament\Resources\StockMovementResource\Pages;

use App\Modules\Inventory\Filament\Resources\StockMovementResource;
use App\Modules\Inventory\Models\StockMovement;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    // No CreateAction — movements are system-generated only
    protected function getHeaderActions(): array
    {
        return [];
    }
}