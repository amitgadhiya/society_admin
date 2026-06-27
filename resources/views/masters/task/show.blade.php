@extends('layouts.app')

@section('title', $task->title . ' — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('task.index') }}">Tasks</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $task->title }}</li>
@endsection

@section('content')

    {{-- Page heading --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('task.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold text-dark mb-0">{{ $task->title }}</h4>
                <p class="text-muted small mb-0">Task details &amp; watchman assignments</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('task.edit', $task) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form action="{{ route('task.destroy', $task) }}" method="POST"
                  onsubmit="return confirm('Delete this task? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </form>
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Task details --}}
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <i class="bi bi-info-circle me-2"></i>Task Information
                </div>
                <div class="card-body p-4">
                    @php
                        $weekDayNames     = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'];
                        $monthNames       = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
                        $recurrenceLabels = ['daily'=>'Daily','weekly'=>'Weekly (Specific Days)','monthly'=>'Monthly (Specific Date)','quarterly'=>'Quarterly (Every 3 Months)','biannual'=>'Biannual (Every 6 Months)','annual'=>'Annual (Every 12 Months)'];
                    @endphp
                    <div class="row g-4">
                        <div class="col-12">
                            <p class="text-muted small fw-semibold mb-1">Title</p>
                            <p class="fw-semibold mb-0">{{ $task->title }}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted small fw-semibold mb-1">Description</p>
                            <p class="mb-0" style="white-space:pre-wrap">{{ $task->description ?: '—' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Status</p>
                            @if ($task->status === 'active')
                                <span class="badge bg-success rounded-pill px-3">Active</span>
                            @else
                                <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Created On</p>
                            <p class="fw-semibold mb-0">{{ optional($task->created_at)->format('d M Y') }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Scheduled Time</p>
                            <p class="fw-semibold mb-0">
                                @if ($task->scheduled_time)
                                    <i class="bi bi-clock me-1 text-primary"></i>
                                    {{ \Carbon\Carbon::parse($task->scheduled_time)->format('h:i A') }}
                                @else
                                    —
                                @endif
                            </p>
                        </div>

                        {{-- Schedule --}}
                        <div class="col-12">
                            <p class="text-muted small fw-semibold mb-2">Schedule</p>
                            @if (! $task->is_repetitive)
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex flex-wrap gap-3">
                                        <div>
                                            <span class="text-muted small">Type</span><br>
                                            <span class="badge bg-secondary rounded-pill px-3">One-time</span>
                                        </div>
                                        <div>
                                            <span class="text-muted small">Deadline Date</span><br>
                                            <span class="fw-semibold">{{ $task->deadline_date?->format('d M Y') ?? '—' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex flex-wrap gap-3 mb-2">
                                        <div>
                                            <span class="text-muted small">Type</span><br>
                                            <span class="badge bg-primary rounded-pill px-3">Repetitive</span>
                                        </div>
                                        <div>
                                            <span class="text-muted small">Recurrence</span><br>
                                            <span class="fw-semibold">{{ $recurrenceLabels[$task->recurrence_type] ?? ucfirst($task->recurrence_type ?? '—') }}</span>
                                        </div>
                                        <div>
                                            <span class="text-muted small">Days to Complete</span><br>
                                            <span class="fw-semibold">{{ $task->days_to_complete ?? '—' }} day(s)</span>
                                        </div>
                                    </div>

                                    {{-- Weekly days --}}
                                    @if ($task->recurrence_type === 'weekly' && $task->week_days)
                                        <div class="mt-2">
                                            <span class="text-muted small">Repeats on</span><br>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @foreach ($task->week_days as $wd)
                                                    <span class="badge bg-info text-dark rounded-pill px-2">{{ $weekDayNames[$wd] ?? $wd }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Monthly day + months --}}
                                    @if ($task->recurrence_type === 'monthly')
                                        <div class="mt-2">
                                            <span class="text-muted small">Day of Month</span><br>
                                            <span class="fw-semibold">{{ $task->month_day ?? '—' }}</span>
                                        </div>
                                        @if ($task->months)
                                            <div class="mt-2">
                                                <span class="text-muted small">Active Months</span><br>
                                                <div class="d-flex flex-wrap gap-1 mt-1">
                                                    @foreach ($task->months as $mo)
                                                        <span class="badge bg-info text-dark rounded-pill px-2">{{ $monthNames[$mo] ?? $mo }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endif

                                    {{-- Recurrence end --}}
                                    <div class="mt-2">
                                        <span class="text-muted small">Recurrence Ends</span><br>
                                        @if ($task->recurrence_ends === 'never')
                                            <span class="fw-semibold">Never</span>
                                        @elseif ($task->recurrence_ends === 'after_occurrences')
                                            <span class="fw-semibold">After {{ $task->occurrences ?? '—' }} occurrence(s)</span>
                                        @elseif ($task->recurrence_ends === 'on_date')
                                            <span class="fw-semibold">On {{ $task->end_date?->format('d M Y') ?? '—' }}</span>
                                        @else
                                            <span class="fw-semibold">—</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Watchman assignments --}}
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-person-badge me-2"></i>Assigned Watchmen</span>
                    <span class="badge bg-secondary rounded-pill">{{ $task->watchmanTasks->count() }}</span>
                </div>

                {{-- Assign form --}}
                <div class="card-body border-bottom pb-3">
                    @if ($availableWatchmen->isEmpty())
                        <p class="text-muted small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            All watchmen from your society are already assigned.
                        </p>
                    @else
                        <form action="{{ route('watchman-task.store', $task) }}" method="POST"
                              class="d-flex gap-2 align-items-end">
                            @csrf
                            <div class="flex-grow-1">
                                <label class="form-label fw-semibold small mb-1">Assign Watchman</label>
                                <select name="watchman_id"
                                        class="form-select form-select-sm @error('watchman_id') is-invalid @enderror">
                                    <option value="">— Select watchman —</option>
                                    @foreach ($availableWatchmen as $w)
                                        <option value="{{ $w->id }}">
                                            {{ $w->name }}{{ $w->employee_id ? ' (' . $w->employee_id . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('watchman_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>Assign
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Assignment list --}}
                @if ($task->watchmanTasks->isEmpty())
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-person-x d-block mb-2" style="font-size:1.5rem"></i>
                        No watchmen assigned yet
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach ($task->watchmanTasks as $wt)
                            <li class="list-group-item d-flex align-items-center justify-content-between py-3 px-4">
                                {{-- Avatar + name --}}
                                <div class="d-flex align-items-center gap-2">
                                    @if ($wt->watchman->photo)
                                        <img src="{{ Storage::url($wt->watchman->photo) }}"
                                             alt="{{ $wt->watchman->name }}"
                                             class="rounded-circle object-fit-cover border"
                                             style="width:36px;height:36px">
                                    @else
                                        <div class="avatar-initials" style="width:36px;height:36px;font-size:.75rem">
                                            {{ strtoupper(substr($wt->watchman->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold small">{{ $wt->watchman->name }}</div>
                                        @if ($wt->watchman->employee_id)
                                            <div class="text-muted" style="font-size:11px">{{ $wt->watchman->employee_id }}</div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Status badge + actions --}}
                                <div class="d-flex align-items-center gap-2">
                                    @if ($wt->status === 'active')
                                        <span class="badge bg-success rounded-pill px-2">Active</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-2">Inactive</span>
                                    @endif

                                    {{-- Toggle status --}}
                                    <form action="{{ route('watchman-task.update', $wt) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $wt->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                title="{{ $wt->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi {{ $wt->status === 'active' ? 'bi-pause-fill' : 'bi-play-fill' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Remove --}}
                                    <form action="{{ route('watchman-task.destroy', $wt) }}" method="POST"
                                          onsubmit="return confirm('Remove {{ $wt->watchman->name }} from this task?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

    </div>

@endsection
