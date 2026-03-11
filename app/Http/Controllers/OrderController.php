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

        $request->validate([
            'total_amount'     => 'required|numeric',
            'shopkeeper_id'    => 'required|integer',
            'address_id'       => 'nullable|exists:addresses,id',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'delivery_address' => 'nullable|string',
            'phone'            => 'nullable|string|max:20',
            'items'            => 'required',
            'image_qrcode'     => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        try {
            return DB::transaction(function () use ($user, $request) {

                $imagePath = null;
                if ($request->hasFile('image_qrcode')) {
                    $imagePath = $request->file('image_qrcode')
                        ->store('qrcodes', 'public');
                }

                $order = Order::create([
                    'user_id'          => $user->id,
                    'address_id'       => $request->address_id ?? null,
                    'latitude'         => $request->latitude,
                    'longitude'        => $request->longitude,
                    'delivery_address' => $request->delivery_address, // ✅
                    'phone'            => $request->phone,
                    'total_amount'     => $request->total_amount,
                    'status'           => $imagePath ? 'PENDING' : 'UNPAID',
                    'shopkeeper_id'    => $request->shopkeeper_id,
                    'image_qrcode'     => $imagePath,
                ]);

                $items = is_array($request->items)
                    ? $request->items
                    : json_decode($request->items, true);

                if (!$items) {
                    throw new \Exception("Invalid items format received");
                }

                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item['product']['id'],
                        'quantity'   => $item['quantity'],
                        'price'      => $item['product']['price'],
                        'size'       => $item['size'] ?? null,
                    ]);
                }

                return response()->json([
                    'message'  => "Order placed successfully",
                    'order_id' => $order->id,
                    'image_url' => $imagePath
                        ? asset('storage/' . $imagePath)
                        : null,
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