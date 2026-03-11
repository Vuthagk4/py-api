<?php

namespace App\Filament\Widgets;

use App\Models\Shopkeeper;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ShopkeeperSalesOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Shopkeeper Sales Overview';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Shopkeeper::query()
                    ->withCount(['products as total_products'])
                    ->withCount(['orders as total_orders'])
                    ->withSum('orders as total_revenue', 'total_amount')
            )
            ->filters([
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('From Date')
                            ->maxDate(now()),
                        DatePicker::make('date_to')
                            ->label('To Date')
                            ->maxDate(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'] ?? null, function ($q, $date) {
                                $q->withCount([
                                    'orders as total_orders' => fn($oq) =>
                                    $oq->whereDate('created_at', '>=', $date)
                                ])
                                    ->withSum([
                                        'orders as total_revenue' => fn($oq) =>
                                        $oq->whereDate('created_at', '>=', $date)
                                    ], 'total_amount');
                            })
                            ->when($data['date_to'] ?? null, function ($q, $date) {
                                $q->withCount([
                                    'orders as total_orders' => fn($oq) =>
                                    $oq->whereDate('created_at', '<=', $date)
                                ])
                                    ->withSum([
                                        'orders as total_revenue' => fn($oq) =>
                                        $oq->whereDate('created_at', '<=', $date)
                                    ], 'total_amount');
                            });
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['date_from'] ?? null)
                            $indicators[] = Tables\Filters\Indicator::make(
                                'From: ' . Carbon::parse($data['date_from'])->format('M d, Y')
                            )->removeField('date_from');
                        if ($data['date_to'] ?? null)
                            $indicators[] = Tables\Filters\Indicator::make(
                                'To: ' . Carbon::parse($data['date_to'])->format('M d, Y')
                            )->removeField('date_to');
                        return $indicators;
                    }),
            ])
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(
                        fn($record) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($record->shop_name) . '&background=random&color=fff'
                    ),

                Tables\Columns\TextColumn::make('shop_name')
                    ->label('Shop Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_products')
                    ->label('Products Listed')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Total Orders')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Revenue Earned')
                    ->money('usd')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),
            ])
            ->defaultSort('total_revenue', 'desc')
            ->paginated([10, 25, 50]);
    }
}
