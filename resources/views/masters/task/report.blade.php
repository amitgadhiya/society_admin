@extends('layouts.app')

@section('title', 'Task Report — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('task.index') }}">Tasks</a></li>
    <li class="breadcrumb-item active" aria-current="page">Report</li>
@endsection

@push('styles')
<style>
    .stat-report-card {
        border: none;
        border-radius: 10px;
    }
    .stat-report-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .stat-report-icon.green  { background: #dcfce7; color: #16a34a; }
    .stat-report-icon.blue   { background: #dbeafe; color: #2563eb; }
    .stat-report-icon.violet { background: #ede9fe; color: #7c3aed; }
    .stat-report-icon.amber  { background: #fef3c7; color: #d97706; }
    .range-btn.active { background: #4f46e5; color: #fff; border-color: #4f46e5; }
    .completion-bar {
        height: 6px;
        border-radius: 3px;
        background: #e5e7eb;
        overflow: hidden;
    }
    .completion-bar-fill {
        height: 100%;
        border-radius: 3px;
        background: #4f46e5;
    }
</style>
@endpush

@section('content')

    @php
        $scheduleLabels = ['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly','quarterly'=>'Quarterly','biannual'=>'Biannual','annual'=>'Annual'];
        $weekDayNames   = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'];
        $monthNames     = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
    @endphp

    {{-- Page heading --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-0">Task Report</h4>
            <p class="text-muted small mb-0">Watchman task completion overview</p>
        </div>
        {{-- Date range buttons --}}
        <div class="d-flex gap-1">
            @foreach ([7 => '7 Days', 14 => '14 Days', 30 => '30 Days'] as $val => $label)
                <a href="{{ route('task.report', ['days' => $val, 'schedule_type' => $scheduleType]) }}"
                   class="btn btn-sm btn-outline-secondary range-btn {{ $days == $val ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Schedule type filter --}}
    <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
        <span class="text-muted small me-1">Schedule:</span>
        <a href="{{ route('task.report', ['days' => $days]) }}"
           class="btn btn-sm {{ is_null($scheduleType) ? 'btn-secondary' : 'btn-outline-secondary' }}">
            All
        </a>
        @foreach ($scheduleLabels as $val => $label)
            <a href="{{ route('task.report', ['days' => $days, 'schedule_type' => $val]) }}"
               class="btn btn-sm {{ $scheduleType === $val ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
                @if (($tasksByScheduleType[$val] ?? 0) > 0)
                    <span class="badge {{ $scheduleType === $val ? 'bg-white text-primary' : 'bg-secondary' }} rounded-pill ms-1">
                        {{ $tasksByScheduleType[$val] }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-report-card shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-report-icon green"><i class="bi bi-check2-circle"></i></div>
                    <div>
                        <div class="fw-bold fs-4 lh-1">{{ $completedToday }}</div>
                        <div class="text-muted small mt-1">Completed Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-report-card shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-report-icon blue"><i class="bi bi-calendar-check"></i></div>
                    <div>
                        <div class="fw-bold fs-4 lh-1">{{ $completedThisMonth }}</div>
                        <div class="text-muted small mt-1">This Month</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-report-card shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-report-icon violet"><i class="bi bi-person-badge"></i></div>
                    <div>
                        <div class="fw-bold fs-4 lh-1">{{ $activeWatchmen }}</div>
                        <div class="text-muted small mt-1">Active Watchmen</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-report-card shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-report-icon amber"><i class="bi bi-clipboard-check"></i></div>
                    <div>
                        <div class="fw-bold fs-4 lh-1">{{ $activeTasks }}</div>
                        <div class="text-muted small mt-1">Active Tasks</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Watchman Report (today + all-time, accordion) --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-people me-2"></i>By Watchman</span>
            <span class="text-muted small">{{ now()->format('l, d M Y') }}</span>
        </div>

        @if ($watchmanDailyReport->isEmpty())
            <div class="card-body text-center text-muted py-5 small">
                <i class="bi bi-moon d-block mb-2" style="font-size:1.5rem"></i>
                No active task assignments found
            </div>
        @else
            <div class="accordion accordion-flush" id="todayReportAccordion">
                @foreach ($watchmanDailyReport as $idx => $item)
                    @php
                        $pct      = $item->total > 0 ? round(($item->done_count / $item->total) * 100) : 0;
                        $allDone  = $item->done_count === $item->total;
                        $noneDone = $item->done_count === 0;
                        $barColor = $allDone ? '#16a34a' : ($noneDone ? '#dc2626' : '#d97706');
                    @endphp
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-3 px-4" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#wr{{ $idx }}"
                                    aria-expanded="false">
                                <div class="d-flex align-items-center w-100 me-2 gap-2">
                                    {{-- Avatar --}}
                                    @if ($item->watchman->photo)
                                        <img src="{{ Storage::url($item->watchman->photo) }}"
                                             class="rounded-circle object-fit-cover border flex-shrink-0"
                                             style="width:32px;height:32px"
                                             alt="{{ $item->watchman->name }}">
                                    @else
                                        <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                             style="width:32px;height:32px;font-size:.75rem">
                                            {{ strtoupper(substr($item->watchman->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    {{-- Name --}}
                                    <div>
                                        <div class="fw-semibold" style="font-size:14px">{{ $item->watchman->name }}</div>
                                        @if ($item->watchman->employee_id)
                                            <div class="text-muted" style="font-size:11px">{{ $item->watchman->employee_id }}</div>
                                        @endif
                                    </div>
                                    {{-- Stats --}}
                                    <div class="ms-auto d-flex align-items-center gap-3">
                                        {{-- Today progress --}}
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge rounded-pill px-2"
                                                  style="background:{{ $barColor }}20;color:{{ $barColor }};border:1px solid {{ $barColor }}40;font-size:11px">
                                                {{ $item->done_count }}/{{ $item->total }} today
                                            </span>
                                            <div style="width:64px;height:6px;border-radius:3px;background:#e5e7eb;overflow:hidden">
                                                <div style="width:{{ $pct }}%;height:100%;border-radius:3px;background:{{ $barColor }}"></div>
                                            </div>
                                        </div>
                                        {{-- All-time --}}
                                        <div class="text-center d-none d-md-block" style="min-width:44px">
                                            <div class="fw-semibold lh-1" style="font-size:14px">{{ $item->completed_total }}</div>
                                            <div class="text-muted" style="font-size:10px">all-time</div>
                                        </div>
                                        {{-- Last completion --}}
                                        <div class="text-end d-none d-lg-block" style="min-width:76px">
                                            <div class="text-muted" style="font-size:10px">last done</div>
                                            <div class="fw-semibold" style="font-size:11px">
                                                {{ $item->last_completion ? \Carbon\Carbon::parse($item->last_completion)->format('d M Y') : '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="wr{{ $idx }}" class="accordion-collapse collapse">
                            <div class="accordion-body p-0">
                                <table class="table table-sm align-middle mb-0" style="font-size:12px">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-4 py-2" style="width:36px"></th>
                                            <th class="py-2">Task</th>
                                            <th class="py-2">Schedule</th>
                                            <th class="py-2" style="width:80px">Sched. Time</th>
                                            <th class="py-2" style="width:90px">Completed At</th>
                                            <th class="py-2">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($item->tasks->sortBy('is_done') as $taskItem)
                                            <tr class="{{ $taskItem->is_done ? '' : 'table-danger' }}">
                                                <td class="px-4 py-2 text-center">
                                                    @if ($taskItem->is_done)
                                                        <i class="bi bi-check-circle-fill text-success"></i>
                                                    @else
                                                        <i class="bi bi-x-circle text-danger"></i>
                                                    @endif
                                                </td>
                                                <td class="py-2 fw-semibold">{{ $taskItem->task->title }}</td>
                                                <td class="py-2">
                                                    @if ($taskItem->task->is_repetitive)
                                                        <span class="badge bg-light text-secondary border px-2" style="font-size:11px">
                                                            {{ $scheduleLabels[$taskItem->task->recurrence_type] ?? ucfirst($taskItem->task->recurrence_type) }}
                                                        </span>
                                                        @if ($taskItem->task->recurrence_type === 'weekly' && !empty($taskItem->task->week_days))
                                                            <div class="text-muted" style="font-size:11px">
                                                                {{ collect($taskItem->task->week_days)->map(fn($d) => $weekDayNames[$d] ?? $d)->join(', ') }}
                                                            </div>
                                                        @elseif ($taskItem->task->recurrence_type === 'monthly' && $taskItem->task->month_day)
                                                            <div class="text-muted" style="font-size:11px">Day {{ $taskItem->task->month_day }}</div>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-light text-secondary border px-2" style="font-size:11px">One-time</span>
                                                        @if ($taskItem->task->deadline_date)
                                                            <div class="text-muted" style="font-size:11px">{{ $taskItem->task->deadline_date->format('d M Y') }}</div>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="py-2 text-muted text-nowrap">
                                                    {{ $taskItem->task->scheduled_time ? \Carbon\Carbon::parse($taskItem->task->scheduled_time)->format('h:i A') : '—' }}
                                                </td>
                                                <td class="py-2 text-muted text-nowrap">
                                                    {{ $taskItem->log?->completed_at?->format('h:i A') ?? '—' }}
                                                </td>
                                                <td class="py-2 text-muted">
                                                    {{ $taskItem->log?->remarks ?: '—' }}
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
            <div class="card-footer bg-white text-muted small py-2">
                {{ $watchmanDailyReport->count() }} watchman{{ $watchmanDailyReport->count() !== 1 ? 'men' : '' }} · {{ $completedToday }} completion{{ $completedToday !== 1 ? 's' : '' }} today
            </div>
        @endif
    </div>

    {{-- Chart + By Task --}}
    <div class="row g-4 mb-4">

        {{-- By Day chart --}}
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-bar-chart-line me-2"></i>Completions by Day</span>
                    <span class="text-muted small">Last {{ $days }} days</span>
                </div>
                <div class="card-body p-3">
                    <canvas id="dayChart" style="max-height:260px"></canvas>
                </div>
            </div>
        </div>

        {{-- By Task --}}
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3">
                    <i class="bi bi-list-check me-2"></i>By Task
                </div>
                @if ($byTask->isEmpty())
                    <div class="card-body text-center text-muted small py-5">
                        <i class="bi bi-clipboard-x d-block mb-2" style="font-size:1.5rem"></i>No completion data yet
                    </div>
                @else
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0" style="font-size:13px">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3">Task</th>
                                    <th class="py-3">Schedule</th>
                                    <th class="py-3" style="width:80px">Time</th>
                                    <th class="py-3 text-center" style="width:72px">Today</th>
                                    <th class="py-3 text-center" style="width:72px">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($byTask as $t)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <div class="fw-semibold">{{ $t->title }}</div>
                                            @if ($t->status === 'inactive')
                                                <span class="text-muted" style="font-size:11px">inactive</span>
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            @if ($t->is_repetitive)
                                                <span class="badge bg-light text-secondary border px-2" style="font-size:11px">
                                                    {{ $scheduleLabels[$t->recurrence_type] ?? ucfirst($t->recurrence_type) }}
                                                </span>
                                                @if ($t->recurrence_type === 'weekly' && !empty($t->week_days))
                                                    <div class="text-muted" style="font-size:11px">
                                                        {{ collect($t->week_days)->map(fn($d) => $weekDayNames[$d] ?? $d)->join(', ') }}
                                                    </div>
                                                @elseif ($t->recurrence_type === 'monthly' && $t->month_day)
                                                    <div class="text-muted" style="font-size:11px">Day {{ $t->month_day }}</div>
                                                @endif
                                            @else
                                                <span class="badge bg-light text-secondary border px-2" style="font-size:11px">One-time</span>
                                                @if ($t->deadline_date)
                                                    <div class="text-muted" style="font-size:11px">{{ $t->deadline_date->format('d M Y') }}</div>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="py-2 text-muted text-nowrap" style="font-size:12px">
                                            {{ $t->scheduled_time ? \Carbon\Carbon::parse($t->scheduled_time)->format('h:i A') : '—' }}
                                        </td>
                                        <td class="py-2 text-center">
                                            @if ($t->today_completions > 0)
                                                <span class="badge bg-success rounded-pill px-2">{{ $t->today_completions }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="py-2 text-center fw-semibold">{{ $t->total_completions }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>


@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = {!! json_encode($dayLabels) !!};
    const counts = {!! json_encode($dayCounts) !!};
    const max    = Math.max(...counts, 1);

    new Chart(document.getElementById('dayChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Completions',
                data: counts,
                backgroundColor: 'rgba(79, 70, 229, 0.75)',
                borderColor:     'rgba(79, 70, 229, 1)',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} completion${ctx.parsed.y !== 1 ? 's' : ''}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: max + 1,
                    ticks: { stepSize: 1, precision: 0 },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
})();
</script>
@endpush
