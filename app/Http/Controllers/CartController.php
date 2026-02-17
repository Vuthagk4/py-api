<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function addToCart(Request $request){
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        //get corrent user logged in 
        $user = $request->user();
        $cart = Cart::firstOrCreate([
            'user_id' => $user->id,
            'status' => 'active'
        ]);
        $cartItem = $cart->items()->where('product_id',$request->product_id)->first();
        if($cartItem){
            $cartItem->quantity += $request->quantity;
            $cartItem->price = $request->price * $cartItem->quantity;
            $cartItem->cart_id = $cart->id;
            $cartItem->save();
        }else{
            $cart->items()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'cart_id' => $cart->id
            ]);
        }
        $cart->total = $cart->items()->sum('price');
        $cart->save();
        return response()->json([
            'message' => 'Product added to cart successfully',
            'cart' => $cart->load('items.product')
        ], 200);

    }

    public function viewCart(Request $request){
        $user = $request->user(); /// get current user

        $carts = Cart::where('user_id',$user->id)->where('status','active')->with('items.product')->first([
            'id',
            'user_id',
            'total',
            'status'
        ]);
        // dd($carts->items);
        if($carts == null){
            return response()->json([
                'cart' => [],
                'total' => 0,
                'count' => 0
            ], 200);
        }
        $cartItems = $carts ? $carts->items : [];
        $total = 0;
        $count = 0;
        foreach($cartItems as $item){
            $total += $item->price;
            $count += $item->quantity;
            
            $item->product->image = asset('storage/'.$item->product->image);
        }
        return response()->json([
            'cart' => $carts,
            'total' => $total,
            'count' => $count
        ], 200);
    }

    public function removeFromCart($proId){
        $user = Auth::user(); // get current user
        $cart = Cart::where('user_id',$user->id)->where('status','active')->first();
        if($cart == null){
            return response()->json([
                'message' => 'Cart is empty'
            ], 404);
        }
        $cartItem = $cart->items()->where('product_id',$proId)->first();

        if($cartItem == null){
            return response()->json([
                'message' => 'Product not found in cart'
            ], 404);
        }
        $cartItem->delete();
        $cart->total = $cart->items()->sum('price');
        $cart->save();
        return response()->json([
            'message' => 'Product removed from cart successfully',
            'cart' => $cart->load('items.product')
        ], 200);
    }
    public function clearCart(){
        $user = Auth::user();
        $cartIds = [];
        $carts = Cart::where('user_id',$user->id)->get();
        if($carts == null){
            return response()->json([
                'message' => 'Cart is empty'
            ], 404);
        }
        foreach($carts as $cart){
            $cartIds[] = $cart->id;
        }
        CartItem::whereIn('cart_id',$cartIds)->delete();
        Cart::where('user_id',$user->id)->where('status','active')->delete();
       
        return response()->json([
            'message' => 'Cart cleared successfully'
        ], 200);


    }
}
