<?php
namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class ManagePermissions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $navigationLabel = 'Permission Control';
    protected static string $view = 'filament.pages.manage-permissions';

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->where('role', 'shopkeeper'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Shopkeeper Name')
                    ->searchable(),

                // 🟢 TOGGLES FOR EACH FEATURE
                Tables\Columns\ToggleColumn::make('can_manage_products')
                    ->label('Products')
                    ->onColor('success'),

                Tables\Columns\ToggleColumn::make('can_manage_categories')
                    ->label('Categories'),

                Tables\Columns\ToggleColumn::make('can_manage_orders')
                    ->label('Orders'),

                Tables\Columns\ToggleColumn::make('can_manage_address')
                    ->label('Address'),
            ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->role === 'admin';
    }
}