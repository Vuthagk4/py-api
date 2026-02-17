<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    
    public function store(Request $request)
    {
        $request->validate([
            'line1' => 'required|string|max:255',
            'line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric'
        ]);

        $address = Address::create($request->all());

        return response()->json([
            'message' => "Address created successfully",
            'address' => $address
        ], 200);
    }
    public function update(Request $request, $id)
    {
        $address = Address::find($id); // Find the address by ID if it exists
        if (!$address) {
            return response()->json([
                'message' => "Address not found"
            ], 404);
        }

        $address->update($request->all());

        return response()->json([
            'success' => true,
            'address' => $address
        ]);
    }
    public function index()
    {
        $user = auth()->user(); // Get the authenticated user
        // dd($user->id);
        $addresses = Address::where('user_id', $user->id)->get(); // Get addresses for the authenticated user
        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }
    public function destroy($id)
    {
        $address = Address::find($id); // Find the address by ID if it exists
        if (!$address) {
            return response()->json([
                'message' => "Address not found"
            ], 404);
        }

        $address->delete();

        return response()->json([
            'message' => "Address deleted successfully"
        ], 200);
    }
}
