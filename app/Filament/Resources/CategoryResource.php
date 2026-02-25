<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder; // 🟢 Added
use Illuminate\Database\Eloquent\Model;   // 🟢 Added

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 3;

    /**
     * SECURITY: This ensures Shopkeepers only see their own categories in the list.
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Admin sees all categories from everyone
        if ($user->role === 'admin' || $user->email === 'admin@me.com') {
            return parent::getEloquentQuery();
        }

        // Shopkeepers only see categories where shopkeeper_id matches their ID
        return parent::getEloquentQuery()->where('shopkeeper_id', $user->shopkeeper?->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true) 
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                $set('slug', Str::slug($state));
                            }),

                        // 🟢 Automatically link to the current Shopkeeper
                        Forms\Components\Hidden::make('shopkeeper_id')
                            ->default(fn () => auth()->user()->shopkeeper?->id),

                        

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                // 🟢 Show which Shop owns this category (Visible to Admin)
                Tables\Columns\TextColumn::make('shopkeeper.shop_name')
                    ->label('Owner Shop')
                    ->visible(fn () => auth()->user()->role === 'admin')
                    ->badge(),

                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50), 

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                // Allow Admin to filter categories by shop
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}