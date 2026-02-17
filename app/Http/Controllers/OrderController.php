<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
{
    
    public function store(Request $request){
        $request->validate([
            'total_amount' => 'required|numeric',
            'status' => 'required|string|max:255',
            'sipping_address' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|string|in:pending,completed,cancelled',
        ]);

        $order = Order::create([
            'user_id' => $request->user_id,
            'total_amount' => $request->total_amount,
            'status' => $request->status,
            'shipping_address' => $request->shipping_address,
        ]);

        foreach($request->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        return response()->json([
            'message' => "Order created successfully",
            'order' => $order
        ], 200);

    }

    public function checkout(){
        $cart = Cart::where('user_id', auth()->user()->id)->first();
        if(!$cart) {
            return response()->json([
                'message' => "Cart not found"
            ], 404);
        }
        $totalAmount = $cart->items->sum(function($item) {
            return $item->price * $item->quantity;
        });

        $order = Order::create([
            'user_id' => auth()->user()->id,
            'total_amount' => $cart->total_amount,
            'status' => 'pending',
            'shipping_address' => $cart->shipping_address,
            'total_amount' => $totalAmount,
            'cart_id' => $cart->id,
        ]);

        // update cart status to completed
        $cart->status = 'completed';
        $cart->save();
        return response()->json([
            'message' => "Order created successfully",
            'order' => $order
        ], 200);
    }

    public function index(){
        $orders = Order::where('user_id', auth()->user()->id)->with('cart.items.product')->get();
        return response()->json($orders, 200);
    }
    
    
}
