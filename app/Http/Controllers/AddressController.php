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
        'full_name' => 'required|string',
        'phone'     => 'required|string',
        'street'    => 'required|string',
        'latitude'  => 'required|numeric',
        'longitude' => 'required|numeric',
    ]);

    $address = Address::create([
        'user_id'   => auth()->id(),
        'full_name' => $request->full_name,
        'phone'     => $request->phone,
        'street'    => $request->street,
        'latitude'  => $request->latitude,
        'longitude' => $request->longitude,
        'country'   => 'Cambodia',
    ]);

    return response()->json(['message' => 'Success', 'address' => $address], 201);
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
