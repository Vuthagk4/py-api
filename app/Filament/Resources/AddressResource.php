<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
use App\Filament\Resources\AddressResource\RelationManagers;
use App\Models\Address;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 5;

    //hide
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        // Admin never sees these
        if ($user->role === 'admin') return false;

        // 🟢 DYNAMIC CHECK: Check if the Admin enabled this feature for the Shopkeeper
        if ($user->role === 'shopkeeper') {
            return match (static::class) {
                ProductResource::class => (bool) $user->can_manage_products,
                OrderResource::class => (bool) $user->can_manage_orders,
                CategoryResource::class => (bool) $user->can_manage_categories,
                AddressResource::class => (bool) $user->can_manage_address,
                default => true,
            };
        }

        return false;
    }
    //
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required()
                    ->label('Customer'),

                Forms\Components\TextInput::make('full_name')  // ✅ matches fillable
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')       // ✅ matches fillable
                    ->required()
                    ->maxLength(20),

                Forms\Components\TextInput::make('street')      // ✅ matches fillable
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('city')        // ✅ matches fillable
                    ->maxLength(255),

                Forms\Components\TextInput::make('country')     // ✅ matches fillable
                    ->maxLength(255),
            ]);
        // ->schema([
        //     // 🔥 ADD THIS: Select the User
        //     Forms\Components\Select::make('user_id')
        //         ->relationship('user', 'name') // Assumes Address belongsTo User
        //         ->searchable()
        //         ->required()
        //         ->label('Customer'),

        //     // Your other address fields...
        //     Forms\Components\TextInput::make('address')
        //         ->required()
        //         ->maxLength(255),

        //     Forms\Components\TextInput::make('city')
        //         ->required()
        //         ->maxLength(255),

        //     Forms\Components\TextInput::make('zip_code')
        //         ->numeric(),
        // ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Customer'),
                Tables\Columns\TextColumn::make('full_name'),
                Tables\Columns\TextColumn::make('street'),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('phone'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}
