<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ShopkeeperStats extends BaseWidget
{
    protected function getStats(): array
    {
        $shopkeeper = auth()->user()->shopkeeper;

        // If the user is an admin or has no shopkeeper profile, show empty stats or handle logic
        if (!$shopkeeper) {
            return [
                Stat::make('Admin View', 'All Systems Nominal')
                    ->description('Log in as shopkeeper to see sales')
                    ->color('info'),
            ];
        }

        return [
            Stat::make('My Products', Product::where('shopkeeper_id', $shopkeeper->id)->count())
                ->description('Total items in your shop')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),

            Stat::make('Shop Status', $shopkeeper->is_verified ? 'Verified' : 'Pending Review')
                ->description($shopkeeper->is_verified ? 'Blue Badge Active' : 'Admin is checking your shop')
                ->descriptionIcon($shopkeeper->is_verified ? 'heroicon-m-check-badge' : 'heroicon-m-clock')
                ->color($shopkeeper->is_verified ? 'info' : 'warning'),
        ];
    }
}