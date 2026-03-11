<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->role === 'admin';
    }

    // ✅ Admin does NOT see Orders, only shopkeeper does
    public static function shouldRegisterNavigation(): bool
{
    $user = auth()->user();

    // 🟢 Fix 1: Admin should NOT see the Order menu based on your previous request
    if ($user->role === 'admin') return false;

    // 🟢 Fix 2: Shopkeeper ONLY sees the menu if their toggle is ON in the database
    // Note: We cast to (bool) to ensure it handles 0/1 correctly from MySQL
    return $user->role === 'shopkeeper' && (bool) $user->can_manage_orders;
}

    // ✅ Shopkeeper sees only their own orders, everyone else sees nothing
    public static function getEloquentQuery(): Builder
{
    $user = auth()->user();

    // 🟢 Fix 3: Even if they access the URL directly, block data if toggle is OFF
    if ($user->role === 'shopkeeper') {
        if (!(bool) $user->can_manage_orders) {
            return parent::getEloquentQuery()->whereRaw('0 = 1'); // Returns nothing
        }

        return parent::getEloquentQuery()
            ->where('shopkeeper_id', $user->shopkeeper?->id);
    }

    return parent::getEloquentQuery()->whereRaw('0 = 1');
}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Customer Account')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'PENDING'    => 'Pending (Awaiting Verification)',
                                'PROCESSING' => 'Processing',
                                'COMPLETED'  => 'Completed',
                                'CANCELLED'  => 'Cancelled',
                            ])
                            ->required()
                            ->native(false)
                            ->suffixIcon('heroicon-m-check-circle'),
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()->prefix('$')->readOnly(),
                    ])->columns(3),

                Forms\Components\Section::make('Shipping & Contact Details')
                    ->schema([
                        Forms\Components\TextInput::make('delivery_address')
                            ->label('Selected Location (from Map)')
                            ->columnSpanFull()
                            ->readOnly(),

                        Forms\Components\TextInput::make('phone')
                            ->label('Customer Phone')
                            ->prefix('📞')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('address_id')
                            ->relationship('address', 'street')
                            ->label('Saved Street')
                            ->disabled()
                            ->columnSpanFull()
                            ->hidden(fn($record) => !$record?->address_id),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('latitude')
                                ->readOnly(),
                            Forms\Components\TextInput::make('longitude')
                                ->readOnly(),
                            Forms\Components\Placeholder::make('map_link')
                                ->label('Navigation')
                                ->content(fn($record) => $record?->latitude
                                    ? new HtmlString(
                                        "<a href='https://www.google.com/maps/search/?api=1&query={$record->latitude},{$record->longitude}'
                                        target='_blank'
                                        style='color:#2563EB;font-weight:bold;text-decoration:underline;'>
                                        📍 Open in Google Maps</a>"
                                    )
                                    : 'No GPS data'),
                        ]),
                    ])->collapsible(),

                Forms\Components\Section::make('Payment Verification')
                    ->schema([
                        Forms\Components\FileUpload::make('image_qrcode')
                            ->label('Payment Slip')
                            ->disk('public')
                            ->directory('qrcodes')
                            ->image()
                            ->imagePreviewHeight('400')
                            ->disabled()
                            ->hidden(fn($record) => !$record?->image_qrcode),
                        Forms\Components\Placeholder::make('no_image')
                            ->label('Payment Slip')
                            ->content('No payment slip uploaded.')
                            ->hidden(fn($record) => $record?->image_qrcode),
                    ])->collapsible(),

                Forms\Components\Section::make('Products Ordered')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->label('Product')
                                    ->disabled()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()->disabled(),
                                Forms\Components\TextInput::make('price')
                                    ->numeric()->prefix('$')->disabled(),
                                Forms\Components\Placeholder::make('size')
                                    ->label('Size')
                                    ->content(fn($get) => $get('size') ?? '—'),
                                Forms\Components\Placeholder::make('subtotal')
                                    ->label('Subtotal')
                                    ->content(fn($get) => '$' . number_format(
                                        ($get('quantity') ?? 0) * ($get('price') ?? 0), 2
                                    )),
                            ])
                            ->columns(6)
                            ->addable(false)
                            ->deletable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('# Order')
                    ->sortable()
                    ->weight('bold')
                    ->prefix('#'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Order $record): string =>
                        $record->user?->email ?? ''),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->badge()
                    ->color('gray')
                    ->suffix(' item(s)'),

                Tables\Columns\TextColumn::make('items.size')
                    ->label('Sizes')
                    ->badge()
                    ->color('warning')
                    ->separator(',')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('delivery_address')
                    ->label('Location')
                    ->limit(25)
                    ->searchable()
                    ->placeholder('Not Set')
                    ->icon('heroicon-m-map-pin')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->copyable()
                    ->copyMessage('Phone copied!')
                    ->icon('heroicon-m-phone')
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PENDING'    => 'warning',
                        'PROCESSING' => 'info',
                        'COMPLETED'  => 'success',
                        'CANCELLED'  => 'danger',
                        default      => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'PENDING'    => 'heroicon-m-clock',
                        'PROCESSING' => 'heroicon-m-arrow-path',
                        'COMPLETED'  => 'heroicon-m-check-circle',
                        'CANCELLED'  => 'heroicon-m-x-circle',
                        default      => 'heroicon-m-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\ImageColumn::make('image_qrcode')
                    ->label('Slip')
                    ->disk('public')
                    ->circular()
                    ->size(45),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->since()
                    ->sortable()
                    ->tooltip(fn(Order $record): string =>
                        $record->created_at?->format('Y-m-d H:i:s') ?? ''),
            ])
            ->defaultSort('id', 'desc')
            ->striped()
            ->actionsColumnLabel('Actions')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PENDING'    => '🕐 Pending',
                        'PROCESSING' => '🔄 Processing',
                        'COMPLETED'  => '✅ Completed',
                        'CANCELLED'  => '❌ Cancelled',
                    ])
                    ->label('Status'),

                Tables\Filters\Filter::make('today')
                    ->label("Today's Orders")
                    ->query(fn(Builder $query) =>
                        $query->whereDate('created_at', today())),

                Tables\Filters\Filter::make('has_slip')
                    ->label('Has Payment Slip')
                    ->query(fn(Builder $query) =>
                        $query->whereNotNull('image_qrcode')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button()->label('Edit'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulkVerify')
                        ->label('Verify Selected')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->action(fn($records) =>
                            $records->each->update(['status' => 'COMPLETED']))
                        ->requiresConfirmation(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}