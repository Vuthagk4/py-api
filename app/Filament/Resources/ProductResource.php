<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product; // Ensure this is here
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 2;

    /**
     * SECURITY: Scopes the query so Shopkeepers only see their own products.
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Admin can see everything
        if ($user->role === 'admin' || $user->email === 'admin@me.com') {
            return parent::getEloquentQuery();
        }

        // Shopkeepers only see their linked products
        return parent::getEloquentQuery()->where('shopkeeper_id', $user->shopkeeper?->id);
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
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required(),

                        Forms\Components\Select::make('shopkeeper_id')
                            ->relationship('shopkeeper', 'shop_name')
                            ->label('Shop Owner')
                            // Auto-fill with the logged-in user's shop ID
                            ->default(fn () => auth()->user()->shopkeeper?->id)
                            // Only Admins can change the shop owner
                            ->disabled(fn () => auth()->user()->role !== 'admin')
                            ->dehydrated() // Ensures the ID is sent even if disabled
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('products')
                            ->default('default.jpg'),
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

                // Displaying Shop Name + Telegram Username
                Tables\Columns\TextColumn::make('shopkeeper.shop_name')
                    ->label('Shop')
                    ->sortable()
                    ->description(fn (Product $record): string => 
                        $record->shopkeeper?->telegram_username ? "@{$record->shopkeeper->telegram_username}" : 'No Telegram'
                    ),

                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // Grouping products by Shop for better Admin view
            ->groups([
                Tables\Grouping\Group::make('shopkeeper.shop_name')
                    ->label('Shop Name')
                    ->collapsible(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                
                Tables\Filters\SelectFilter::make('shopkeeper')
                    ->relationship('shopkeeper', 'shop_name')
                    ->visible(fn () => auth()->user()->role === 'admin'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->role === 'admin'),
                ]),
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