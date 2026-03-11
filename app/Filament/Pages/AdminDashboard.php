<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\AdminStatsOverview;
use App\Filament\Widgets\ShopkeeperSalesOverview;

class AdminDashboard extends Page
{
    // The blade view this page renders
    protected static string $view = 'filament.pages.admin-dashboard';

    // Sidebar icon
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    // Sidebar label
    protected static ?string $navigationLabel = 'Admin Dashboard';


    // Page title (shown at top)
    protected static ?string $title = 'Admin Dashboard';

    // Where it appears in the sidebar (lower = higher up)
    protected static ?int $navigationSort = -1;

    // Only admin can see this page
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    // Register which widgets appear on this page
    protected function getHeaderWidgets(): array
    {
        return [
            AdminStatsOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            ShopkeeperSalesOverview::class,
        ];
    }
}
