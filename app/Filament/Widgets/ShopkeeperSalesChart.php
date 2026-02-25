<?php

namespace App\Filament\Widgets;

use App\Models\OrderProduct;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ShopkeeperSalesChart extends ChartWidget
{
    protected static ?string $heading = 'Shop Sales Performance';
    protected static string $color = 'info';

    // 🟢 Hide this specific widget from Global Admins if it's meant for Shopkeepers only
    public static function canView(): bool
    {
        return auth()->user()->role === 'shopkeeper';
    }

    protected function getData(): array
    {
        $shopkeeperId = auth()->user()->shopkeeper?->id;

        // If for some reason an admin sees this, show all data; otherwise filter
        $query = OrderProduct::query();
        
        if (auth()->user()->role !== 'admin') {
            $query->whereHas('product', function ($q) use ($shopkeeperId) {
                $q->where('shopkeeper_id', $shopkeeperId);
            });
        }

        $data = Trend::query($query)
            ->between(
                start: now()->startOfMonth(),
                end: now()->endOfMonth(),
            )
            ->perDay()
            ->count(); 

        return [
            'datasets' => [
                [
                    'label' => 'Items Sold This Month',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'fill' => 'start',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}