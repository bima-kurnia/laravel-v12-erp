<?php

namespace App\Modules\Inventory\Filament\Resources\WarehouseResource\Pages;

use App\Modules\Inventory\Filament\Resources\WarehouseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWarehouses extends ListRecords
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}