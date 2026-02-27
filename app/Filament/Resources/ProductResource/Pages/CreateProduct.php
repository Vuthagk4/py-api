<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Services\FCMService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $product = $this->record;

        // 🟢 Use the same service that worked in Postman
        $fcmService = new FCMService();

        try {
            // 🟢 MATCH YOUR POSTMAN TOPIC: 'shopkeepers'
            $fcmService->sendToTopic(
                'shopkeepers', 
                'New Product Added! 🛒', 
                "Shopkeeper has added: {$product->name}"
            );
            Log::info("Filament Notification Sent to 'shopkeepers' topic.");
        } catch (\Exception $e) {
            Log::error("Filament Notification Failed: " . $e->getMessage());
        }
    }
}