<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopkeeperResource\Pages;
use App\Models\Shopkeeper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ShopkeeperResource extends Resource
{
    protected static ?string $model = Shopkeeper::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Shopkeeper Account & Profile')
                    ->schema([
                        // FIXED: Cleaned up the Select and createOptionForm brackets
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Link to User Account')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique('users', 'email'),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->dehydrated(fn ($state) => filled($state)),
                                Forms\Components\Hidden::make('role')
                                    ->default('shopkeeper'),
                            ]),

                        Forms\Components\TextInput::make('shop_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('telegram_username')
                            ->label('Telegram Username')
                            ->prefix('@'),

                        Forms\Components\FileUpload::make('image')
                            ->label('Shop Logo')
                            ->directory('shop-logos')
                            ->image(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Logo')
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('shop_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('telegram_username')
                    ->label('Telegram')
                    ->icon('heroicon-m-paper-airplane')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean()
                    ->label('Verified'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    // --- PERMISSIONS ---

    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShopkeepers::route('/'),
            'create' => Pages\CreateShopkeeper::route('/create'),
            'edit' => Pages\EditShopkeeper::route('/{record}/edit'),
        ];
    }
}