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
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool { return auth()->user()->role === 'admin'; }
    public static function canDelete(Model $record): bool { return auth()->user()->role === 'admin'; }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if ($user->role === 'admin') return false;
        return $user->role === 'shopkeeper' && (bool) $user->can_manage_orders;
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        if ($user->role === 'admin' || $user->email === 'admin@me.com') {
            return parent::getEloquentQuery();
        }
        return parent::getEloquentQuery()->where('shopkeeper_id', $user->shopkeeper?->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- SECTION 1: CORE ORDER INFO ---
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Customer Account')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'PENDING' => 'Pending (Awaiting Verification)',
                                'PROCESSING' => 'Processing',
                                'COMPLETED' => 'Completed',
                                'CANCELLED' => 'Cancelled',
                            ])
                            ->required()
                            ->native(false)
                            ->suffixIcon('heroicon-m-check-circle'),

                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('$')
                            ->readOnly(),
                    ])->columns(3),

                // --- SECTION 2: SHIPPING & GPS ---
                Forms\Components\Section::make('Shipping & Contact Details')
                    ->description('Precise delivery location and contact info')
                    ->schema([
                        // 🟢 FIXED: Displaying the location name sent from Flutter
                        Forms\Components\TextInput::make('delivery_address')
                            ->label('Selected Location (from Map)')
                            ->helperText('The readable address picked by the customer on the map')
                            ->columnSpanFull()
                            ->readOnly(),

                        Forms\Components\Select::make('address_id')
                            ->relationship('address', 'street') 
                            ->label('Saved Profile Street')
                            ->placeholder('No address selected')
                            ->disabled()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('address.full_name')
                            ->label('Recipient Name')
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('address.phone')
                            ->label('Contact Phone')
                            ->prefix('📞')
                            ->disabled(),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')->label('Latitude')->readOnly(),
                                Forms\Components\TextInput::make('longitude')->label('Longitude')->readOnly(),
                                
                                Forms\Components\Placeholder::make('map_link')
                                    ->label('Navigation')
                                    ->content(fn ($record) => $record?->latitude 
                                        ? new HtmlString("<a href='https://www.google.com/maps/search/?api=1&query={$record->latitude},{$record->longitude}' target='_blank' style='color: #2563EB; font-weight: bold; text-decoration: underline;'>📍 Open in Google Maps</a>")
                                        : 'No GPS data'),
                            ]),
                    ])->collapsible(),

                // --- SECTION 3: PAYMENT SLIP ---
                Forms\Components\Section::make('Payment Verification')
                    ->schema([
                        Forms\Components\FileUpload::make('image_qrcode')
                            ->label('Customer Payment Slip')
                            ->disk('public')
                            ->directory('qrcodes')
                            ->image()
                            ->imagePreviewHeight('400')
                            ->disabled() 
                            ->hidden(fn ($record) => !$record?->image_qrcode),
                        
                        Forms\Components\Placeholder::make('no_image')
                            ->label('Payment Slip')
                            ->content('No payment slip uploaded.')
                            ->hidden(fn ($record) => $record?->image_qrcode),
                    ])->collapsible(),

                // --- SECTION 4: PRODUCT ITEMS ---
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
                                Forms\Components\TextInput::make('quantity')->numeric()->disabled(),
                                Forms\Components\TextInput::make('price')->numeric()->prefix('$')->disabled(),
                                Forms\Components\Placeholder::make('subtotal')
                                    ->label('Subtotal')
                                    ->content(fn($get) => '$' . (number_format(($get('quantity') ?? 0) * ($get('price') ?? 0), 2))),
                            ])
                            ->columns(5)
                            ->addable(false)
                            ->deletable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Customer')->searchable(),

                // 🟢 FIXED: Showing the readable location name in the table
                Tables\Columns\TextColumn::make('delivery_address')
                    ->label('Location Name')
                    ->limit(25)
                    ->searchable()
                    ->placeholder('Map Location Not Set'),

                Tables\Columns\TextColumn::make('address.phone')
                    ->label('Phone')
                    ->copyable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'PROCESSING' => 'info',
                        'COMPLETED' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_amount')->money('USD')->sortable(),

                Tables\Columns\ImageColumn::make('image_qrcode')
                    ->label('Slip')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('verifyPayment')
                    ->label('Verify')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'PENDING')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'COMPLETED']);
                        Notification::make()->title('Payment Verified')->success()->send();
                    })
                    ->requiresConfirmation(),
                
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->role === 'admin'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}