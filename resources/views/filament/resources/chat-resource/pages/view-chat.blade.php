<x-filament-panels::page>
    <div class="flex flex-col h-[650px] bg-white border rounded-xl dark:bg-gray-900 dark:border-gray-700 shadow-sm overflow-hidden">
        
        <div class="p-4 border-b bg-gray-50 dark:bg-gray-800 flex items-center justify-between">
            <h3 class="font-bold text-lg text-gray-800 dark:text-white">
                Customer Conversation
            </h3>
            <span class="text-xs text-gray-500">Live Updates Enabled</span>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50 dark:bg-gray-950" 
             wire:poll.3s
             id="chat-container">
            
            @forelse($messages as $msg)
                <div class="flex {{ $msg->sender_type === 'shopkeeper' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%] px-4 py-2 rounded-2xl shadow-sm {{ $msg->sender_type === 'shopkeeper' ? 'bg-primary-600 text-white' : 'bg-white text-gray-800 border dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700' }}">
                        
                        @if($msg->image_url)
                            <img src="{{ asset('storage/' . $msg->image_url) }}" 
                                 class="rounded-lg mb-2 max-h-48 w-auto cursor-pointer" 
                                 onclick="window.open(this.src)">
                        @endif

                        @if($msg->message)
                            <p class="text-sm leading-relaxed">{{ $msg->message }}</p>
                        @endif
                        
                        <div class="text-[10px] mt-1 opacity-70 {{ $msg->sender_type === 'shopkeeper' ? 'text-right' : 'text-left' }}">
                            {{ $msg->created_at->format('H:i') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <p>No messages yet. Say hello to your customer!</p>
                </div>
            @endforelse
        </div>

        <div class="p-4 border-t bg-white dark:bg-gray-900">
            <div class="flex items-center gap-2">
                <input type="text" 
                       wire:model="replyMessage" 
                       wire:keydown.enter="sendMessage"
                       placeholder="Type your reply..." 
                       class="flex-1 rounded-full border-gray-300 dark:bg-gray-800 dark:border-gray-700 focus:ring-primary-500">
                
                <button wire:click="sendMessage" 
                        class="p-2 bg-primary-600 hover:bg-primary-700 text-white rounded-full transition-colors">
                    <x-heroicon-m-paper-airplane class="w-6 h-6"/>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const container = document.getElementById('chat-container');
            const scrollToBottom = () => { container.scrollTop = container.scrollHeight; };
            
            scrollToBottom();
            
            Livewire.on('refresh-chat', () => {
                setTimeout(scrollToBottom, 100);
            });
        });
    </script>
</x-filament-panels::page>