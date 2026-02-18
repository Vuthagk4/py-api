<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $avatarPath = 'default.jpg'; // Default value from your Navicat SQL

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            // Store in storage/app/public/users
            $avatarPath = Storage::disk('public')->put('users', $image);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar' => $avatarPath // Save the path string
        ]);
        
        return response()->json([
            'message' => 'User created successfully',
        ], 200);
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);
        
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Ensure the avatar shows the full URL in the response
        if ($user->avatar && $user->avatar !== 'default.jpg') {
            $user->avatar = asset('storage/' . $user->avatar);
        } else {
            $user->avatar = asset('storage/users/default.jpg');
        }

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request) {
        $request->user()->tokens()->delete();
        return response()->json(['message' => "Logout successfully"]);
    }

    public function update(Request $request) {
        $user = $request->user();

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $path = Storage::disk('public')->put('users', $image);
            
            // Delete old avatar if it's not the default
            if ($user->avatar && $user->avatar !== 'default.jpg' && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $path;
        }

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        // Only update name if it is provided in the request
        if ($request->name) {
            $user->name = $request->name;
        }
    
        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }
}