<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\UnitMember;
use App\Models\Unit;

class VisitorController extends Controller
{
    /**
     * Return all visitors for the society (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'visitors' => []], 401);
        }

        $q = Visitor::with(['society', 'unit', 'watchman', 'createdBy'])->where('society_id', $user->society_id);
        return response()->json(['status' => true, 'visitors' => $q->orderByDesc('in_at')->get()]);
    }

    /**
     * Return visitors recorded for the logged-in user's unit(s).
     */
    public function myUnit(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'visitors' => []], 401);
        }

        // Find unit ids for which the user is a member
        $unitIds = UnitMember::query()->where('user_id', $user->id)->pluck('unit_id')->toArray();
        if (empty($unitIds)) {
            return response()->json(['status' => true, 'visitors' => []]);
        }

        $q = Visitor::with(['society', 'unit', 'watchman', 'createdBy'])->where('society_id', $user->society_id);

        // Filter by units the user is member of
        $q->whereIn('visit_to_unit_id', $unitIds);

        // Optional date-range filter (from / to — date strings YYYY-MM-DD).
        $from = $request->query('from');
        $to   = $request->query('to');
        if ($from) {
            $q->where('in_at', '>=', $from . ' 00:00:00');
        }
        if ($to) {
            $q->where('in_at', '<=', $to . ' 23:59:59');
        }

        $visitors = $q->orderByDesc('in_at')->get();
        return response()->json(['status' => true, 'visitors' => $visitors]);
    }

    /**
     * Store a new visitor record
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'visitor_name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'photo' => 'nullable|string',
            'in_at' => 'nullable|date',
            'visit_to_unit_id' => 'required|integer|exists:units,id',
            'watchman_id' => 'nullable|integer|exists:watchmen,id',
            'unit_id' => 'nullable|integer|exists:units,id',
            'reason' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,allowed,not_allowed',
            'remarks' => 'nullable|string',
            'rejection_reason' => 'nullable|string',
            'vehicle_number' => 'nullable|string|max:50',
            'id_proof' => 'nullable|string',
        ]);

        // Ensure the units belong to the user's society
        $visitUnit = Unit::find($data['visit_to_unit_id']);
        if (!$visitUnit || $visitUnit->society_id !== $user->society_id) {
            return response()->json(['status' => false, 'message' => 'Invalid unit'], 422);
        }

        if (isset($data['unit_id'])) {
            $addedByUnit = Unit::find($data['unit_id']);
            if (!$addedByUnit || $addedByUnit->society_id !== $user->society_id) {
                return response()->json(['status' => false, 'message' => 'Invalid unit_id'], 422);
            }
        }

        $data['society_id'] = $user->society_id;
        $data['created_by'] = $user->id;
        $data['in_at'] = now();

        $visitor = Visitor::create($data);

        // Generate a visitor code and OTP for gate entry
        try {
            $visitorCode = $visitor->visitor_code ?: ('V' . str_pad($visitor->id, 6, '0', STR_PAD_LEFT));
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $visitor->visitor_code = $visitorCode;
            $visitor->otp = $otp;
            $visitor->otp_expires_at = now()->addMinutes(15);
            $visitor->save();
        } catch (\Throwable $e) {
            // ignore generation errors, return created visitor anyway
        }

        return response()->json(['status' => true, 'visitor' => $visitor->load(['unit', 'watchman', 'createdBy'])], 201);
    }

    /**
     * Mark a visitor as checked out and update their status
     */
    public function checkout(Request $request, Visitor $visitor): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->society_id !== $visitor->society_id) {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'out_at' => 'nullable|date',
            'status' => 'nullable|in:pending,allowed,not_allowed',
            'remarks' => 'nullable|string',
            'rejection_reason' => 'nullable|string',
        ]);

        $visitor->out_at = $data['out_at'] ?? now();
        
        if (isset($data['status'])) {
            $visitor->status = $data['status'];
        }
        
        if (isset($data['remarks'])) {
            $visitor->remarks = $data['remarks'];
        }
        
        if (isset($data['rejection_reason'])) {
            $visitor->rejection_reason = $data['rejection_reason'];
        }
        
        $visitor->save();
        
        return response()->json(['status' => true, 'visitor' => $visitor->load(['unit', 'watchman', 'createdBy'])]);
    }
}
