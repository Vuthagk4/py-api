<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\View\LegacyComponents\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\UserResource\Widgets\NormalUsersTable;
use App\Filament\Resources\UserResource\Widgets\AdminUsersTable;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'User Management';
    protected static ?string $navigationGroup = 'User Management';


    // Protect if not admin not show user management 
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    // app/Filament/Resources/UserResource.php
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

    // app/Filament/Resources/UserResource.php

public static function form(Form $form): Form
{
    return $form
        ->schema([
            // SECTION 1: Account Basics
            Forms\Components\Section::make('User Account')
                ->schema([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('email')->email()->required(),
                    Forms\Components\Select::make('role')
                        ->options([
                            'admin' => 'Admin',
                            'shopkeeper' => 'Shopkeeper',
                            'user' => 'User',
                        ])
                        ->required()
                        ->live(), // 🟢 This allows the UI to update as you change the role
                ]),

            // SECTION 2: Permission Control (The "Menu" you are looking for)
            Forms\Components\Section::make('Permission Control')
                ->description('Enable or disable shop menus for this Shopkeeper.')
                ->visible(fn ($get) => $get('role') === 'shopkeeper') // 🟢 Only shows for shopkeepers
                ->schema([
                    Forms\Components\Toggle::make('can_manage_products')
                        ->label('Product Menu')
                        ->helperText('Allow access to Cloth Inventory'),
                    
                    Forms\Components\Toggle::make('can_manage_categories')
                        ->label('Category Menu')
                        ->helperText('Allow access to Cloth Categories'),
                    
                    Forms\Components\Toggle::make('can_manage_orders')
                        ->label('Order Menu')
                        ->helperText('Allow access to Sales and Orders'),
                ])->columns(3),
        ]);
}
    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('name')->searchable(),
    //             Tables\Columns\TextColumn::make('email')->searchable(),
    //             Tables\Columns\TextColumn::make('role')->badge(),
    //         ])
    //         ->actions([
    //             Tables\Actions\ViewAction::make(),
    //             Tables\Actions\EditAction::make(),
    //             Tables\Actions\DeleteAction::make(),
    //         ]);
    // }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([]) // ✅ No columns = invisible table
            ->paginated(false) // ✅ No pagination bar
            ->actions([]);
    }


    // ✅ Register the 2 widgets here
    public static function getWidgets(): array
    {
        return [
            NormalUsersTable::class,
            AdminUsersTable::class,
        ];
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
