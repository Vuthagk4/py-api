<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $avatarPath = 'users/default.jpg';

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $avatarPath = Storage::disk('public')->put('users', $image);
        }

        // 🟢 FIXED: Removed Hash::make() because the User model hashes it via 'casts'
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, 
            'avatar' => $avatarPath,
            'role' => 'user' 
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'message' => 'User created successfully',
            'token' => $token,
            'user' => $this->formatUserAvatar($user)
        ], 201);
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);
        
        $user = User::where('email', $request->email)->first();

        // 🟢 Hash::check still works perfectly with the 'hashed' cast
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // 🟢 Logic Improvement: Block both Admin and Shopkeepers from mobile login
        if (in_array($user->role, ['admin', 'shopkeeper'])) {
            return response()->json(['message' => 'Staff must use the web management panel.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUserAvatar($user)
        ]);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete(); 
        return response()->json(['message' => "Logout successfully"]);
    }

    public function update(Request $request) {
        $user = $request->user();

        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && !str_contains($user->avatar, 'default.jpg')) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = Storage::disk('public')->put('users', $request->file('avatar'));
        }

        // 🟢 FIXED: Removed Hash::make() here as well
        if ($request->password) $user->password = $request->password;
        if ($request->name) $user->name = $request->name;
        if ($request->email) $user->email = $request->email;
    
        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $this->formatUserAvatar($user)
        ]);
    }

    private function formatUserAvatar($user) {
        if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
            $user->avatar = asset('storage/' . $user->avatar);
        }
        return $user;
    }
}