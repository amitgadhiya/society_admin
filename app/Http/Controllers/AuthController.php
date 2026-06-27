<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\WhatsAppService;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20', 'unique:users,mobile'],
            'society_id' => ['required', 'integer', 'exists:societies,id'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($validated);
        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Registered successfully.',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('mobile', $validated['mobile'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'mobile' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'user' => $request->user(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    public function sendResetOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string'],
        ]);

        $mobile = $validated['mobile'];

        $user = User::query()->where('mobile', $mobile)->first();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'No account found for this mobile number.',
            ], 404);
        }

        // Generate 6-digit OTP
        $token = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in password_reset_tokens table using the mobile as the key (email column)
        DB::table(config('auth.passwords.users.table', 'password_reset_tokens'))
            ->updateOrInsert(
                ['email' => $mobile],
                ['token' => $token, 'created_at' => Carbon::now()]
            );

        // Try sending OTP via WhatsApp if configured
        $sentInfo = null;
        try {
            $sentInfo = WhatsAppService::sendOtp($mobile, $token);
        } catch (\Throwable $e) {
            // Log but don't fail the request
            report($e);
            // capture exception message for debug output
            $sentException = $e->getMessage();
        }

        $response = [
            'status' => true,
            'message' => 'OTP generated and stored.',
        ];

        // For development/testing expose the token and provider response when WHATSAPP_DEBUG=true
        if (env('WHATSAPP_DEBUG', false)) {
            $response['token'] = $token;
            if ($sentInfo !== null) {
                $response['sent'] = $sentInfo;
            }
            if (! empty($sentException)) {
                $response['error'] = $sentException;
            }
        }

        return response()->json($response);
    }

    public function verifyResetOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string'],
            'token' => ['required', 'string'],
        ]);

        $row = DB::table(config('auth.passwords.users.table', 'password_reset_tokens'))
            ->where('email', $validated['mobile'])
            ->first();

        if (! $row || ! hash_equals($row->token, $validated['token'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        // Optionally check expiry (e.g., 30 minutes)
        $created = Carbon::parse($row->created_at ?? now());
        if ($created->diffInMinutes(Carbon::now()) > 30) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired. Please request a new one.',
            ], 422);
        }

        return response()->json(['status' => true, 'message' => 'OTP verified']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $row = DB::table(config('auth.passwords.users.table', 'password_reset_tokens'))
            ->where('email', $validated['mobile'])
            ->first();

        if (! $row || ! hash_equals($row->token, $validated['token'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired token.',
            ], 422);
        }

        $user = User::query()->where('mobile', $validated['mobile'])->first();
        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        // Delete the used token
        DB::table(config('auth.passwords.users.table', 'password_reset_tokens'))
            ->where('email', $validated['mobile'])
            ->delete();

        return response()->json(['status' => true, 'message' => 'Password reset successful']);
    }
}
