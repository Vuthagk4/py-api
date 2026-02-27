<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $shopId = auth()->user()->shopkeeper?->id;

        if (!$shopId) {
            throw new \Exception("You do not have a Shopkeeper profile assigned to your account.");
        }

        $data['shopkeeper_id'] = $shopId;

        return $data;
    }
}
