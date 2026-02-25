<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
  public function checkout(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'total_amount'  => 'required|numeric',
            'shopkeeper_id' => 'required|integer', // 🟢 Require the ID from Flutter
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric',
        ]);

        try {
            return DB::transaction(function () use ($user, $request) {
                
                $order = Order::create([
                    'user_id'       => $user->id,
                    'total_amount'  => $request->total_amount,
                    'status'        => 'COMPLETED', 
                    'shopkeeper_id' => $request->shopkeeper_id, // 🟢 Save it directly!
                ]);

                foreach ($request->items as $item) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'price'      => $item['price']
                    ]);
                }

                return response()->json([
                    'message' => "Order placed successfully",
                    'order_id' => $order->id,
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'DB ERROR: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markAsPaid($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'COMPLETED']);

        return response()->json(['message' => "Payment confirmed", 'order' => $order], 200);
    }

    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
                    ->with(['items.product']) 
                    ->latest()
                    ->get();

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