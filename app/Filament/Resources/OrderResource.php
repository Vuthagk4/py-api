<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 4;


    public static function form(Form $form): Form
{
    return $form
        ->schema([
            // Section 1: Order Information
            Forms\Components\Section::make('Order Information')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required()
                        ->label('Customer'),

                    Forms\Components\Select::make('status')
                        ->options([
                            'PENDING' => 'Pending',
                            'PROCESSING' => 'Processing',
                            'COMPLETED' => 'Completed',
                            'CANCELLED' => 'Cancelled',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('total_amount')
                        ->numeric()
                        ->prefix('$')
                        ->readOnly(),
                ])->columns(3),

            // Section 2: Order Items (The Products)
            Forms\Components\Section::make('Products Ordered')
                ->schema([
                    Forms\Components\Repeater::make('items') // This refers to the OrderItems relationship
                        ->relationship()
                        ->schema([
                            // 1. Show Product Name (via relationship)
                            Forms\Components\Select::make('product_id')
                                ->relationship('product', 'name')
                                ->label('Product Name')
                                ->disabled() // Read-only for checkout history
                                ->columnSpan(2),

                            // 2. Show Quantity
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->label('Qty')
                                ->disabled(),

                            // 3. Show Price at time of purchase
                            Forms\Components\TextInput::make('price')
                                ->numeric()
                                ->prefix('$')
                                ->label('Unit Price')
                                ->disabled(),
                                
                            // 4. Subtotal (Optional calculated field)
                            Forms\Components\Placeholder::make('subtotal')
                                ->label('Subtotal')
                                ->content(fn ($get) => '$' . (number_format(($get('quantity') ?? 0) * ($get('price') ?? 0), 2))),
                        ])
                        ->columns(5) // Adjusted columns to fit subtotal
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                ]),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'PROCESSING' => 'info',
                        'COMPLETED' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable(),

                // 🔥 FIXED: Changed 'created_on' to 'created_at'
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // 🔥 FIXED: Changed 'created_on' to 'created_at'
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PENDING' => 'Pending',
                        'COMPLETED' => 'Completed',
                        'CANCELLED' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
