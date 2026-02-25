<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockProducts extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Low Stock Alert';

    /**
     * Set who can see this widget on their dashboard
     */
    public static function canView(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'shopkeeper']);
    }

    public function table(Table $table): Table
    {
        return $table
            // Inside app/Filament/Widgets/LowStockProducts.php
->query(
    Product::query()
        ->when(auth()->user()->role !== 'admin', function (Builder $query) {
            return $query->where('shopkeeper_id', auth()->user()->shopkeeper?->id);
        })
        ->where('stock', '<=', 10) // 🟢 Change this back to 'stock'
        ->latest()
)
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable(),

                // Visible to Admin to see which shop is low on items
                Tables\Columns\TextColumn::make('shopkeeper.shop_name')
                    ->label('Shop')
                    ->visible(fn () => auth()->user()->role === 'admin'),

                // 🟢 Change 'stock' to 'quantity' here too
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Remaining Qty')
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('price')
                    ->money('usd'),
            ]);
    }
}