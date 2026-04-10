<?php

namespace App\Modules\Inventory\Filament\Resources\ProductResource\Pages;

use App\Modules\Inventory\Filament\Resources\ProductResource;
use App\Modules\Inventory\Services\ProductService;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['sku'])) {
            $data['sku'] = app(ProductService::class)->generateSku();
        }
        return $data;
    }
}