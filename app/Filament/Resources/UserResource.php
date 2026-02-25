<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Customers';
    protected static ?string $modelLabel = 'Customer';

    /**
     * SECURITY: Filter the list so Shopkeepers only see 'user' role accounts 
     * who have purchased from their specific shop.
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Admin sees everyone
        if ($user->role === 'admin' || $user->email === 'admin@me.com') {
            return parent::getEloquentQuery();
        }

        // Shopkeepers ONLY see 'user' role accounts linked via orders
        return parent::getEloquentQuery()
            ->where('role', 'user') 
            ->whereHas('orders', function ($query) use ($user) {
                $query->where('shopkeeper_id', $user->shopkeeper?->id);
            });
    }

    /**
     * PERMISSIONS: Disable Create, Edit, and Delete for Shopkeepers.
     */
    public static function canCreate(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('email')->email()->required(),
                Forms\Components\Select::make('role')
                    ->options([
                        'user' => 'User',
                        'shopkeeper' => 'Shopkeeper',
                        'admin' => 'Admin',
                    ])
                    ->visible(fn () => auth()->user()->role === 'admin'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('role')->badge(),
            ])
            ->actions([
                // Added ViewAction so Shopkeepers can still see details in read-only mode
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}