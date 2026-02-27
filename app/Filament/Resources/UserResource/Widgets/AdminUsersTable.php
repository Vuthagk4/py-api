<?php

namespace App\Filament\Resources\UserResource\Widgets;


use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\UserResource;

use Filament\Widgets\TableWidget as BaseWidget;

class AdminUsersTable extends BaseWidget
{
    protected static ?string $heading = 'Admin & Shopkeepers';
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(User::whereIn('role', ['admin', 'shopkeeper']))
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('role')->badge(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn(User $record) => UserResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}