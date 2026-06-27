<?php
namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Visitor;
use App\Models\Watchman;
use App\Models\Wing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class WatchmanVisitorController extends Controller
{
    /** List all visitors for the watchman's society */
    public function index(Request $request): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();

        // Counts: today only
        $countBase = Visitor::where('society_id', $watchman->society_id)
            ->whereDate('created_at', Carbon::today());

        $counts = [
            'total'   => (clone $countBase)->count(),
            'in'      => (clone $countBase)->whereNull('out_at')->whereNotNull('in_at')->count(),
            'out'     => (clone $countBase)->whereNotNull('out_at')->count(),
            'pending' => (clone $countBase)->where('status', 'pending')->count(),
        ];

        // Visitors list: still inside from last 3 days + all of today
        $cutoff = Carbon::now()->subDays(3)->startOfDay();
        $visitors = Visitor::where('society_id', $watchman->society_id)
            ->where(function ($q) use ($cutoff) {
                $q->whereDate('created_at', Carbon::today())
                  ->orWhere(function ($q2) use ($cutoff) {
                      $q2->whereNull('out_at')
                         ->where('created_at', '>=', $cutoff);
                  });
            })
            ->with(['unit', 'watchman'])
            ->orderByDesc('created_at')
            ->get();

        foreach ($visitors as $visitor) {
            if ($visitor->photo != null && $visitor->photo != '') {
                $visitor->photo = Storage::disk('public')->url($visitor->photo);
            }
        }

        return response()->json(['status' => true, 'counts' => $counts, 'visitors' => $visitors]);
    }

    /** Get a single visitor record */
    public function show(Request $request, Visitor $visitor): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();

        if ($visitor->society_id !== $watchman->society_id) {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }
        if($visitor->photo!=null or $visitor->photo!='')
            $visitor->photo = Storage::disk('public')->url($visitor->photo);
              
        return response()->json(['status' => true, 'visitor' => $visitor->load(['unit', 'watchman'])]);
    }

    public function getUnit(Request $request): JsonResponse{

        $watchman = $request->user();
        $units= Unit::where('society_id',$watchman->society_id)->get();
        if (!$units) {
            return response()->json(['status' => false, 'message' => 'no unit found']);
        }
        return response()->json(['status' => true, 'units' => $units]);

    }

    public function getWings(Request $request): JsonResponse
    {
        $watchman = $request->user();
        $wings = Wing::where('society_id', $watchman->society_id)->get();
        return response()->json(['status' => true, 'wings' => $wings]);
    }

    public function getUnitsByWing(Request $request): JsonResponse
    {
        $watchman = $request->user();
        $wingId = $request->query('wing_id');

        $query = Unit::with('wing')->where('society_id', $watchman->society_id);

        if ($wingId) {
            $wing = Wing::find($wingId);
            if (!$wing || $wing->society_id !== $watchman->society_id) {
                return response()->json(['status' => false, 'message' => 'Invalid wing'], 422);
            }
            $query->where('wing_id', $wingId);
        }

        return response()->json(['status' => true, 'units' => $query->get()]);
    }
    /** Record a new visitor entry */
    public function store(Request $request): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();
        $data = $request->validate([
            'visitor_name'     => 'required|string|max:255',
            'mobile'           => 'nullable|string|max:50',
            'photo'            => 'nullable|image',
            'visit_to_unit_id' => 'required|integer|exists:units,id',
            'reason'           => 'nullable|string|max:255',
            'vehicle_number'   => 'nullable|string|max:50',
            'id_proof'         => 'nullable|string',
        ]);
        $unit = Unit::find($data['visit_to_unit_id']);
        if (! $unit || $unit->society_id !== $watchman->society_id) {
            return response()->json(['status' => false, 'message' => 'Invalid unit'], 422);
        }
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('visitors','public');
            $data['photo'] = $path;
        }
        $data['society_id']  = $watchman->society_id;
        $data['watchman_id'] = $watchman->id;
        $data['status']      = 'pending';

        $visitor = Visitor::create($data);

        return response()->json([
            'status'  => true,
            'message'=>'Visitor added successfully!',
            'visitor' => $visitor->load(['unit', 'watchman']),
        ], 201);
    }
    /** Update a visitor record */
    public function update(Request $request, Visitor $visitor): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();

        if ($visitor->society_id !== $watchman->society_id) {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'visitor_name'     => 'sometimes|string|max:255',
            'mobile'           => 'nullable|string|max:50',
            'photo'            => 'nullable|image',
            'visit_to_unit_id' => 'sometimes|integer|exists:units,id',
            'reason'           => 'nullable|string|max:255',
            'vehicle_number'   => 'nullable|string|max:50',
            'id_proof'         => 'nullable|string',
            'status'           => 'nullable|in:pending,allowed,not_allowed',
            'remarks'          => 'nullable|string',
            'rejection_reason' => 'nullable|string',
        ]);

        if (isset($data['visit_to_unit_id'])) {
            $unit = Unit::find($data['visit_to_unit_id']);
            if (! $unit || $unit->society_id !== $watchman->society_id) {
                return response()->json(['status' => false, 'message' => 'Invalid unit'], 422);
            }
        }

        if ($request->hasFile('photo')) {
            if ($visitor->photo) {
                Storage::disk('public')->delete($visitor->photo);
            }
            $data['photo'] = $request->file('photo')->store('visitors', 'public');
        }

        $visitor->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'Visitor updated successfully!',
            'visitor' => $visitor->load(['unit', 'watchman']),
        ]);
    }

    public function checkout(Request $request, Visitor $visitor): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();

        if ($visitor->society_id !== $watchman->society_id) {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }
        $visitor->out_at = now();
        $visitor->save();

        return response()->json(['status' => true, 'message'=>"Checkout succesfully!"]);
    }
    /** Mark a visitor as checked out */
    public function checkout1(Request $request, Visitor $visitor): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();

        if ($visitor->society_id !== $watchman->society_id) {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }

        $visitor->out_at = now();

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

        return response()->json(['status' => true, 'visitor' => $visitor->load(['unit', 'watchman'])]);
    }
}
