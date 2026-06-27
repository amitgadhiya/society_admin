@extends('layouts.app')

@section('title', 'Tasks — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tasks</li>
@endsection

@section('content')

    {{-- Page heading --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">Tasks</h4>
            <p class="text-muted small mb-0">Manage watchman task templates</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('task.report') }}" class="btn btn-outline-secondary">
                <i class="bi bi-bar-chart-line me-1"></i>Report
            </a>
            <a href="{{ route('task.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg me-1"></i>Add Task
            </a>
        </div>
    </div>

    {{-- Flash success --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Search --}}
    <form method="GET" action="{{ route('task.index') }}" class="d-flex gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by title or description…"
               class="form-control form-control-sm" style="max-width:360px">
        <button type="submit" class="btn btn-sm btn-secondary">
            <i class="bi bi-search me-1"></i>Search
        </button>
        @if (request('search'))
            <a href="{{ route('task.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x me-1"></i>Clear
            </a>
        @endif
    </form>

    {{-- Table card --}}
    <div class="card shadow-sm">
        @if ($tasks->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    @php
                        $recurrenceLabels = ['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly','quarterly'=>'Quarterly','biannual'=>'Biannual','annual'=>'Annual'];
                    @endphp
                    <thead>
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="py-3">Title</th>
                            <th class="py-3">Schedule</th>
                            <th class="py-3">Time</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $task)
                            <tr>
                                <td class="px-4 py-3 text-muted small">{{ $tasks->firstItem() + $loop->index }}</td>
                                <td class="py-3 fw-semibold">
                                    {{ $task->title }}
                                    @if ($task->description)
                                        <div class="text-muted small fw-normal">{{ \Illuminate\Support\Str::limit($task->description, 60) }}</div>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if (! $task->is_repetitive)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">One-time</span>
                                        @if ($task->deadline_date)
                                            <div class="text-muted small mt-1">{{ $task->deadline_date->format('d M Y') }}</div>
                                        @endif
                                    @else
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">
                                            {{ $recurrenceLabels[$task->recurrence_type] ?? ucfirst($task->recurrence_type ?? '—') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if ($task->scheduled_time)
                                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($task->scheduled_time)->format('h:i A') }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if ($task->status === 'active')
                                        <span class="badge bg-success rounded-pill px-3">Active</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('task.show', $task) }}"
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('task.edit', $task) }}"
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('task.destroy', $task) }}" method="POST"
                                              onsubmit="return confirm('Delete this task? This cannot be undone.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($tasks->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-end">
                    {{ $tasks->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-clipboard-x text-muted" style="font-size:3rem"></i>
                </div>
                <h6 class="fw-semibold text-dark">No tasks found</h6>
                <p class="text-muted small mb-4">
                    {{ request('search') ? 'No results match your search.' : 'Add your first task template to get started.' }}
                </p>
                @unless (request('search'))
                    <a href="{{ route('task.create') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Add Task
                    </a>
                @endunless
            </div>
        @endif
    </div>

@endsection
