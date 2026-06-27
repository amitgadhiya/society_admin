<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Unit;
use App\Models\UnitMember;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    private array $managerRoles = ['admin', 'secretary', 'treasurer', 'accountant'];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $isManager = in_array($user->role, $this->managerRoles, true);

        $query = Complaint::query()
            ->where('society_id', $user->society_id)
            ->with(['unit:id,unit_number', 'user:id,name']);

        if (!$isManager) {
            $unitIds = UnitMember::query()
                ->where('society_id', $user->society_id)
                ->where('user_id', $user->id)
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
                })
                ->pluck('unit_id');

            $query->whereIn('unit_id', $unitIds);
        }

        $complaints = $query->orderByDesc('created_at')
            ->get()
            ->map(fn (Complaint $c) => $this->format($c));

        return response()->json(['status' => true, 'complaints' => $complaints]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'unit_id'      => 'required|integer|exists:units,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'before_image' => 'nullable|image|max:4096',
        ]);

        $unit = Unit::findOrFail($validated['unit_id']);
        abort_unless($unit->society_id === $user->society_id, 403, 'Access denied.');

        $isManager = in_array($user->role, $this->managerRoles, true);
        if (!$isManager) {
            $isMember = UnitMember::query()
                ->where('unit_id', $unit->id)
                ->where('user_id', $user->id)
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
                })
                ->exists();
            abort_unless($isMember, 403, 'You are not a member of this unit.');
        }

        $imagePath = null;
        if ($request->hasFile('before_image')) {
            $imagePath = $request->file('before_image')->store('complaints', 'public');
        }

        $complaint = Complaint::create([
            'society_id'  => $user->society_id,
            'unit_id'     => $validated['unit_id'],
            'user_id'     => $user->id,
            'created_by'  => $user->id,
            'updated_by'  => $user->id,
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status'      => 'open',
            'before_image' => $imagePath,
        ]);

        // Notify all admins/managers about the new complaint
        $adminUsers = User::query()
            ->where('society_id', $user->society_id)
            ->whereIn('role', ['admin', 'secretary', 'treasurer', 'accountant'])
            ->pluck('id')
            ->toArray();
        
        foreach ($adminUsers as $adminId) {
            NotificationService::notify(
                $adminId,
                'New Complaint Raised',
                "A new complaint has been raised: {$validated['title']} in unit {$unit->unit_number} by {$user->name}.",
                'complaint',
                ['complaint_id' => $complaint->id, 'complaint_title' => $validated['title']]
            );
        }

        return response()->json([
            'status'    => true,
            'complaint' => $this->format($complaint->load(['unit:id,unit_number', 'user:id,name'])),
        ], 201);
    }

    public function show(Request $request, Complaint $complaint): JsonResponse
    {
        $user = $request->user();
        abort_unless($complaint->society_id === $user->society_id, 403, 'Access denied.');

        $isManager = in_array($user->role, $this->managerRoles, true);
        if (!$isManager) {
            $isMember = UnitMember::query()
                ->where('unit_id', $complaint->unit_id)
                ->where('user_id', $user->id)
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
                })
                ->exists();
            abort_unless($isMember || $complaint->user_id === $user->id, 403, 'Access denied.');
        }

        return response()->json([
            'status'    => true,
            'complaint' => $this->format($complaint->load(['unit:id,unit_number', 'user:id,name'])),
        ]);
    }

    public function update(Request $request, Complaint $complaint): JsonResponse
    {
        $user = $request->user();
        abort_unless($complaint->society_id === $user->society_id, 403, 'Access denied.');

        $isManager = in_array($user->role, $this->managerRoles, true);
        $isOwner   = $complaint->user_id === $user->id;
        abort_unless($isManager || $isOwner, 403, 'You cannot edit this complaint.');
        abort_if($complaint->status === 'closed', 422, 'Cannot edit a closed complaint.');

        $validated = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'before_image' => 'nullable|image|max:4096',
        ]);

        $data = ['updated_by' => $user->id];
        if (isset($validated['title'])) {
            $data['title'] = $validated['title'];
        }
        if (array_key_exists('description', $validated)) {
            $data['description'] = $validated['description'];
        }
        if ($request->hasFile('before_image')) {
            if ($complaint->before_image) {
                Storage::disk('public')->delete($complaint->before_image);
            }
            $data['before_image'] = $request->file('before_image')->store('complaints', 'public');
        }

        $complaint->update($data);

        return response()->json([
            'status'    => true,
            'complaint' => $this->format($complaint->load(['unit:id,unit_number', 'user:id,name'])),
        ]);
    }

    public function close(Request $request, Complaint $complaint): JsonResponse
    {
        $user = $request->user();
        abort_unless($complaint->society_id === $user->society_id, 403, 'Access denied.');
        abort_unless(in_array($user->role, $this->managerRoles, true), 403, 'Only managers can close complaints.');
        abort_if($complaint->status === 'closed', 422, 'Complaint is already closed.');

        $validated = $request->validate([
            'remark_after_solution' => 'nullable|string',
            'after_image'           => 'nullable|image|max:4096',
        ]);

        $data = [
            'status'                => 'closed',
            'closed_by'             => $user->id,
            'closed_on'             => now(),
            'updated_by'            => $user->id,
            'remark_after_solution' => $validated['remark_after_solution'] ?? null,
        ];

        if ($request->hasFile('after_image')) {
            if ($complaint->after_image) {
                Storage::disk('public')->delete($complaint->after_image);
            }
            $data['after_image'] = $request->file('after_image')->store('complaints', 'public');
        }

        $complaint->update($data);

        // Notify the user who raised the complaint that it has been resolved
        $complaintUser = User::find($complaint->user_id);
        if ($complaintUser) {
            NotificationService::notify(
                $complaintUser->id,
                'Complaint Resolved',
                "Your complaint '{$complaint->title}' has been solved. Please review the solution and provide your feedback.",
                'complaint_resolved',
                ['complaint_id' => $complaint->id, 'complaint_title' => $complaint->title]
            );
        }

        return response()->json([
            'status'    => true,
            'complaint' => $this->format($complaint->load(['unit:id,unit_number', 'user:id,name'])),
        ]);
    }

    public function rate(Request $request, Complaint $complaint): JsonResponse
    {
        $user = $request->user();
        abort_unless($complaint->society_id === $user->society_id, 403, 'Access denied.');
        abort_unless($complaint->user_id === $user->id, 403, 'Only the complaint owner can rate.');
        abort_unless($complaint->status === 'closed', 422, 'Can only rate a closed complaint.');

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $complaint->update(['rating' => $validated['rating']]);

        return response()->json([
            'status'    => true,
            'complaint' => $this->format($complaint->load(['unit:id,unit_number', 'user:id,name'])),
        ]);
    }

    private function format(Complaint $complaint): array
    {
        $baseUrl  = rtrim(config('app.url'), '/');
        $imageUrl = fn (?string $path) => $path ? $baseUrl . '/storage/' . $path : null;

        return [
            'id'                    => $complaint->id,
            'society_id'            => $complaint->society_id,
            'unit_id'               => $complaint->unit_id,
            'unit_number'           => $complaint->unit?->unit_number,
            'user_id'               => $complaint->user_id,
            'user_name'             => $complaint->user?->name,
            'created_by'            => $complaint->created_by,
            'updated_by'            => $complaint->updated_by,
            'closed_by'             => $complaint->closed_by,
            'closed_on'             => $complaint->closed_on?->toDateTimeString(),
            'title'                 => $complaint->title,
            'description'           => $complaint->description,
            'status'                => $complaint->status,
            'before_image_url'      => $imageUrl($complaint->before_image),
            'after_image_url'       => $imageUrl($complaint->after_image),
            'remark_after_solution' => $complaint->remark_after_solution,
            'rating'                => $complaint->rating,
            'created_at'            => $complaint->created_at?->toDateTimeString(),
            'updated_at'            => $complaint->updated_at?->toDateTimeString(),
        ];
    }
}
