@extends('layouts.app')

@section('title', 'Task Analysis — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('task.index') }}">Tasks</a></li>
    <li class="breadcrumb-item active" aria-current="page">Analysis</li>
@endsection

@section('content')

    {{-- Page heading --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">Task Analysis</h4>
            <p class="text-muted small mb-0">Filter completions by date range, watchman, or task</p>
        </div>
        <a href="{{ route('task.report') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-bar-chart-line me-1"></i>Summary Report
        </a>
    </div>

    {{-- Filter bar --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3 px-4">
            <form method="GET" action="{{ route('task.analysis') }}" class="row g-2 align-items-end">

                <div class="col-sm-6 col-lg-2">
                    <label class="form-label fw-semibold small mb-1">From</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                </div>

                <div class="col-sm-6 col-lg-2">
                    <label class="form-label fw-semibold small mb-1">To</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                </div>

                <div class="col-sm-6 col-lg-3">
                    <label class="form-label fw-semibold small mb-1">Watchman</label>
                    <select name="watchman_id" class="form-select form-select-sm">
                        <option value="">All Watchmen</option>
                        @foreach ($watchmenList as $w)
                            <option value="{{ $w->id }}" {{ (string)$watchmanId === (string)$w->id ? 'selected' : '' }}>
                                {{ $w->name }}{{ $w->employee_id ? ' ('.$w->employee_id.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <label class="form-label fw-semibold small mb-1">Task</label>
                    <select name="task_id" class="form-select form-select-sm">
                        <option value="">All Tasks</option>
                        @foreach ($tasksList as $t)
                            <option value="{{ $t->id }}" {{ (string)$taskId === (string)$t->id ? 'selected' : '' }}>
                                {{ $t->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i>Apply
                    </button>
                    <a href="{{ route('task.analysis') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>

            </form>
        </div>
    </div>

    {{-- Summary stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10"
                         style="width:48px;height:48px;flex-shrink:0">
                        <i class="bi bi-check2-circle text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Completions</div>
                        <div class="fw-bold fs-4 lh-1">{{ number_format($totalCompletions) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10"
                         style="width:48px;height:48px;flex-shrink:0">
                        <i class="bi bi-person-badge text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Active Watchmen</div>
                        <div class="fw-bold fs-4 lh-1">{{ $uniqueWatchmen }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning bg-opacity-10"
                         style="width:48px;height:48px;flex-shrink:0">
                        <i class="bi bi-clipboard-check text-warning fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Tasks Completed</div>
                        <div class="fw-bold fs-4 lh-1">{{ $uniqueTasks }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- By Watchman + By Task --}}
    <div class="row g-4 mb-4">

        {{-- By Watchman --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-person-badge me-2"></i>By Watchman</span>
                    <span class="badge bg-secondary rounded-pill">{{ $byWatchmanRows->count() }}</span>
                </div>
                @if ($byWatchmanRows->isEmpty())
                    <div class="text-center py-5 text-muted small">No data for selected filters.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Watchman</th>
                                    <th class="py-3 text-center">Completions</th>
                                    <th class="py-3 text-center">Tasks Done</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($byWatchmanRows as $row)
                                    @php $w = $watchmenMap[$row->watchman_id] ?? null @endphp
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="fw-semibold small">{{ $w?->name ?? '—' }}</div>
                                            @if ($w?->employee_id)
                                                <div class="text-muted" style="font-size:11px">{{ $w->employee_id }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="badge bg-success rounded-pill px-3">{{ $row->total }}</span>
                                        </td>
                                        <td class="py-3 text-center text-muted small">{{ $row->unique_tasks }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- By Task --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clipboard-check me-2"></i>By Task</span>
                    <span class="badge bg-secondary rounded-pill">{{ $byTaskRows->count() }}</span>
                </div>
                @if ($byTaskRows->isEmpty())
                    <div class="text-center py-5 text-muted small">No data for selected filters.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Task</th>
                                    <th class="py-3">Sched. Time</th>
                                    <th class="py-3 text-center">Completions</th>
                                    <th class="py-3 text-center">Watchmen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $recurrenceLabels = ['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly','quarterly'=>'Quarterly','biannual'=>'Biannual','annual'=>'Annual'];
                                @endphp
                                @foreach ($byTaskRows as $row)
                                    @php $t = $tasksMap[$row->task_id] ?? null @endphp
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="fw-semibold small">{{ $t?->title ?? '—' }}</div>
                                            @if ($t)
                                                @if ($t->is_repetitive)
                                                    <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:10px">
                                                        {{ $recurrenceLabels[$t->recurrence_type] ?? ucfirst($t->recurrence_type) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:10px">One-time</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="py-3 text-muted small text-nowrap">
                                            {{ $t?->scheduled_time ? \Carbon\Carbon::parse($t->scheduled_time)->format('h:i A') : '—' }}
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="badge bg-success rounded-pill px-3">{{ $row->total }}</span>
                                        </td>
                                        <td class="py-3 text-center text-muted small">{{ $row->unique_watchmen }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Detailed log --}}
    <div class="card shadow-sm">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul me-2"></i>Completion Log</span>
            <span class="badge bg-secondary rounded-pill">{{ $logs->total() }}</span>
        </div>

        @if ($logs->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox d-block mb-2" style="font-size:2.5rem"></i>
                <p class="small mb-0">No completion records found for the selected filters.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="py-3">Watchman</th>
                            <th class="py-3">Task</th>
                            <th class="py-3">Sched. Time</th>
                            <th class="py-3">Completed At</th>
                            <th class="py-3">Photo</th>
                            <th class="py-3">Location</th>
                            <th class="py-3">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td class="px-4 py-3 text-nowrap small fw-semibold">
                                    {{ $log->completion_date->format('d M Y') }}
                                </td>
                                <td class="py-3">
                                    <div class="fw-semibold small">{{ $log->watchman?->name ?? '—' }}</div>
                                    @if ($log->watchman?->employee_id)
                                        <div class="text-muted" style="font-size:11px">{{ $log->watchman->employee_id }}</div>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <div class="small">{{ $log->task?->title ?? '—' }}</div>
                                    @if ($log->task)
                                        @if ($log->task->is_repetitive)
                                            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:10px">
                                                {{ $recurrenceLabels[$log->task->recurrence_type] ?? ucfirst($log->task->recurrence_type) }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:10px">One-time</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-3 text-muted small text-nowrap">
                                    {{ $log->task?->scheduled_time ? \Carbon\Carbon::parse($log->task->scheduled_time)->format('h:i A') : '—' }}
                                </td>
                                <td class="py-3 text-muted small text-nowrap">
                                    {{ $log->completed_at ? $log->completed_at->format('h:i A') : '—' }}
                                </td>
                                <td class="py-3">
                                    @if ($log->photo)
                                        <a href="{{ Storage::url($log->photo) }}" target="_blank">
                                            <img src="{{ Storage::url($log->photo) }}"
                                                 alt="proof"
                                                 class="rounded border object-fit-cover"
                                                 style="width:40px;height:40px">
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="py-3 text-muted small text-nowrap">
                                    @if ($log->latitude && $log->longitude)
                                        <a href="https://maps.google.com/?q={{ $log->latitude }},{{ $log->longitude }}"
                                           target="_blank" class="text-decoration-none">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                            {{ number_format($log->latitude, 4) }},
                                            {{ number_format($log->longitude, 4) }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-3 text-muted small">
                                    {{ $log->remarks ?: '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-end">
                    {{ $logs->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
