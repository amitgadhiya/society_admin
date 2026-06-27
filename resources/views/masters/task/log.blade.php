@extends('layouts.app')

@section('title', 'Task Log — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('task.index') }}">Tasks</a></li>
    <li class="breadcrumb-item active" aria-current="page">Daily Log</li>
@endsection

@push('styles')
<style>
    .completion-bar { height: 6px; border-radius: 3px; background: #e5e7eb; overflow: hidden; }
    .completion-bar-fill { height: 100%; border-radius: 3px; }
</style>
@endpush

@section('content')

@php
    $recurrenceLabels = ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'biannual' => 'Biannual', 'annual' => 'Annual'];
    $weekDayNames     = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
@endphp

{{-- Page heading --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-0">Task Log</h4>
        <p class="text-muted small mb-0">Watchman task completion status by date range</p>
    </div>
    <a href="{{ route('task.analysis') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-funnel me-1"></i>Analysis
    </a>
</div>

{{-- Filters --}}
<div class="card shadow-sm mb-3">
    <div class="card-body py-3 px-4">
        <form method="GET" action="{{ route('task.log') }}" class="row g-2 align-items-end" id="logFilterForm">

            @if ($scheduleType)
                <input type="hidden" name="schedule_type" value="{{ $scheduleType }}">
            @endif

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

            {{-- Watchman --}}
            <div class="col-sm-6 col-lg-3">
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
                <a href="{{ route('task.log') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>

        </form>
    </div>
</div>

{{-- Recurrence type pills --}}
@php
    $pillBase = array_filter([
        'date_mode'   => $dateMode,
        'date'        => $dateMode === 'single' ? $date        : null,
        'from'        => $dateMode === 'range'  ? $from        : null,
        'to'          => $dateMode === 'range'  ? $to          : null,
        'watchman_id' => $watchmanId,
    ]);
@endphp
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <span class="text-muted small me-1">Schedule:</span>
    <a href="{{ route('task.log', $pillBase) }}"
       class="btn btn-sm {{ is_null($scheduleType) ? 'btn-secondary' : 'btn-outline-secondary' }}">
        All
    </a>
    @foreach ($recurrenceLabels as $val => $label)
        <a href="{{ route('task.log', array_merge($pillBase, ['schedule_type' => $val])) }}"
           class="btn btn-sm {{ $scheduleType === $val ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ $label }}
            @if (($tasksByScheduleType[$val] ?? 0) > 0)
                <span class="badge rounded-pill ms-1 {{ $scheduleType === $val ? 'bg-white text-primary' : 'bg-secondary' }}">
                    {{ $tasksByScheduleType[$val] }}
                </span>
            @endif
        </a>
    @endforeach
</div>

{{-- Summary stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-secondary bg-opacity-10"
                     style="width:48px;height:48px;flex-shrink:0">
                    <i class="bi bi-clipboard-check text-secondary fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Assigned</div>
                    <div class="fw-bold fs-4 lh-1">{{ $totalAssigned }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10"
                     style="width:48px;height:48px;flex-shrink:0">
                    <i class="bi bi-check2-circle text-success fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Tasks With Completions</div>
                    <div class="fw-bold fs-4 lh-1 text-success">{{ $totalCompleted }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10"
                     style="width:48px;height:48px;flex-shrink:0">
                    <i class="bi bi-x-circle text-danger fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">No Completions</div>
                    <div class="fw-bold fs-4 lh-1 text-danger">{{ $totalIncomplete }}</div>
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

    @if ($totalAssigned === 0)
        <div class="card-body text-center text-muted py-5 small">
            <i class="bi bi-moon d-block mb-2" style="font-size:1.5rem"></i>
            No active task assignments found
        </div>
    @else

        <div class="accordion accordion-flush" id="logAccordion">
            @foreach ($dateReport as $idx => $dayItem)
                @php
                    $parsedDay = \Carbon\Carbon::parse($dayItem->date);
                    $pct       = $dayItem->total > 0 ? round(($dayItem->done / $dayItem->total) * 100) : 0;
                    $allDone   = $dayItem->done === $dayItem->total && $dayItem->total > 0;
                    $noneDone  = $dayItem->done === 0;
                    $barColor  = $allDone ? '#16a34a' : ($noneDone ? '#dc2626' : '#d97706');
                @endphp

                <div class="accordion-item border-0 border-bottom">

                    {{-- Accordion header --}}
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $idx === 0 ? '' : 'collapsed' }} py-3 px-4"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#dr{{ $idx }}"
                                aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}">
                            <div class="d-flex align-items-center w-100 me-2 gap-2">

                                {{-- Date icon --}}
                                <div class="rounded-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:32px;height:32px">
                                    <i class="bi bi-calendar3" style="font-size:14px"></i>
                                </div>

                                {{-- Date label --}}
                                <div>
                                    <div class="fw-semibold" style="font-size:14px">
                                        {{ $parsedDay->format('D, d M Y') }}
                                    </div>
                                    <div class="text-muted" style="font-size:11px">
                                        {{ $dayItem->watchmen->count() }} watchman{{ $dayItem->watchmen->count() !== 1 ? 'men' : '' }}
                                    </div>
                                </div>

                                {{-- Progress --}}
                                <div class="ms-auto d-flex align-items-center gap-3">
                                    <span class="badge rounded-pill px-2"
                                          style="background:{{ $barColor }}20;color:{{ $barColor }};border:1px solid {{ $barColor }}40;font-size:11px">
                                        {{ $dayItem->done }}/{{ $dayItem->total }} tasks
                                    </span>
                                    <div style="width:64px;height:6px;border-radius:3px;background:#e5e7eb;overflow:hidden">
                                        <div style="width:{{ $pct }}%;height:100%;border-radius:3px;background:{{ $barColor }}"></div>
                                    </div>
                                </div>

                            </div>
                        </button>
                    </h2>

                    {{-- Accordion body --}}
                    <div id="dr{{ $idx }}" class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}">
                        <div class="accordion-body p-0">
                            <table class="table table-sm align-middle mb-0" style="font-size:12px">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-2" style="width:36px"></th>
                                        <th class="py-2">Task</th>
                                        <th class="py-2">Schedule</th>
                                        <th class="py-2" style="width:80px">Sched. Time</th>
                                        <th class="py-2" style="width:90px">Completed At</th>
                                        <th class="py-2" style="width:56px">Photo</th>
                                        <th class="py-2">Location</th>
                                        <th class="py-2">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dayItem->watchmen as $watchmanItem)
                                        @php
                                            $wAllDone  = $watchmanItem->done === $watchmanItem->total && $watchmanItem->total > 0;
                                            $wPct       = $watchmanItem->total > 0 ? round(($watchmanItem->done / $watchmanItem->total) * 100) : 0;
                                            $wNoneDone = $watchmanItem->done === 0;
                                            $wColor    = $wAllDone ? '#16a34a' : ($wNoneDone ? '#dc2626' : '#d97706');
                                        @endphp

                                        {{-- Watchman sub-header --}}
                                        <tr style="background:#f8f9fa">
                                            <td colspan="8" class="px-4 py-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    @if ($watchmanItem->watchman->photo)
                                                        <img src="{{ Storage::url($watchmanItem->watchman->photo) }}"
                                                             class="rounded-circle object-fit-cover border flex-shrink-0"
                                                             style="width:22px;height:22px"
                                                             alt="{{ $watchmanItem->watchman->name }}">
                                                    @else
                                                        <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                                             style="width:22px;height:22px;font-size:.65rem">
                                                            {{ strtoupper(substr($watchmanItem->watchman->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <span class="fw-semibold" style="font-size:12px">
                                                        {{ $watchmanItem->watchman->name }}
                                                    </span>
                                                    @if ($watchmanItem->watchman->employee_id)
                                                        <span class="text-muted" style="font-size:11px">
                                                            {{ $watchmanItem->watchman->employee_id }}
                                                        </span>
                                                    @endif
                                                    <span class="badge rounded-pill ms-1"
                                                          style="background:{{ $wColor }}20;color:{{ $wColor }};border:1px solid {{ $wColor }}40;font-size:10px">
                                                        {{ $watchmanItem->done }}/{{ $watchmanItem->total }} tasks
                                                    </span>
                                                    <div style="width:64px;height:6px;border-radius:3px;background:#e5e7eb;overflow:hidden">
                                                        <div style="width:{{ $wPct }}%;height:100%;border-radius:3px;background:{{ $wColor }}"></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Task rows for this watchman --}}
                                        @foreach ($watchmanItem->tasks->sortBy('is_done') as $taskItem)
                                            <tr class="{{ $taskItem->is_done ? '' : 'table-danger' }}">
                                                <td class="px-4 py-2 text-center">
                                                    @if ($taskItem->is_done)
                                                        <i class="bi bi-check-circle-fill text-success"></i>
                                                    @else
                                                        <i class="bi bi-x-circle text-danger"></i>
                                                    @endif
                                                </td>
                                                <td class="py-2 fw-semibold">
                                                    {{ $taskItem->task->title }}
                                                </td>
                                                <td class="py-2">
                                                    @if ($taskItem->task->is_repetitive)
                                                        <span class="badge bg-light text-secondary border px-2" style="font-size:11px">
                                                            {{ $recurrenceLabels[$taskItem->task->recurrence_type] ?? ucfirst($taskItem->task->recurrence_type) }}
                                                        </span>
                                                        @if ($taskItem->task->recurrence_type === 'weekly' && !empty($taskItem->task->week_days))
                                                            <div class="text-muted" style="font-size:11px">
                                                                {{ collect($taskItem->task->week_days)->map(fn($d) => $weekDayNames[$d] ?? $d)->join(', ') }}
                                                            </div>
                                                        @elseif ($taskItem->task->recurrence_type === 'monthly' && $taskItem->task->month_day)
                                                            <div class="text-muted" style="font-size:11px">
                                                                Day {{ $taskItem->task->month_day }}
                                                            </div>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-light text-secondary border px-2" style="font-size:11px">One-time</span>
                                                        @if ($taskItem->task->deadline_date)
                                                            <div class="text-muted" style="font-size:11px">
                                                                {{ $taskItem->task->deadline_date->format('d M Y') }}
                                                            </div>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="py-2 text-muted text-nowrap">
                                                    {{ $taskItem->task->scheduled_time ? \Carbon\Carbon::parse($taskItem->task->scheduled_time)->format('h:i A') : '—' }}
                                                </td>
                                                <td class="py-2 text-muted text-nowrap">
                                                    {{ $taskItem->log?->completed_at?->format('h:i A') ?? '—' }}
                                                </td>
                                                <td class="py-2 text-center">
                                                    @if ($taskItem->log?->photo)
                                                        <img src="{{ Storage::url($taskItem->log->photo) }}"
                                                             alt="proof"
                                                             class="rounded border object-fit-cover task-photo-thumb"
                                                             style="width:36px;height:36px;cursor:pointer"
                                                             data-src="{{ Storage::url($taskItem->log->photo) }}"
                                                             data-bs-toggle="modal"
                                                             data-bs-target="#photoModal">
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="py-2 text-muted text-nowrap" style="font-size:11px">
                                                    @if ($taskItem->log?->latitude && $taskItem->log?->longitude)
                                                        <a href="https://maps.google.com/?q={{ $taskItem->log->latitude }},{{ $taskItem->log->longitude }}"
                                                           target="_blank" class="text-decoration-none">
                                                            <i class="bi bi-geo-alt-fill text-danger"></i>
                                                            {{ number_format($taskItem->log->latitude, 4) }},
                                                            {{ number_format($taskItem->log->longitude, 4) }}
                                                        </a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="py-2 text-muted">
                                                    {{ $taskItem->log?->remarks ?: '—' }}
                                                </td>
                                            </tr>
                                        @endforeach

                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>{{-- /.accordion-item --}}
            @endforeach
        </div>{{-- /.accordion --}}

        <div class="card-footer bg-white text-muted small py-2 px-4">
            {{ $dateReport->count() }} day{{ $dateReport->count() !== 1 ? 's' : '' }}
            &middot;
            {{ $dateReport->sum('done') }} completion{{ $dateReport->sum('done') !== 1 ? 's' : '' }} in range
        </div>

    @endif
</div>{{-- /.card --}}

{{-- Photo popup modal --}}
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2 px-3 border-0">
                <span class="fw-semibold small">Proof Photo</span>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <img id="photoModalImg" src="" alt="proof" class="img-fluid w-100 rounded-bottom">
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

// Photo popup: load full-size src into modal image on thumbnail click
document.addEventListener('click', function (e) {
    var thumb = e.target.closest('.task-photo-thumb');
    if (thumb) {
        document.getElementById('photoModalImg').src = thumb.dataset.src;
    }
});
</script>
@endpush
