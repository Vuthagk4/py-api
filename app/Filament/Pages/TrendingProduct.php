<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\TrendingProductsChart; // 🟢 Import your widget

class TrendingProduct extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static string $view = 'filament.pages.trending-product';
    protected static ?string $navigationLabel = 'Trending Products';

    // 🟢 This puts the chart inside the page
    protected function getHeaderWidgets(): array
    {
        return [
            TrendingProductsChart::class,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        // 🟢 Only show this menu to shopkeepers
        return auth()->user()->role === 'shopkeeper';
    }
}
