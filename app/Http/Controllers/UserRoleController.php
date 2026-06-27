<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $users = User::query()
            ->where('society_id', $authUser->society_id)
            ->orderBy('name')
            ->get(['id', 'name', 'mobile', 'email', 'role', 'status', 'society_id']);

        return response()->json([
            'status' => true,
            'users' => $users,
        ]);
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureAdmin($authUser);
        $this->ensureSameSociety($authUser->society_id, $user->society_id);

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:owner,admin,secretary,treasurer'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        if ($authUser->id === $user->id && $validated['role'] !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'You cannot remove your own admin role.',
            ], 422);
        }

        $user->update([
            'role' => $validated['role'],
            'status' => $validated['status'] ?? $user->status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User role updated successfully.',
            'user' => $user,
        ]);
    }

    private function ensureAdmin(User $user): void
    {
        abort_unless($user->hasAnyRole(['admin']), 403, 'Only admin can perform this action.');
    }

    private function ensureSameSociety(?int $expectedSocietyId, ?int $actualSocietyId): void
    {
        abort_unless($expectedSocietyId !== null && $expectedSocietyId === $actualSocietyId, 403, 'Record does not belong to your society.');
    }
}
