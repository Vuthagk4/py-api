<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // 1. CHECKOUT (Receives items directly from Flutter)
    public function checkout(Request $request)
    {
        $user = $request->user();

        // 1. Validate the direct payload from Flutter
        $request->validate([
            'total_amount' => 'required|numeric',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        try {
            return DB::transaction(function () use ($user, $request) {
                
                // 2. Create the Order
                $order = Order::create([
                    'user_id'      => $user->id,
                    'total_amount' => $request->total_amount,
                    'status'       => 'PENDING', // Matches Filament warning badge
                    'address_id'   => $request->address_id ?? null, 
                ]);

                // 3. Save items to permanent OrderItems table
                foreach ($request->items as $item) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'price'      => $item['price']
                    ]);
                }

                // Return order ID so Flutter can immediately mark it as paid!
                return response()->json([
                    'message' => "Order placed successfully",
                    'order_id' => $order->id,
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Checkout failed', 'error' => $e->getMessage()], 500);
        }
    }

    // 2. MARK AS PAID (Call this after Bakong Success in Flutter)
    public function markAsPaid($id)
    {
        $order = Order::findOrFail($id);
        
        // Update to COMPLETED so Filament turns the badge GREEN
        $order->update(['status' => 'COMPLETED']);

        return response()->json([
            'message' => "Payment confirmed",
            'order'   => $order
        ], 200);
    }

    // 3. INDEX (For Flutter Order History)
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
                    ->with(['items.product']) 
                    ->latest()
                    ->get();

        // 🔴 CRITICAL FIX: Format Product Image URLs so they load in the Flutter app!
        $orders->map(function ($order) {
            $order->items->map(function ($item) {
                if ($item->product) {
                    if ($item->product->image && $item->product->image !== 'default.jpg' && !str_starts_with($item->product->image, 'http')) {
                        $item->product->image = asset('storage/' . $item->product->image);
                    } else if (!$item->product->image || $item->product->image === 'default.jpg') {
                        $item->product->image = asset('storage/products/default.jpg');
                    }
                }
                return $item;
            });
            return $order;
        });

        return response()->json($orders, 200);
    }
}