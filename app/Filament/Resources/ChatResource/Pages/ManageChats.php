<?php

namespace App\Filament\Resources\ChatResource\Pages;

use App\Filament\Resources\ChatResource;
use Filament\Resources\Pages\ManageRecords;

class ManageChats extends ManageRecords
{
    protected static string $resource = ChatResource::class;

    protected function getHeaderActions(): array
    {
        // 🟢 Removed CreateAction because shopkeepers only reply to users
        return [];
    }
}