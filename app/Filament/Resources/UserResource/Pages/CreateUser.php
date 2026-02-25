<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Hash the password before saving to the database.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Encrypt the password so Laravel can verify it at login
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        // Ensure the role is set correctly
        $data['role'] = $data['role'] ?? 'shopkeeper';

        return $data;
    }
}