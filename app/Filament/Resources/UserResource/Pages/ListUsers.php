<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // ✅ Put widgets in header ABOVE everything
    protected function getHeaderWidgets(): array
    {
        return [
            UserResource\Widgets\NormalUsersTable::class,
            UserResource\Widgets\AdminUsersTable::class,
        ];
    }
}