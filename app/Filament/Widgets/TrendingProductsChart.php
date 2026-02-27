<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TrendingProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Product Sales Trend';

    // This makes the widget take up the full width of the dashboard
    protected int | string | array $columnSpan = 'full';
    public static function canView(): bool
    {
        // 🟢 Ensure only shopkeepers see this specific sales trend
        return auth()->user()->role === 'shopkeeper';
    }

    protected function getData(): array
    {
        $shopId = auth()->user()->shopkeeper?->id;

        // We query OrderItem to see how many of each product were actually sold
        $trendingData = \App\Models\OrderItem::query()
            ->whereHas('order', function ($query) use ($shopId) {
                $query->where('shopkeeper_id', $shopId);
            })
            ->select('product_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->with('product:id,name')
            ->get();

        if ($trendingData->isEmpty()) {
            return [
                'datasets' => [['label' => 'No Sales', 'data' => [0], 'borderColor' => '#9ca3af']],
                'labels' => ['No items sold yet'],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Quantity Sold',
                    'data' => $trendingData->pluck('total_sold')->toArray(),
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $trendingData->map(fn($item) => $item->product?->name ?? 'Unknown Item')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line'; // 🟢 Changed to line
    }
}
