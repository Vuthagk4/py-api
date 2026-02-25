<?php
namespace App\Filament\Widgets;

use App\Models\OrderProduct; 
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ShopkeeperRecentOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Filter the items so the seller only sees THEIR sold products
                OrderProduct::query()
                    ->whereHas('product', function ($query) {
                        $query->where('shopkeeper_id', auth()->user()->shopkeeper?->id);
                    })
                    ->with(['product', 'order']) // Ensure relationships are loaded
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Order #')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product'),
                Tables\Columns\TextColumn::make('quantity')
                    ->badge(),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('order.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
            ]);
    }
}