@extends('layouts.app')

@section('title', 'Maid Entry Log — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item">Maids</li>
    <li class="breadcrumb-item active" aria-current="page">Entry Log</li>
@endsection

@section('content')

{{-- Page heading --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-0">Maid Entry Log</h4>
        <p class="text-muted small mb-0">Maid entry &amp; exit records by date range</p>
    </div>
    <a href="{{ route('maid.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-people me-1"></i>All Maids
    </a>
</div>

{{-- Filters --}}
<div class="card shadow-sm mb-3">
    <div class="card-body py-3 px-4">
        <form method="GET" action="{{ route('maid.log') }}" class="row g-2 align-items-end">

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

            {{-- Maid --}}
            <div class="col-sm-6 col-lg-2">
                <label class="form-label fw-semibold small mb-1">Maid</label>
                <select name="maid_id" class="form-select form-select-sm">
                    <option value="">All Maids</option>
                    @foreach ($maidsList as $maid)
                        <option value="{{ $maid->id }}" {{ (string) $maidId === (string) $maid->id ? 'selected' : '' }}>
                            {{ $maid->name }}{{ $maid->mobile ? ' (' . $maid->mobile . ')' : '' }}
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
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i>Apply
                </button>
                <a href="{{ route('maid.log') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>

        </form>
    </div>
</div>

{{-- Summary stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-sm-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-secondary bg-opacity-10"
                     style="width:48px;height:48px;flex-shrink:0">
                    <i class="bi bi-people text-secondary fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Entries</div>
                    <div class="fw-bold fs-4 lh-1">{{ $total }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10"
                     style="width:48px;height:48px;flex-shrink:0">
                    <i class="bi bi-box-arrow-in-right text-success fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Inside</div>
                    <div class="fw-bold fs-4 lh-1 text-success">{{ $inside }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10"
                     style="width:48px;height:48px;flex-shrink:0">
                    <i class="bi bi-box-arrow-right text-primary fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Checked Out</div>
                    <div class="fw-bold fs-4 lh-1 text-primary">{{ $exited }}</div>
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
            No maid entry records found for this period
        </div>
    @else

        <div class="accordion accordion-flush" id="maidLogAccordion">
            @foreach ($dateReport as $idx => $dayItem)
                @php
                    $parsedDay = \Carbon\Carbon::parse($dayItem->date);
                    $pct       = $dayItem->total > 0 ? round(($dayItem->exited / $dayItem->total) * 100) : 0;
                    $allOut    = $dayItem->inside === 0 && $dayItem->total > 0;
                    $noneOut   = $dayItem->exited === 0;
                    $barColor  = $allOut ? '#16a34a' : ($noneOut ? '#dc2626' : '#d97706');
                @endphp

                <div class="accordion-item border-0 border-bottom">

                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $idx === 0 ? '' : 'collapsed' }} py-3 px-4"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#md{{ $idx }}"
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
                                        {{ $dayItem->total }} entr{{ $dayItem->total !== 1 ? 'ies' : 'y' }}
                                    </div>
                                </div>

                                <div class="ms-auto d-flex align-items-center gap-3">
                                    <span class="badge rounded-pill px-2"
                                          style="background:{{ $barColor }}20;color:{{ $barColor }};border:1px solid {{ $barColor }}40;font-size:11px">
                                        {{ $dayItem->exited }} out / {{ $dayItem->inside }} inside
                                    </span>
                                    <div style="width:64px;height:6px;border-radius:3px;background:#e5e7eb;overflow:hidden">
                                        <div style="width:{{ $pct }}%;height:100%;border-radius:3px;background:{{ $barColor }}"></div>
                                    </div>
                                </div>

                            </div>
                        </button>
                    </h2>

                    <div id="md{{ $idx }}" class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}">
                        <div class="accordion-body p-0">
                            <table class="table table-sm align-middle mb-0" style="font-size:12px">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-2" style="width:36px"></th>
                                        <th class="py-2">Maid</th>
                                        <th class="py-2">Watchman</th>
                                        <th class="py-2 text-nowrap">Entry Time</th>
                                        <th class="py-2 text-nowrap">Exit Time</th>
                                        <th class="py-2 text-nowrap">Duration</th>
                                        <th class="py-2 text-nowrap">No Of Visits</th>
                                        <th class="py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $maidVisitNums = [];
                                        $totalDuration =0;
                                    @endphp
                                    @foreach ($dayItem->logs->sortBy('enter_time') as $log)
                                        @php
                                            $isInside = $log->status === 'enter';
                                            $duration = null;
                                            if ($log->exit_time) {
                                                $mins = round($log->enter_time->diffInMinutes($log->exit_time));
                                                $totalDuration += $mins;
                                                $duration = $mins >= 60
                                                    ? floor($mins / 60) . 'h ' . ($mins % 60) . 'm'
                                                    : $mins . 'm';
                                            }
                                            $maidVisitNums[$log->maid_id] = ($maidVisitNums[$log->maid_id] ?? 0) + 1;
                                            $n       = $maidVisitNums[$log->maid_id];
                                            $suffix  = match(true) {
                                                $n % 100 >= 11 && $n % 100 <= 13 => 'th',
                                                $n % 10 === 1 => 'st',
                                                $n % 10 === 2 => 'nd',
                                                $n % 10 === 3 => 'rd',
                                                default       => 'th',
                                            };
                                            $visitLabel = $n . $suffix . ' visit';
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-2 text-center">
                                                @if ($isInside)
                                                    <i class="bi bi-person-fill-check text-success"></i>
                                                @else
                                                    <i class="bi bi-box-arrow-right text-primary"></i>
                                                @endif
                                            </td>
                                            <td class="py-2">
                                                <div class="fw-semibold">{{ $log->maid?->name ?? '—' }}</div>
                                                @if ($log->maid?->mobile)
                                                    <div class="text-muted" style="font-size:11px">{{ $log->maid->mobile }}</div>
                                                @endif
                                            </td>
                                            <td class="py-2 text-muted">
                                                {{ $log->watchman?->name ?? '—' }}
                                            </td>
                                            <td class="py-2 text-muted text-nowrap">
                                                {{ $log->enter_time?->format('h:i A') ?? '—' }}
                                            </td>
                                            <td class="py-2 text-muted text-nowrap">
                                                {{ $log->exit_time?->format('h:i A') ?? '—' }}
                                            </td>
                                            <td class="py-2 text-muted text-nowrap">
                                                {{ $duration ?? '—' }}
                                            </td>
                                            <td class="py-2 text-nowrap">
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2" style="font-size:10px">
                                                    {{ $visitLabel }}
                                                </span>
                                            </td>
                                            <td class="py-2">
                                                @if ($isInside)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2" style="font-size:10px">
                                                        Inside
                                                    </span>
                                                @else
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2" style="font-size:10px">
                                                        Exited
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="5" class="px-4 py-2 text-end text-muted small">
                                            Total Time {{$totalDuration >= 60
                                                    ? floor(abs($totalDuration) / 60) . 'h ' . (abs($totalDuration) % 60) . 'm'
                                                    : abs($totalDuration) . 'm';}}
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="card-footer bg-white text-muted small py-2 px-4">
            {{ $dateReport->count() }} day{{ $dateReport->count() !== 1 ? 's' : '' }}
            &middot;
            {{ $total }} entr{{ $total !== 1 ? 'ies' : 'y' }} in range
        </div>

    @endif
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
</script>
@endpush
