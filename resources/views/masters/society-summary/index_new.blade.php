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

        <div class="accordion" id="duesAccordion">

@foreach ($duesByUnit as $item)

@php
    $unit        = $item['unit'];
    $bills       = $item['bills'];
    $status      = $item['actual_status'];
    $outstanding = $item['outstanding'];
@endphp

<div class="accordion-item mb-2 border rounded">

    {{-- HEADER --}}
    <h2 class="accordion-header" id="heading{{ $loop->index }}">

        <button class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapse{{ $loop->index }}">

            <div class="d-flex justify-content-between w-100 align-items-center">

                <div>
                    <strong>
                        {{ $unit->wing?->name }} - Unit {{ $unit->unit_number }}
                    </strong>
                </div>

                <div class="d-flex gap-2 align-items-center">

                    {{-- STATUS BADGE (SAFE CSS) --}}
                    <span class="badge
                        @if($status=='paid') bg-success
                        @elseif($status=='overdue') bg-dark
                        @else bg-danger
                        @endif">

                        {{ strtoupper($status) }}

                    </span>

                    {{-- OUTSTANDING --}}
                    @if($outstanding > 0)
                        <span class="text-danger fw-bold">
                            ₹{{ number_format($outstanding,2) }}
                        </span>
                    @endif

                    {{-- COUNT --}}
                    <span class="badge bg-warning text-dark">
                        {{ $bills->count() }} Bills
                    </span>

                </div>

            </div>

        </button>
    </h2>

    {{-- BODY --}}
    <div id="collapse{{ $loop->index }}"
         class="accordion-collapse collapse"
         data-bs-parent="#duesAccordion">

        <div class="accordion-body p-0">

            <div class="table-responsive">

                <table class="table table-sm table-bordered mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Bill No</th>
                            <th>Due Date</th>
                            <th class="text-end">Charges</th>
                            <th class="text-end">Paid</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    @foreach ($bills as $bill)

                        @php
                            $isPaid = $status === 'paid';
                            $isOver = $status === 'overdue';
                        @endphp

                        <tr
                            @if($isOver) class="table-danger" @endif
                        >

                            <td>{{ $bill->bill_no }}</td>

                            <td>{{ $bill->due_date->format('d M Y') }}</td>

                            <td class="text-end">
                                ₹{{ number_format($bill->total_charges,2) }}
                            </td>

                            <td class="text-end text-success">
                                ₹{{ number_format($bill->total_paid,2) }}
                            </td>

                            <td>

                                <span class="badge
                                    @if($isPaid) bg-success
                                    @elseif($isOver) bg-dark
                                    @else bg-danger
                                    @endif">

                                    {{ strtoupper($status) }}

                                </span>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2">Total</th>

                            <th class="text-end">
                                ₹{{ number_format($item['total_charges'],2) }}
                            </th>

                            <th class="text-end">
                                ₹{{ number_format($item['total_paid'],2) }}
                            </th>

                            <th class="text-end text-danger">
                                ₹{{ number_format($outstanding,2) }}
                            </th>
                        </tr>
                    </tfoot>

                </table>

            </div>

        </div>

    </div>

</div>

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
