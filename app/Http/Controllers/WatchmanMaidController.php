<?php

namespace App\Http\Controllers;

use App\Models\Maid;
use App\Models\MaidEntryLog;
use App\Models\MaidUnitAssignment;
use App\Models\Watchman;
use App\Models\Unit;
use App\Services\NotificationService;
use App\Services\WatchmanNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WatchmanMaidController extends Controller
{
    /** List today's maid entry logs for the watchman's society */
    public function index(Request $request): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();

        $logs = MaidEntryLog::where('society_id', $watchman->society_id)
            ->whereDate('enter_time', Carbon::today())
            ->with(['maid', 'watchman'])
            ->orderByDesc('enter_time')
            ->get()
            ->map(fn($log) => $this->formatLog($log));

        $counts = [
            'total'   => $logs->count(),
            'entered' => $logs->where('status', 'enter')->count(),
            'exited'  => $logs->where('status', 'exit')->count(),
        ];

        return response()->json(['status' => true, 'counts' => $counts, 'logs' => $logs]);
    }

    /** List active maids in the watchman's society who have not entered today */
    public function maids(Request $request): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();

        $enteredMaidIds = MaidEntryLog::where('society_id', $watchman->society_id)
            ->whereDate('enter_time', Carbon::today())
            ->where('status', 'enter')
            ->pluck('maid_id');

        $maids = Maid::where('society_id', $watchman->society_id)
            ->where('status', 'active')
            ->whereNotIn('id', $enteredMaidIds)
            ->orderBy('name')
            ->get()
            ->map(fn($maid) => [
                'id'     => $maid->id,
                'name'   => $maid->name,
                'mobile' => $maid->mobile,
                'photo'  => $maid->photo ? Storage::url($maid->photo) : null,
            ]);

        return response()->json(['status' => true, 'maids' => $maids]);
    }

    /** Log maid entry */
    public function enter(Request $request): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();

        $data = $request->validate([
            'maid_id' => ['required', 'integer', 'exists:maids,id'],
        ]);

        $maid = Maid::find($data['maid_id']);
        if ($maid->society_id !== $watchman->society_id) {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }

        // Check if maid already has an open entry today (entered but not exited)
        $open = MaidEntryLog::where('maid_id', $maid->id)
            ->where('status', 'enter')
            ->whereDate('enter_time', Carbon::today())
            ->first();

        if ($open) {
            return response()->json([
                'status'  => false,
                'message' => 'Maid already has an open entry. Record exit first.',
            ], 422);
        }

        $log = MaidEntryLog::create([
            'society_id'  => $watchman->society_id,
            'maid_id'     => $maid->id,
            'watchman_id' => $watchman->id,
            'enter_time'  => now(),
            'status'      => 'enter',
        ]);
        $maidAllowedAssignment = MaidUnitAssignment::where('maid_id', $maid->id)->where('is_permitted', 'allowed')->pluck('unit_id');
        $maidPendingAssignment = MaidUnitAssignment::where('maid_id', $maid->id)->where('is_permitted', 'pending')->pluck('unit_id');
        $this->notifyAllowedUnits($maid, $log, $watchman->society_id, $maidAllowedAssignment);
        $this->notifyPendingUnits($maid, $log, $watchman->society_id, $maidPendingAssignment);
        return response()->json([
            'status'  => true,
            'message' => 'Maid entry successfully.',
            'log'     => $this->formatLog($log->load(['maid', 'watchman'])),
        ], 201);
    }

    /** Log maid exit */
    public function exit(Request $request, MaidEntryLog $log): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();

        if ($log->society_id !== $watchman->society_id) {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }

        if ($log->status === 'exit') {
            return response()->json(['status' => false, 'message' => 'Exit already recorded.'], 422);
        }

        $log->exit_time = now();
        $log->status    = 'exit';
        $log->save();

        $maid = $log->maid ?? Maid::find($log->maid_id);
        $maidAssignment = MaidUnitAssignment::where('maid_id', $log->maid_id)->where('is_permitted', 'allowed')->pluck('unit_id');
        $this->notifyExitUnits($maid, $log, $watchman->society_id, $maidAssignment);

        return response()->json([
            'status'  => true,
            'message' => 'Maid exit logged successfully.',
            'log'     => $this->formatLog($log->load(['maid', 'watchman'])),
        ]);
    }

    /** Get a single log */
    public function show(Request $request, MaidEntryLog $log): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();

        if ($log->society_id !== $watchman->society_id) {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }

        return response()->json([
            'status' => true,
            'log'    => $this->formatLog($log->load(['maid', 'watchman'])),
        ]);
    }

    private function notifyAllowedUnits(Maid $maid, MaidEntryLog $log, int $societyId, \Illuminate\Support\Collection $unitIds): void
    {
        $notifiedUserIds = [];
        $units = Unit::query()
            ->with(['members.user'])
            ->where('society_id', $societyId)
            ->whereIn('id', $unitIds)
            ->where('status', 'active')
            ->get();

        foreach ($units as $unit) {
            foreach ($unit->members as $member) {
                $user = $member->user;
                if (! $user || in_array($user->id, $notifiedUserIds, true)) {
                    continue;
                }
                $notifiedUserIds[] = $user->id;
                NotificationService::notify(
                    $user->id,
                    'Maid Entry — ' . $maid->name,
                    $maid->name . ' has entered the society at ' . now()->format('h:i A') . '.',
                    'maid_entry',
                    ['maid_id' => $maid->id, 'log_id' => $log->id]
                );
            }
        }
    }

    private function notifyPendingUnits(Maid $maid, MaidEntryLog $log, int $societyId, \Illuminate\Support\Collection $unitIds): void
    {
        $notifiedUserIds = [];
        $units = Unit::query()
            ->with(['members.user'])
            ->where('society_id', $societyId)
            ->whereIn('id', $unitIds)
            ->where('status', 'active')
            ->get();

        foreach ($units as $unit) {
            foreach ($unit->members as $member) {
                $user = $member->user;
                if (! $user || in_array($user->id, $notifiedUserIds, true)) {
                    continue;
                }
                $notifiedUserIds[] = $user->id;
                NotificationService::notify(
                    $user->id,
                    'Maid Entry Request — ' . $maid->name,
                    $maid->name . ' is at the gate and awaiting your approval to enter.',
                    'maid_entry_request',
                    ['maid_id' => $maid->id, 'log_id' => $log->id]
                );
            }
        }
    }

    private function notifyExitUnits(Maid $maid, MaidEntryLog $log, int $societyId, \Illuminate\Support\Collection $unitIds): void
    {
        $notifiedUserIds = [];
        $units = Unit::query()
            ->with(['members.user'])
            ->where('society_id', $societyId)
            ->whereIn('id', $unitIds)
            ->where('status', 'active')
            ->get();

        foreach ($units as $unit) {
            foreach ($unit->members as $member) {
                $user = $member->user;
                if (! $user || in_array($user->id, $notifiedUserIds, true)) {
                    continue;
                }
                $notifiedUserIds[] = $user->id;
                NotificationService::notify(
                    $user->id,
                    'Maid Exit — ' . $maid->name,
                    $maid->name . ' has exited the society at ' . now()->format('h:i A') . '.',
                    'maid_exit',
                    ['maid_id' => $maid->id, 'log_id' => $log->id]
                );
            }
        }
    }

    /** POST /watchman/maid-logs/test-notification — send a test push to the watchman */
    public function testNotification(Request $request): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();

        $data = $request->validate([
            'type' => 'nullable',
        ]);

        $type = $data['type'] ?? 'maid_entry';

        if ($type === 'maid_exit') {
            $title = 'Maid Exit Test';
            $body  = 'This is a test maid exit notification.';
        } else {
            $title = 'Maid Entry Test';
            $body  = 'This is a test maid entry notification.';
        }

        WatchmanNotificationService::notify($watchman->id, $title, $body, $type, ['test' => true]);

        return response()->json(['status' => true, 'message' => 'Test notification sent.', 'type' => $type]);
    }

    private function formatLog(MaidEntryLog $log): array
    {
        $maid = $log->relationLoaded('maid') ? $log->maid : null;

        return [
            'id'          => $log->id,
            'status'      => $log->status,
            'enter_time'  => $log->enter_time?->toDateTimeString(),
            'exit_time'   => $log->exit_time?->toDateTimeString(),
            'maid'        => $maid ? [
                'id'     => $maid->id,
                'name'   => $maid->name,
                'mobile' => $maid->mobile,
                'photo'  => $maid->photo ? Storage::url($maid->photo) : null,
            ] : null,
            'watchman_id' => $log->watchman_id,
            'created_at'  => $log->created_at?->toDateTimeString(),
        ];
    }
    
}
