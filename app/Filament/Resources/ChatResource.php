<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ChatResource\Pages;
use App\Models\Chat;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ChatResource extends Resource
{
    protected static ?string $model = Chat::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function table(Table $table): Table
    {
        return $table
            // 🟢 Only show the latest message for each unique customer
            ->query(Chat::where('shopkeeper_id', Auth::id())->distinct('user_id'))
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('message')
                    ->label('Last Message')
                    ->limit(40),
                Tables\Columns\IconColumn::make('is_read')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-envelope')
                    ->color(fn (bool $state): string => $state ? 'gray' : 'danger'), // 🟢 Red envelope for unread
            ])
            ->actions([
                // 🟢 This opens the custom "ViewChat" page we created
                Tables\Actions\Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-m-chat-bubble-left')
                    ->color('success')
                    ->url(fn (Chat $record): string => static::getUrl('view-chat', ['userId' => $record->user_id])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageChats::route('/'),
            'view-chat' => Pages\ViewChat::route('/{userId}/view'),
        ];
    }
}
