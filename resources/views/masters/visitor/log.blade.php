@extends('layouts.app')

@section('title', 'Visitor Log — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item">Visitors</li>
    <li class="breadcrumb-item active" aria-current="page">Visitor Log</li>
@endsection

@section('content')

{{-- Page heading --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-0">Visitor Log</h4>
        <p class="text-muted small mb-0">Visitor entries by date range</p>
    </div>
</div>

{{-- Filters --}}
<div class="card shadow-sm mb-3">
    <div class="card-body py-3 px-4">
        <form method="GET" action="{{ route('visitor.log') }}" class="row g-2 align-items-end">

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
                <label class="form-label fw-semibold small mb-1">Date</label>
                <input type="date" name="date" value="{{ $date ?? $from }}" class="form-control form-control-sm">
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
                    @foreach ($wingsList as $wing)
                        <option value="{{ $wing->id }}" {{ (string) $wingId === (string) $wing->id ? 'selected' : '' }}>
                            {{ $wing->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Unit --}}
            <div class="col-sm-6 col-lg-2">
                <label class="form-label fw-semibold small mb-1">Unit</label>
                <select name="unit_id" id="unitSelect" class="form-select form-select-sm">
                    <option value="">All Units</option>
                    @foreach ($unitsList as $unit)
                        <option value="{{ $unit->id }}"
                                data-wing="{{ $unit->wing_id }}"
                                {{ (string) $unitId === (string) $unit->id ? 'selected' : '' }}>
                            {{ $unit->unit_number }}{{ $unit->registered_in_name_of ? ' — ' . $unit->registered_in_name_of : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Watchman --}}
            <div class="col-sm-6 col-lg-2">
                <label class="form-label fw-semibold small mb-1">Watchman</label>
                <select name="watchman_id" class="form-select form-select-sm">
                    <option value="">All Watchmen</option>
                    @foreach ($watchmenList as $w)
                        <option value="{{ $w->id }}" {{ (string) $watchmanId === (string) $w->id ? 'selected' : '' }}>
                            {{ $w->name }}{{ $w->employee_id ? ' (' . $w->employee_id . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i>Apply
                </button>
                <a href="{{ route('visitor.log') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>

        </form>
    </div>
</div>

{{-- Summary stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-sm-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-secondary bg-opacity-10"
                     style="width:48px;height:48px;flex-shrink:0">
                    <i class="bi bi-people text-secondary fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total</div>
                    <div class="fw-bold fs-4 lh-1">{{ $total }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10"
                     style="width:48px;height:48px;flex-shrink:0">
                    <i class="bi bi-box-arrow-in-right text-success fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Inside</div>
                    <div class="fw-bold fs-4 lh-1 text-success">{{ $in }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10"
                     style="width:48px;height:48px;flex-shrink:0">
                    <i class="bi bi-box-arrow-right text-primary fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Checked Out</div>
                    <div class="fw-bold fs-4 lh-1 text-primary">{{ $out }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning bg-opacity-10"
                     style="width:48px;height:48px;flex-shrink:0">
                    <i class="bi bi-hourglass-split text-warning fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Pending</div>
                    <div class="fw-bold fs-4 lh-1 text-warning">{{ $pending }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Date accordion --}}
<div class="card shadow-sm mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-calendar3 me-2"></i>By Date</span>
        <span class="text-muted small">
            {{ \Carbon\Carbon::parse($from)->format('d M Y') }}
            @if ($from !== $to)
                &ndash; {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            @endif
        </span>
    </div>

    @if ($dateReport->isEmpty())
        <div class="card-body text-center text-muted py-5 small">
            <i class="bi bi-moon d-block mb-2" style="font-size:1.5rem"></i>
            No visitor records found for this period
        </div>
    @else

        <div class="accordion accordion-flush" id="visitorLogAccordion">
            @foreach ($dateReport as $idx => $dayItem)
                @php
                    $parsedDay = \Carbon\Carbon::parse($dayItem->date);
                    $pct       = $dayItem->total > 0 ? round(($dayItem->out / $dayItem->total) * 100) : 0;
                    $allOut    = $dayItem->in === 0 && $dayItem->total > 0;
                    $noneOut   = $dayItem->out === 0;
                    $barColor  = $allOut ? '#16a34a' : ($noneOut ? '#dc2626' : '#d97706');
                @endphp

                <div class="accordion-item border-0 border-bottom">

                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $idx === 0 ? '' : 'collapsed' }} py-3 px-4"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#vd{{ $idx }}"
                                aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}">
                            <div class="d-flex align-items-center w-100 me-2 gap-2">

                                <div class="rounded-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:32px;height:32px">
                                    <i class="bi bi-calendar3" style="font-size:14px"></i>
                                </div>

                                <div>
                                    <div class="fw-semibold" style="font-size:14px">
                                        {{ $parsedDay->format('D, d M Y') }}
                                    </div>
                                    <div class="text-muted" style="font-size:11px">
                                        {{ $dayItem->total }} visitor{{ $dayItem->total !== 1 ? 's' : '' }}
                                    </div>
                                </div>

                                <div class="ms-auto d-flex align-items-center gap-3">
                                    <span class="badge rounded-pill px-2"
                                          style="background:{{ $barColor }}20;color:{{ $barColor }};border:1px solid {{ $barColor }}40;font-size:11px">
                                        {{ $dayItem->out }} out / {{ $dayItem->in }} inside
                                    </span>
                                    <div style="width:64px;height:6px;border-radius:3px;background:#e5e7eb;overflow:hidden">
                                        <div style="width:{{ $pct }}%;height:100%;border-radius:3px;background:{{ $barColor }}"></div>
                                    </div>
                                </div>

                            </div>
                        </button>
                    </h2>

                    <div id="vd{{ $idx }}" class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}">
                        <div class="accordion-body p-0">
                            <table class="table table-sm align-middle mb-0" style="font-size:12px">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-2" style="width:36px"></th>
                                        <th class="py-2">Visitor</th>
                                        <th class="py-2">Wing</th>
                                        <th class="py-2">Unit</th>
                                        <th class="py-2">Reason</th>
                                        <th class="py-2">Watchman</th>
                                        <th class="py-2 text-nowrap">In At</th>
                                        <th class="py-2 text-nowrap">Out At</th>
                                        <th class="py-2">Status</th>
                                        <th class="py-2">Photo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dayItem->visitors as $visitor)
                                        @php
                                            $isOut = $visitor->out_at !== null;
                                            $statusColor = match($visitor->status) {
                                                'allowed'     => 'success',
                                                'not_allowed' => 'danger',
                                                default       => 'warning',
                                            };
                                        @endphp
                                        <tr class="{{ !$isOut ? '' : '' }}">
                                            <td class="px-4 py-2 text-center">
                                                @if ($isOut)
                                                    <i class="bi bi-box-arrow-right text-primary"></i>
                                                @else
                                                    <i class="bi bi-person-fill-check text-success"></i>
                                                @endif
                                            </td>
                                            <td class="py-2">
                                                <div class="fw-semibold">{{ $visitor->visitor_name }}</div>
                                                @if ($visitor->mobile)
                                                    <div class="text-muted" style="font-size:11px">{{ $visitor->mobile }}</div>
                                                @endif
                                                @if ($visitor->vehicle_number)
                                                    <div class="text-muted" style="font-size:11px">
                                                        <i class="bi bi-truck me-1"></i>{{ $visitor->vehicle_number }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="py-2 text-muted">
                                                {{ $visitor->unit?->wing?->name ?? '—' }}
                                            </td>
                                            <td class="py-2">
                                                <div class="fw-semibold" style="font-size:12px">{{ $visitor->unit?->unit_number ?? '—' }}</div>
                                                @if ($visitor->unit?->registered_in_name_of)
                                                    <div class="text-muted" style="font-size:11px">{{ $visitor->unit->registered_in_name_of }}</div>
                                                @endif
                                            </td>
                                            <td class="py-2 text-muted">
                                                {{ $visitor->reason ?: '—' }}
                                            </td>
                                            <td class="py-2 text-muted">
                                                {{ $visitor->watchman?->name ?? '—' }}
                                            </td>
                                            <td class="py-2 text-muted text-nowrap">
                                                {{ $visitor->created_at?->format('h:i A') ?? '—' }}
                                            </td>
                                            <td class="py-2 text-muted text-nowrap">
                                                {{ $visitor->out_at?->format('h:i A') ?? '—' }}
                                            </td>
                                            <td class="py-2">
                                                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} border border-{{ $statusColor }}-subtle px-2" style="font-size:10px">
                                                    {{ ucfirst(str_replace('_', ' ', $visitor->status)) }}
                                                </span>
                                            </td>
                                            <td class="py-2 text-center">
                                                @if ($visitor->photo)
                                                    <img src="{{ Storage::url($visitor->photo) }}"
                                                         alt="visitor"
                                                         class="rounded border object-fit-cover visitor-photo-thumb"
                                                         style="width:36px;height:36px;cursor:pointer"
                                                         data-src="{{ Storage::url($visitor->photo) }}"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#photoModal">
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="card-footer bg-white text-muted small py-2 px-4">
            {{ $dateReport->count() }} day{{ $dateReport->count() !== 1 ? 's' : '' }}
            &middot;
            {{ $total }} visitor{{ $total !== 1 ? 's' : '' }} in range
        </div>

    @endif
</div>

{{-- Photo popup modal --}}
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2 px-3 border-0">
                <span class="fw-semibold small">Visitor Photo</span>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <img id="photoModalImg" src="" alt="visitor" class="img-fluid w-100 rounded-bottom">
            </div>
        </div>
    </div>
</div>

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
})();

document.addEventListener('click', function (e) {
    var thumb = e.target.closest('.visitor-photo-thumb');
    if (thumb) {
        document.getElementById('photoModalImg').src = thumb.dataset.src;
    }
});

// Wing → Unit cascade
(function () {
    var wingSelect = document.getElementById('wingSelect');
    var unitSelect = document.getElementById('unitSelect');
    var allUnitOptions = Array.from(unitSelect.querySelectorAll('option[data-wing]'));

    function filterUnits() {
        var wingId = wingSelect.value;
        allUnitOptions.forEach(function (opt) {
            var show = !wingId || opt.dataset.wing === wingId;
            opt.hidden = !show;
            if (!show && opt.selected) {
                opt.selected = false;
                unitSelect.value = '';
            }
        });
    }

    wingSelect.addEventListener('change', filterUnits);
    filterUnits(); // apply on load in case wing is pre-selected
})();
</script>
@endpush
