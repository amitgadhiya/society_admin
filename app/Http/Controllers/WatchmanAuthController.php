<?php

namespace App\Http\Controllers;

use App\Models\Watchman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Storage;

class WatchmanAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile'   => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
        $watchman = Watchman::query()->where('mobile', $validated['mobile'])
            ->where('active', true)
            ->first();
        if (! $watchman || ! Hash::check($validated['password'], $watchman->password)) {
            return response()->json([
                'status'   => false,
                'message'  => 'Invalid credentials.'
            ]);     
        }
        $token = $watchman->createToken('watchman-token')->plainTextToken;
        
        return response()->json([
            'status'   => true,
            'message'  => 'Login successful.',
            'token'    => $token,
            'watchman' => $watchman,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $watchman = $request->user();
        $watchman->photo = Storage::disk('public')->url($watchman->photo);
        return response()->json([
            'status'   => true,
            'watchman' => $watchman,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully.',
        ]);
    }
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        /** @var Watchman $user */
        $watchman = $request->user();

        if (! Hash::check($validated['current_password'], $watchman->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $watchman->password = Hash::make($validated['password']);
        $watchman->save();

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully.',
        ]);
    }
}
