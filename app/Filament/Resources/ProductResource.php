<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        if ($user->role === 'admin' || $user->email === 'admin@me.com') {
            return parent::getEloquentQuery();
        }
        return parent::getEloquentQuery()->where('shopkeeper_id', $user->shopkeeper?->id);
    }

    //hide
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        // Admin never sees these
        if ($user->role === 'admin') return false;

        // 🟢 DYNAMIC CHECK: Check if the Admin enabled this feature for the Shopkeeper
        if ($user->role === 'shopkeeper') {
            return match (static::class) {
                ProductResource::class => (bool) $user->can_manage_products,
                OrderResource::class => (bool) $user->can_manage_orders,
                CategoryResource::class => (bool) $user->can_manage_categories,
                AddressResource::class => (bool) $user->can_manage_address,
                default => true,
            };
        }

        return false;
    }





    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\Select::make('category_id')
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                // 🟢 ADD THIS MODIFY QUERY:
                                modifyQueryUsing: fn(Builder $query) => $query->where('shopkeeper_id', auth()->user()->shopkeeper?->id)
                            )
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required(),

                        // --- FEATURED TOGGLE ADDED HERE ---
                        Toggle::make('is_featured')
                            ->label('Feature this Product')
                            ->onIcon('heroicon-m-bolt')
                            ->offIcon('heroicon-m-x-mark')
                            ->default(false),

                        Forms\Components\Select::make('shopkeeper_id')
                            ->relationship('shopkeeper', 'shop_name')
                            ->label('Shop Owner')
                            ->default(fn() => auth()->user()->shopkeeper?->id)
                            ->disabled(fn() => auth()->user()->role !== 'admin')
                            ->dehydrated()
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('products')
                            ->visibility('public'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                // --- FEATURED STATUS VISIBLE IN TABLE ---
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('shopkeeper.shop_name')
                    ->label('Shop')
                    ->sortable()
                    ->description(
                        fn(Product $record): string =>
                        $record->shopkeeper?->telegram_username ? "@{$record->shopkeeper->telegram_username}" : 'No Telegram'
                    ),

                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured Only'),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
