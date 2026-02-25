<?php

namespace App\Filament\Resources\ShopkeeperResource\Pages;

use App\Filament\Resources\ShopkeeperResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShopkeeper extends EditRecord
{
    protected static string $resource = ShopkeeperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
