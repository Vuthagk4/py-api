<?php
namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestShopProducts extends BaseWidget
{
    protected int | string | array $columnSpan = 'full'; // Make it wide

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Security: Only fetch products for this shopkeeper
                Product::query()->where('shopkeeper_id', auth()->user()->shopkeeper?->id)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')->circular(),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('price')->money('USD'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ]);
    }
}