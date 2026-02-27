<?php


namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\UserResource;


use Filament\Widgets\TableWidget as BaseWidget;

class NormalUsersTable extends BaseWidget
{
    protected static ?string $heading = 'Normal Users';
    protected int|string|array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(User::where('role', 'user'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('role')->badge(),
            ])
            ->actions([
                // 🟢 This is your "Permission Menu" action
                Tables\Actions\Action::make('permissions')
                    ->label('Permission')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    // This opens a modal with the toggles directly in the table
                    ->form([
                        Forms\Components\Section::make('Feature Access')
                            ->description('Enable or disable menus for this shopkeeper')
                            ->schema([
                                Forms\Components\Toggle::make('can_manage_products')
                                    ->label('Products Menu'),
                                Forms\Components\Toggle::make('can_manage_categories')
                                    ->label('Categories Menu'),
                                Forms\Components\Toggle::make('can_manage_orders')
                                    ->label('Orders Menu'),
                            ])->columns(2),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update($data);
                    }),

                Tables\Actions\EditAction::make()
                    ->url(fn(User $record) => UserResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}