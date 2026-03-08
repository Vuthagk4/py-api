<?php

namespace App\Filament\Resources\ChatResource\Pages;

use App\Filament\Resources\ChatResource;
use App\Models\Chat;
use App\Models\User;
use App\Services\FCMService;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

// 🟢 The class name MUST match the filename and NOT be "ChatResource"
class ViewChat extends Page
{
    protected static string $resource = ChatResource::class;

    protected static string $view = 'filament.resources.chat-resource.pages.view-chat';

    public $userId;
    public $replyMessage = '';

    public function mount($userId): void
    {
        $this->userId = $userId;
        
        // Mark customer messages as read
        Chat::where('user_id', $userId)
            ->where('shopkeeper_id', Auth::id())
            ->update(['is_read' => true]);
    }

    public function sendMessage(): void
    {
        if (empty($this->replyMessage)) return;

        Chat::create([
            'user_id' => $this->userId,
            'shopkeeper_id' => Auth::id(),
            'message' => $this->replyMessage,
            'sender_type' => 'shopkeeper', 
        ]);

        // Trigger Push Notification to Flutter
        $user = User::find($this->userId);
        if ($user && $user->fcm_token) {
            app(FCMService::class)->sendPushNotification(
                $user->fcm_token,
                'New Message',
                $this->replyMessage,
                ['type' => 'chat']
            );
        }

        $this->replyMessage = '';
    }

    protected function getViewData(): array
{
    return [
        // 🟢 Ensure we fetch ALL messages in this thread, regardless of sender
        'messages' => Chat::where('user_id', $this->userId)
            ->where('shopkeeper_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->get(),
    ];
}
}