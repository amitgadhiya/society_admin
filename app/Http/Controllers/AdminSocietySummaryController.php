<?php

namespace App\Http\Controllers;

use App\Models\PaymentReceipt;
use App\Models\Unit;
use App\Models\Wing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSocietySummaryController extends Controller
{
    public function index(Request $request)
    {
        $societyId = Auth::user()->society_id;

        $wings = Wing::where('society_id', $societyId)->orderBy('name')->get();

        $dateMode   = in_array($request->input('date_mode'), ['single', 'range']) ? $request->input('date_mode') : 'single';
        $date       = $request->input('date');
        $from       = $request->input('from');
        $to         = $request->input('to');
        $billStatus = in_array($request->input('status'), ['unpaid', 'overdue', 'paid']) ? $request->input('status') : null;

        $unitQuery = Unit::where('society_id', $societyId)->orderBy('unit_number');
        if ($request->filled('wing_id')) {
            $unitQuery->where('wing_id', $request->wing_id);
        }
        $units = $unitQuery->get();

        $evalQuery = Unit::with('wing')
            ->where('society_id', $societyId)
            ->where('status', 'active')
            ->orderBy('unit_number');

        if ($request->filled('wing_id')) {
            $evalQuery->where('wing_id', $request->wing_id);
        }
        if ($request->filled('unit_id')) {
            $evalQuery->where('id', $request->unit_id);
        }

        $duesByUnit = collect();
        $totalDue   = 0;

        foreach ($evalQuery->get() as $unit) {

            $totalCharges  = (float) $unit->maintenanceBills()->sum('total_charges');
            $totalPayments = (float) PaymentReceipt::where('unit_id', $unit->id)
                ->where('status', 'cleared')
                ->sum('amount');

            $opening     = (float) ($unit->opening_balance ?? 0);
            $outstanding = round($opening + $totalCharges - $totalPayments, 2);
            $billQuery = $unit->maintenanceBills()->orderBy('due_date');

            if ($dateMode === 'single' && $date) {
                $billQuery->whereDate('due_date', $date);
            } elseif ($dateMode === 'range' && $from && $to) {
                $billQuery->whereBetween('due_date', [$from, $to]);
            }

            $bills = $billQuery->with('paymentAllocations.paymentReceipt')->get();

            if ($bills->isEmpty()) {
                continue;
            }

            // Virtually distribute receipts not yet matched to any bill (oldest-first).
            // Happens when payment was recorded before later bills were generated.
            $allocatedToBills   = (float) $bills->sum('total_paid');
            $initialUnallocated = round(max(0.0, $totalPayments - $allocatedToBills), 2);
            $unallocated        = $initialUnallocated;
            foreach ($bills as $bill) {
                $billDue = max(0.0, (float) $bill->closing_balance);
                $bill->virtual_compensation = 0.0;
                if ($unallocated > 0 && $billDue > 0) {
                    $bill->virtual_compensation = min($unallocated, $billDue);
                    $unallocated -= $bill->virtual_compensation;
                }
            }

            $hasPastDueBill = $bills->contains(fn ($b) => \Carbon\Carbon::parse($b->due_date)->lt(now()));
            // $hasPastDueBill = $bills->contains(fn ($b) => $b->status ==="overdue");

            /*
            | outstanding <= 0          => paid (credit or exact)
            | outstanding > 0 + overdue => overdue
            | outstanding > 0 only      => unpaid
            */
            if ($outstanding <= 0) {
                $actualStatus = 'paid';
            } elseif ($hasPastDueBill) {
                $actualStatus = 'overdue';
            } else {
                $actualStatus = 'unpaid';
            }
            // $hasPastDueBill = $bills->contains(fn ($b) => \Carbon\Carbon::parse($b->due_date)->lt(now()));
            // $hasPaid = $bills->contains(fn ($b) => $b->status ==="paid" );
            // $hasOverDue = $bills->contains(fn ($b) => $b->status ==="overdue" );
            // $hasUnpaid = $bills->contains(fn ($b) => $b->status ==="unpaid" );
            // if ($outstanding <= 0 && $hasPaid=='paid') {
            //     $actualStatus = 'paid';
            // } elseif ($hasOverDue=='overdue') {
            //     $actualStatus = 'overdue';
            // } else {
            //     $actualStatus = 'unpaid';
            // }
            if ($billStatus && $actualStatus !== $billStatus) {
                continue;
            }

            $duesByUnit->push([
                'unit'                => $unit,
                'bills'               => $bills,
                'opening_balance'     => round($opening, 2),
                'total_charges'       => round($totalCharges, 2),
                'total_paid'          => round($totalPayments, 2),
                'outstanding'         => $outstanding,
                'actual_status'       => $actualStatus,
                'credit_amount'       => $outstanding < 0 ? round(abs($outstanding), 2) : 0,
                'unallocated_payment' => $initialUnallocated,
            ]);

            $totalDue += $outstanding;
        }

        $totalUnits = $duesByUnit->count();
        $totalBills = $duesByUnit->sum(fn ($i) => $i['bills']->count());

        return view('masters.society-summary.index', compact(
            'wings', 'units', 'duesByUnit', 'totalDue',
            'totalUnits', 'totalBills',
            'dateMode', 'date', 'from', 'to', 'billStatus'
        ));
    }
}
