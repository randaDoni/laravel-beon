<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Coba login dan generate token
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Email atau password salah'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Gagal membuat token'], 500);
        }

        // Login sukses, kirim token
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'success',
            'code' => 200
        ]);
    }

}
