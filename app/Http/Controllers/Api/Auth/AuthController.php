<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'phone' => 'required|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        $this->create_favorite_cart($user);
        return response()->json(['token' => $user->createToken('api-token')->plainTextToken]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
    public function create_favorite_cart(User $user){
        if (!Favorite::where('user_id', $user->id)->exists()){
            $favorite = Favorite::create([
                'user_id' => $user->id,
            ]);
        }if (!Cart::where('user_id', $user->id)->exists()){
            $cart = Cart::create([
                'user_id' => $user->id,
            ]);
        }
       
    }

}
