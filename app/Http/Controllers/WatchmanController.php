<?php

namespace App\Http\Controllers;

use App\Models\Watchman;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WatchmanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $societyId = $request->user()?->society_id ?? $request->query('society_id');
        $q = Watchman::query();
        if ($societyId) $q->where('society_id', $societyId);
        return response()->json(['status' => true, 'watchmen' => $q->orderBy('name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'photo' => 'nullable|string',
            'employee_id' => 'nullable|string|max:100',
            'society_id' => 'nullable|integer|exists:societies,id',
            'active' => 'nullable|boolean',
        ]);

        $watchman = Watchman::create($data);
        return response()->json(['status' => true, 'watchman' => $watchman], 201);
    }
}
