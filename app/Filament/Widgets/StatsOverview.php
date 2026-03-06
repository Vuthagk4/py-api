<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'shopkeeper']);
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        // Consistently check for 'admin' role or specific email
        $isAdmin = $user->role === 'admin' || $user->email === 'admin@me.com';
        $shopId = $user->shopkeeper?->id; 

        // 🟢 Query Scoping Logic
        $revenueQuery = Cart::where('status', 'completed');
        $pendingQuery = Cart::where('status', 'pending');
        $completedQuery = Cart::where('status', 'completed');
        $productQuery = Product::query();
        $categoryQuery = Category::query();

        if (!$isAdmin) {
            $revenueQuery->where('shopkeeper_id', $shopId);
            $pendingQuery->where('shopkeeper_id', $shopId);
            $completedQuery->where('shopkeeper_id', $shopId);
            $productQuery->where('shopkeeper_id', $shopId);
            $categoryQuery->where('shopkeeper_id', $shopId);
        }

        return [
            Stat::make('Total Revenue', '$' . number_format($revenueQuery->sum('total'), 2))
                ->description($isAdmin ? 'Platform-wide revenue' : 'Your shop revenue')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Products', $productQuery->count())
                ->description('Active items')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Total Categories', $categoryQuery->count())
                ->description('Your categories')
                ->descriptionIcon('heroicon-m-tag')
                ->color('warning'),

            Stat::make('Pending Orders', $pendingQuery->count())
                ->description('Awaiting action')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            Stat::make('Completed Orders', $completedQuery->count())
                ->description('Finished transactions')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Customers', $isAdmin ? User::count() : ($user->customers() ? $user->customers()->count() : 0))
                ->description($isAdmin ? 'Total platform users' : 'Users who bought from you')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}