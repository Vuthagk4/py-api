<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\Cart;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRevenue = Cart::where('status', 'completed')->sum('total');
        $pendingOrders = Cart::where('status', 'pending')->count();
        $completedOrders = Cart::where('status', 'completed')->count();

        return [
            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->description('From completed orders')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Products', Product::count())
                ->description('In your store')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Total Categories', Category::count())
                ->description('Product categories')
                ->descriptionIcon('heroicon-m-tag')
                ->color('warning'),

            Stat::make('Pending Orders', $pendingOrders)
                ->description('Waiting to process')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            Stat::make('Completed Orders', $completedOrders)
                ->description('Successfully delivered')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Customers', User::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
