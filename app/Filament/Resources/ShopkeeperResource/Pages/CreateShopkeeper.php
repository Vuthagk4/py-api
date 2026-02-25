<?php

namespace App\Filament\Resources\ShopkeeperResource\Pages;

use App\Filament\Resources\ShopkeeperResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShopkeeper extends CreateRecord
{
    protected static string $resource = ShopkeeperResource::class;

    /**
     * We removed the manual User::create logic here because 
     * the Select component in ShopkeeperResource handles it now.
     */
}