<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB; // 🔥 Important for safety

class OrderController extends Controller
{
    // 1. CHECKOUT (The "Two" Strategy)
    public function checkout(Request $request)
    {
        $user = $request->user();

        // Find the 'active' cart (don't pick up old completed ones)
        $cart = Cart::where('user_id', $user->id)
                    ->where('status', 'active') 
                    ->with('items') 
                    ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => "Cart is empty or not found"], 404);
        }

        // Start Transaction
        return DB::transaction(function () use ($user, $cart, $request) {
            
            // Calculate total again to be safe
            $totalAmount = $cart->items->sum(function($item) {
                return $item->price * $item->quantity;
            });

            // A. Create the Order
            $order = Order::create([
                'user_id' => $user->id,
                'cart_id' => $cart->id, // Link to Cart
                'total_amount' => $totalAmount,
                'status' => 'PENDING',
                // Use address from request, or fallback to user's default if you have one
                'address_id' => $request->address_id ?? null, 
            ]);

            // B. Copy Cart Items -> Order Items (The "Two" Strategy)
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price
                ]);
            }

            // C. Close the Cart
            $cart->update(['status' => 'completed']);

            return response()->json([
                'message' => "Order placed successfully",
                'order' => $order->load('items') // Load the new items
            ], 201);
        });
    }

    // 2. INDEX (View History)
    public function index(Request $request)
    {
        // Load BOTH the old cart and the permanent order items
        $orders = Order::where('user_id', $request->user()->id)
                    ->with(['cart', 'items.product']) 
                    ->latest()
                    ->get();

        return response()->json($orders, 200);
    }

    // 3. STORE (Manual Creation - Optional)
    public function store(Request $request)
    {
        $request->validate([
            'total_amount' => 'required|numeric',
            'status' => 'required|string|in:pending,completed,cancelled',
            'user_id' => 'required|exists:users,id',
            // 'items' => 'required|array' // Make sure you validate items exist
        ]);

        return DB::transaction(function () use ($request) {
            $order = Order::create([
                'user_id' => $request->user_id,
                'total_amount' => $request->total_amount,
                'status' => $request->status,
                'shipping_address' => $request->shipping_address ?? 'Default Address',
            ]);

            if ($request->items) {
                foreach($request->items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
            }

            return response()->json([
                'message' => "Order created successfully",
                'order' => $order->load('items')
            ], 201);
        });
    }
}