@extends('layouts.app')

@section('title', 'Society Summary — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Society Summary</li>
@endsection

@section('content')

{{-- Page heading --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-0">Society Summary</h4>
        <p class="text-muted small mb-0">Pending maintenance dues grouped by unit</p>
    </div>
</div>

{{-- Filters --}}
<div class="card shadow-sm mb-3">
    <div class="card-body py-3 px-4">
        <form method="GET" action="{{ route('society-summary.index') }}" class="row g-2 align-items-end" id="filterForm">

            {{-- Date mode toggle --}}
            <div class="col-auto">
                <label class="form-label fw-semibold small mb-1 d-block">Mode</label>
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="date_mode" id="mode_single" value="single"
                           {{ $dateMode === 'single' ? 'checked' : '' }} autocomplete="off">
                    <label class="btn btn-outline-primary" for="mode_single">
                        <i class="bi bi-calendar-day me-1"></i>Single
                    </label>
                    <input type="radio" class="btn-check" name="date_mode" id="mode_range" value="range"
                           {{ $dateMode === 'range' ? 'checked' : '' }} autocomplete="off">
                    <label class="btn btn-outline-primary" for="mode_range">
                        <i class="bi bi-calendar-range me-1"></i>Range
                    </label>
                </div>
            </div>

            {{-- Single date --}}
            <div id="single-date-wrap" class="col-sm-6 col-lg-2">
                <label class="form-label fw-semibold small mb-1">Due Date</label>
                <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm">
            </div>

            {{-- Date range --}}
            <div id="range-date-wrap" class="col-lg-4 d-none">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label fw-semibold small mb-1">From</label>
                        <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold small mb-1">To</label>
                        <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                    </div>
                </div>
            </div>

            {{-- Wing --}}
            <div class="col-sm-6 col-lg-2">
                <label class="form-label fw-semibold small mb-1">Wing</label>
                <select name="wing_id" id="wingSelect" class="form-select form-select-sm">
                    <option value="">All Wings</option>
                    @foreach ($wings as $wing)
                        <option value="{{ $wing->id }}" {{ request('wing_id') == $wing->id ? 'selected' : '' }}>
                            {{ $wing->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Unit --}}
            <div class="col-sm-6 col-lg-2">
                <label class="form-label fw-semibold small mb-1">Unit</label>
                <select name="unit_id" class="form-select form-select-sm">
                    <option value="">All Units</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->wing?->name ? $unit->wing->name . ' – ' : '' }}{{ $unit->unit_number }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i>Apply
                </button>
                <a href="{{ route('society-summary.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>

        </form>
    </div>
</div>

{{-- Status pills --}}
@php
    $pillBase = array_filter([
        'date_mode' => $dateMode,
        'date'      => $dateMode === 'single' ? $date : null,
        'from'      => $dateMode === 'range'  ? $from : null,
        'to'        => $dateMode === 'range'  ? $to   : null,
        'wing_id'   => request('wing_id'),
        'unit_id'   => request('unit_id'),
    ]);
@endphp
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <span class="text-muted small me-1">Status:</span>
    <a href="{{ route('society-summary.index', $pillBase) }}"
       class="btn btn-sm {{ is_null($billStatus) ? 'btn-secondary' : 'btn-outline-secondary' }}">
        All Dues
    </a>
    @foreach (['unpaid' => ['Unpaid', 'btn-danger'], 'overdue' => ['Overdue', 'btn-dark'], 'paid' => ['Paid', 'btn-success']] as $val => [$label, $activeClass])
        <a href="{{ route('society-summary.index', array_merge($pillBase, ['status' => $val])) }}"
           class="btn btn-sm {{ $billStatus === $val ? $activeClass : 'btn-outline-secondary' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Summary stat cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10"
                     style="width:48px;height:48px;flex-shrink:0">
                    <i class="bi bi-house text-primary fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Units with Dues</div>
                    <div class="fw-bold fs-4 lh-1">{{ $totalUnits }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning bg-opacity-10"
                     style="width:48px;height:48px;flex-shrink:0">
                    <i class="bi bi-receipt text-warning fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Pending Bills</div>
                    <div class="fw-bold fs-4 lh-1">{{ $totalBills }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10"
                     style="width:48px;height:48px;flex-shrink:0">
                    <i class="bi bi-currency-rupee text-danger fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Outstanding</div>
                    <div class="fw-bold fs-4 lh-1 text-danger">₹{{ number_format($totalDue, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Dues accordion --}}
<div class="card shadow-sm mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-house-door me-2"></i>By Unit</span>
        <span class="text-muted small">
            @if ($dateMode === 'single' && $date)
                {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
            @elseif ($dateMode === 'range' && $from && $to)
                {{ \Carbon\Carbon::parse($from)->format('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            @else
                All dates
            @endif
        </span>
    </div>

    @if ($duesByUnit->isEmpty())
        <div class="card-body text-center text-muted py-5 small">
            <i class="bi bi-check2-circle d-block mb-2" style="font-size:1.5rem;color:#16a34a"></i>
            No outstanding dues found for the selected filters
        </div>
    @else

        <div class="accordion accordion-flush" id="duesAccordion">
            @foreach ($duesByUnit as $item)
                @php
                    $unit                = $item['unit'];
                    $bills               = $item['bills'];
                    $total_paid          = $item['total_paid'];
                    $total_charges       = $item['total_charges'];
                    $opening_balance     = $item['opening_balance'];
                    $outstanding         = $item['outstanding'];
                    $actualStatus        = $item['actual_status'] ?? 'unpaid';
                    $isPaid              = $actualStatus === 'paid';
                    $isCredit            = $outstanding < 0;
                    $creditAmount        = $item['credit_amount'] ?? 0;
                    $unallocatedPayment  = $item['unallocated_payment'] ?? 0;
                    // Overdue = past-due bill with remaining balance even after virtual compensation
                    $overdueCount        = $isPaid ? 0 : $bills->filter(function ($b) {
                        $bc = (float) $b->closing_balance;
                        $vc = (float) ($b->virtual_compensation ?? 0);
                        return $b->due_date->isPast() && ($bc - $vc) > 0;
                    })->count();
                @endphp

                <div class="accordion-item border-0 border-bottom">

                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} py-3 px-4"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#du{{ $loop->index }}"
                                aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                            <div class="d-flex align-items-center w-100 me-2 gap-2">

                                {{-- Unit icon --}}
                                <div class="rounded-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:32px;height:32px">
                                    <i class="bi bi-house" style="font-size:14px"></i>
                                </div>

                                {{-- Unit label --}}
                                <div>
                                    <div class="fw-semibold" style="font-size:14px">
                                        {{ $unit->wing?->name ? $unit->wing->name . ' – ' : '' }}Unit {{ $unit->unit_number }}
                                    </div>
                                    @if ($unit->registered_in_name_of)
                                        <div class="text-muted" style="font-size:11px">{{ $unit->registered_in_name_of }}</div>
                                    @endif
                                </div>

                                {{-- Badges + amount --}}
                                <div class="ms-auto d-flex align-items-center gap-3">
                                    @if ($isPaid)
                                        <span class="badge rounded-pill px-2"
                                              style="background:#16a34a20;color:#16a34a;border:1px solid #16a34a40;font-size:11px">
                                            <i class="bi bi-check2-circle me-1"></i>
                                            {{ $isCredit ? 'Credit ' . number_format(abs($outstanding), 2) : 'Paid' }}
                                        </span>
                                    @else
                                        @if ($overdueCount > 0)
                                            <span class="badge rounded-pill px-2"
                                                  style="background:#dc262620;color:#dc2626;border:1px solid #dc262640;font-size:11px">
                                                {{ $overdueCount }} overdue
                                            </span>
                                        @endif
                                        <span class="fw-semibold text-danger d-none d-md-inline" style="font-size:13px">
                                            ₹{{ number_format($outstanding, 2) }}
                                        </span>
                                    @endif
                                    <span class="badge rounded-pill px-2"
                                          style="background:#d9770620;color:#d97706;border:1px solid #d9770640;font-size:11px">
                                        {{ $bills->count() }} bill{{ $bills->count() !== 1 ? 's' : '' }}
                                    </span>
                                </div>

                            </div>
                        </button>
                    </h2>

                    <div id="du{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}">
                        <div class="accordion-body p-0">

                            {{-- Unit-level credit banner (total paid > total due) --}}
                            @if ($isCredit && $creditAmount > 0)
                            <div class="d-flex align-items-center gap-2 px-4 py-2 border-bottom"
                                 style="background:#f0fdf4;font-size:12px;color:#16a34a;">
                                <i class="bi bi-check-circle fs-6"></i>
                                <span>
                                    <strong>Credit Balance: ₹{{ number_format($creditAmount, 2) }}</strong>
                                    — Overpaid. This credit carries forward to the next billing cycle.
                                </span>
                            </div>
                            @endif
                            {{-- Compensation banner (receipts exist but not yet matched to bills) --}}
                            @if (!$isCredit && $unallocatedPayment > 0)
                            <div class="d-flex align-items-center gap-2 px-4 py-2 border-bottom"
                                 style="background:#eff6ff;font-size:12px;color:#1d4ed8;">
                                <i class="bi bi-arrow-left-right fs-6"></i>
                                <span>
                                    <strong>₹{{ number_format($unallocatedPayment, 2) }} pending compensation</strong>
                                    — Payment received but not yet allocated to bills. Applied to oldest unpaid bills below.
                                </span>
                            </div>
                            @endif

                            <table class="table table-sm align-middle mb-0" style="font-size:12px">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-2" style="width:36px"></th>
                                        <th class="py-2">Bill No.</th>
                                        <th class="py-2">Bill Date</th>
                                        <th class="py-2">Due Date</th>
                                        <th class="py-2 text-end">Opening Balance</th>
                                        <th class="py-2 text-end">Charges</th>
                                        <th class="py-2 text-end">Paid</th>
                                        <th class="py-2 text-center">Status</th>
                                        <th class="py-2 text-center">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bills as $bill)
                                        @php
                                            // Previous bill credit carried into this bill's opening balance
                                            $billCreditCarried   = (float) $bill->opening_balance < 0;

                                            // Actual DB closing balance: negative = this bill itself is overpaid → credit to next
                                            $billClosing         = (float) $bill->closing_balance;
                                            $billActualCredit    = $billClosing < 0;
                                            $billActualCreditAmt = $billActualCredit ? round(abs($billClosing), 2) : 0;

                                            // Virtual compensation from unallocated receipts
                                            $billVirtComp        = (float) ($bill->virtual_compensation ?? 0);
                                            $billVirtDue         = round(max(0.0, $billClosing) - $billVirtComp, 2);
                                            $billCompensated     = $billVirtComp > 0 && $billVirtDue <= 0;

                                            $effectivePaid       = $isPaid || $bill->status === 'paid' || $billActualCredit || $billCompensated;
                                            $isOverdueRow        = !$effectivePaid && in_array($bill->status, ['overdue', 'unpaid', 'partial']) && $bill->due_date->isPast();

                                            // Collect notes from all payment receipts allocated to this bill
                                            $billNotes = $bill->paymentAllocations
                                                ->map(fn($a) => $a->paymentReceipt)
                                                ->filter()
                                                ->filter(fn($r) => filled($r->notes))
                                                ->map(fn($r) => trim($r->notes))
                                                ->unique()
                                                ->values();
                                        @endphp
                                        <tr class="{{ $isOverdueRow ? 'table-danger' : '' }}">
                                            <td class="px-4 py-2 text-center">
                                                @if ($billCompensated || $billActualCredit)
                                                    <i class="bi bi-check-circle-fill" style="color:#0891b2"></i>
                                                @elseif ($effectivePaid)
                                                    <i class="bi bi-check-circle-fill text-success"></i>
                                                @elseif ($isOverdueRow)
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                @else
                                                    <i class="bi bi-clock text-warning"></i>
                                                @endif
                                            </td>
                                            <td class="py-2 fw-semibold text-nowrap">{{ $bill->bill_no }}</td>
                                            <td class="py-2 text-muted">{{ $bill->bill_date->format('d M Y') }}</td>
                                            <td class="py-2 {{ $isOverdueRow ? 'text-danger fw-semibold' : 'text-muted' }}">
                                                {{ $bill->due_date->format('d M Y') }}
                                            </td>
                                            <td class="py-2 text-end">
                                                @if ($billCreditCarried)
                                                    <span style="color:#0891b2;font-weight:600">
                                                        ₹{{ number_format(abs($bill->opening_balance), 2) }}
                                                    </span>
                                                    <div style="font-size:10px;color:#0891b2;">
                                                        <i class="bi bi-arrow-left-right me-1"></i>Credit carried
                                                    </div>
                                                @else
                                                    ₹{{ number_format($bill->opening_balance, 2) }}
                                                @endif
                                            </td>
                                            <td class="py-2 text-end">₹{{ number_format($bill->total_charges, 2) }}</td>
                                            <td class="py-2 text-end">
                                                <span class="text-success">₹{{ number_format($bill->total_paid, 2) }}</span>
                                                @if ($billVirtComp > 0)
                                                    <div style="font-size:10px;color:#0891b2;">
                                                        <i class="bi bi-arrow-left-right me-1"></i>+₹{{ number_format($billVirtComp, 2) }} compensated
                                                    </div>
                                                @endif
                                                @if ($billActualCredit)
                                                    <div style="font-size:10px;color:#0891b2;">
                                                        <i class="bi bi-arrow-right me-1"></i>₹{{ number_format($billActualCreditAmt, 2) }} credit → next bill
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="py-2 text-center">
                                                @if ($billCompensated)
                                                    {{-- Unallocated receipt covers this bill --}}
                                                    <span class="badge rounded-pill px-2"
                                                          style="background:#0891b220;color:#0891b2;border:1px solid #0891b240;font-size:11px">
                                                        <i class="bi bi-arrow-left-right me-1"></i>Compensated
                                                    </span>
                                                @elseif ($billActualCredit)
                                                    {{-- Bill's own total_paid exceeds its full balance; excess → next bill --}}
                                                    <span class="badge rounded-pill px-2"
                                                          style="background:#0891b220;color:#0891b2;border:1px solid #0891b240;font-size:11px">
                                                        <i class="bi bi-arrow-left-right me-1"></i>Credit
                                                    </span>
                                                @elseif ($effectivePaid)
                                                    <span class="badge bg-success rounded-pill px-2">Paid</span>
                                                @elseif ($bill->status === 'partial')
                                                    <span class="badge bg-warning text-dark rounded-pill px-2">Partial</span>
                                                @elseif ($isOverdueRow)
                                                    <span class="badge bg-dark rounded-pill px-2">Overdue</span>
                                                @else
                                                    <span class="badge bg-danger rounded-pill px-2">Unpaid</span>
                                                @endif
                                            </td>
                                            <td class="py-2" style="max-width:200px;font-size:11px">
                                                @forelse ($billNotes as $note)
                                                    <div class="text-muted text-truncate" title="{{ $note }}">{{ $note }}</div>
                                                @empty
                                                    <span class="text-muted">—</span>
                                                @endforelse
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 text-end text-muted small">
                                            True outstanding (opening balance + all charges − all receipts)
                                        </td>
                                        <td class="py-2 text-center fw-bold text-danger">
                                            Opening Balance ₹{{ number_format($opening_balance, 2) }}
                                        </td>
                                        <td class="py-2 text-center fw-bold text-danger">
                                            Total Changes ₹{{ number_format($total_charges, 2) }}
                                        </td>
                                        <td class="py-2 text-center fw-bold text-danger">
                                            Total Paid ₹{{ number_format($total_paid, 2) }}
                                        </td>
                                        <td class="py-2 text-center fw-bold {{ $isCredit ? 'text-success' : 'text-danger' }}">
                                            {{ $isCredit ? 'Credit' : 'Outstanding' }}
                                            ₹{{ number_format(abs($outstanding), 2) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>{{-- /.accordion-item --}}
            @endforeach
        </div>{{-- /.accordion --}}

        <div class="card-footer bg-white text-muted small py-2 px-4">
            {{ $totalUnits }} unit{{ $totalUnits !== 1 ? 's' : '' }}
            &middot;
            {{ $totalBills }} bill{{ $totalBills !== 1 ? 's' : '' }}
            &middot;
            ₹{{ number_format($totalDue, 2) }} outstanding
        </div>

    @endif
</div>{{-- /.card --}}

@endsection

@push('scripts')
<script>
(function () {
    var singleWrap = document.getElementById('single-date-wrap');
    var rangeWrap  = document.getElementById('range-date-wrap');

    function applyMode() {
        var isSingle = document.getElementById('mode_single').checked;
        singleWrap.classList.toggle('d-none', !isSingle);
        rangeWrap.classList.toggle('d-none',  isSingle);
    }

    document.querySelectorAll('input[name="date_mode"]').forEach(function (r) {
        r.addEventListener('change', applyMode);
    });

    applyMode();

    document.getElementById('wingSelect').addEventListener('change', function () {
        document.querySelector('[name="unit_id"]').value = '';
        document.getElementById('filterForm').submit();
    });
})();
</script>
@endpush
