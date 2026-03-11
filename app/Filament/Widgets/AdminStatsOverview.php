<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shopkeeper;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    // Only admin sees this
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    protected function getStats(): array
    {
        $totalShopkeepers = Shopkeeper::count();
        $totalRevenue     = Order::whereMonth('created_at', now()->month)->sum('total_amount');
        $ordersToday      = Order::whereDate('created_at', today())->count();
        $lowStock         = Product::where('stock', '<=', 10)->count();

        // Compare revenue to last month
        $lastMonthRevenue = Order::whereMonth('created_at', now()->subMonth()->month)->sum('total_amount');
        $trend = $lastMonthRevenue > 0
            ? round((($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        return [
            Stat::make('Total Shopkeepers', $totalShopkeepers)
                ->description('Registered shops on platform')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info'),

            Stat::make('Revenue This Month', '$' . number_format($totalRevenue, 2))
                ->description(($trend >= 0 ? '+' : '') . $trend . '% vs last month')
                ->descriptionIcon($trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($trend >= 0 ? 'success' : 'danger'),

            Stat::make('Orders Today', $ordersToday)
                ->description('Across all shops')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),

            Stat::make('Low Stock Products', $lowStock)
                ->description('Across all shops')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStock > 0 ? 'danger' : 'success'),
        ];
    }
}
