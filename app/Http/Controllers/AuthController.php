<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request) {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:50',
                'phone' => 'required|max:11|unique:users',
                'password' => ['required', 'confirmed', Password::min(8)]
            ]);

            return DB::transaction(function () use ($validated){
                $user = User::create([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'password' => $validated['password']
                ]);

                Auth::login($user);

                $token = $user->createToken('auth-token')->plainTextToken;

                return response()->json([
                    'message' => 'با موفقیت ثبت نام شدید',
                    'token' => $token
                ]);
            });
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'ثبت نام با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function login(Request $request) {
        try {
            $validated = $request->validate([
                'phone' => 'required|exists:users',
                'password' => ['required', Password::min(8)]
            ]);

            if(!Auth::attempt($validated)) {
                return response()->json([
                    'message' => 'شماره تلفن یا رمز عبور نادرست است'
                ], 401);
            };

            $user = Auth::user();

            if($user->tokens()->exists()) {
                $user->tokens()->delete();
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'message' => 'ورود با موفقیت انجام شد',
                'token' => $token
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'ورود با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function profile() {
        $user = Auth::user();

        return response()->json([
            $user
        ]);
    }
}