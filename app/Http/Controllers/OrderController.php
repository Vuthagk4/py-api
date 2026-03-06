<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();

        // 🟢 1. Validation updated with Address and GPS fields
        $request->validate([
            'total_amount'   => 'required|numeric',
            'shopkeeper_id'  => 'required|integer',
            'address_id'     => 'required|exists:addresses,id', // 🟢 Ensure address exists
            'latitude'       => 'nullable|numeric',            // 🟢 New GPS field
            'longitude'      => 'nullable|numeric',            // 🟢 New GPS field
            'items'          => 'required',
            'image_qrcode'   => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        try {
            return DB::transaction(function () use ($user, $request) {
                
                // 🟢 2. Handle Image Upload
                $imagePath = null;
                if ($request->hasFile('image_qrcode')) {
                    $imagePath = $request->file('image_qrcode')->store('qrcodes', 'public');
                }

                // 🟢 3. Create the Order with Address and Location
                $order = Order::create([
                    'user_id'       => $user->id,
                    'address_id'    => $request->address_id,   // 🟢 Store Address ID
                    'latitude'      => $request->latitude,     // 🟢 Store Latitude
                    'longitude'     => $request->longitude,    // 🟢 Store Longitude
                    'total_amount'  => $request->total_amount,
                    'status'        => $imagePath ? 'PENDING' : 'UNPAID', 
                    'shopkeeper_id' => $request->shopkeeper_id,
                    'image_qrcode'  => $imagePath,
                ]);

                // 🟢 4. Decode items safely
                $items = is_array($request->items) ? $request->items : json_decode($request->items, true);
                
                if (!$items) {
                    throw new \Exception("Invalid items format received");
                }

                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item['product']['id'],
                        'quantity'   => $item['quantity'],
                        'price'      => $item['product']['price']
                    ]);
                }

                return response()->json([
                    'message' => "Order placed successfully",
                    'order_id' => $order->id,
                    'image_url' => $imagePath ? asset('storage/' . $imagePath) : null,
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error("Checkout Error: " . $e->getMessage());
            
            return response()->json([
                'message' => 'Upload Failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        // 🟢 Updated to include the address relationship for the order history
        $orders = Order::where('user_id', $request->user()->id)
                    ->with(['items.product', 'address']) 
                    ->latest()
                    ->get();

        $orders->map(function ($order) {
            if ($order->image_qrcode) {
                $order->image_qrcode = asset('storage/' . $order->image_qrcode);
            }
            return $order;
        });

        return response()->json($orders, 200);
    }

    public function confirmPayment($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'COMPLETED']); 
        return response()->json(['message' => 'Payment verified!']);
    }
}