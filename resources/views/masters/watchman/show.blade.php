@extends('layouts.app')

@section('title', $watchman->name . ' — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('watchman.index') }}">Watchmen</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $watchman->name }}</li>
@endsection

@section('content')

    {{-- Page heading --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('watchman.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold text-dark mb-0">{{ $watchman->name }}</h4>
                <p class="text-muted small mb-0">Watchman details &amp; task assignments</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('watchman.edit', $watchman) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form action="{{ route('watchman.destroy', $watchman) }}" method="POST"
                  onsubmit="return confirm('Delete {{ $watchman->name }}? This cannot be undone.')">
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

    {{-- Top row: profile + details --}}
    <div class="row g-4 mb-4">

        {{-- Profile card --}}
        <div class="col-lg-3">
            <div class="card shadow-sm text-center p-4">
                @if ($watchman->photo)
                    <img src="{{ Storage::url($watchman->photo) }}" alt="{{ $watchman->name }}"
                         class="rounded-circle object-fit-cover border border-3 mx-auto mb-3"
                         style="width:90px;height:90px">
                @else
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-indigo text-white fw-bold"
                         style="width:90px;height:90px;font-size:2.5rem">
                        {{ strtoupper(substr($watchman->name, 0, 1)) }}
                    </div>
                @endif

                <h5 class="fw-bold mb-0">{{ $watchman->name }}</h5>

                @if ($watchman->employee_id)
                    <p class="text-muted small mb-2">{{ $watchman->employee_id }}</p>
                @endif

                <div class="mt-2">
                    @if ($watchman->active)
                        <span class="badge bg-success rounded-pill px-3 py-2">Active</span>
                    @else
                        <span class="badge bg-danger rounded-pill px-3 py-2">Inactive</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Details card --}}
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <i class="bi bi-info-circle me-2"></i>Watchman Information
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Full Name</p>
                            <p class="fw-semibold mb-0">{{ $watchman->name }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Mobile</p>
                            <p class="fw-semibold mb-0">{{ $watchman->mobile ?? '—' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Employee ID</p>
                            <p class="fw-semibold mb-0">{{ $watchman->employee_id ?? '—' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Status</p>
                            @if ($watchman->active)
                                <span class="badge bg-success rounded-pill px-3">Active</span>
                            @else
                                <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Added On</p>
                            <p class="fw-semibold mb-0">{{ optional($watchman->created_at)->format('d M Y') }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Last Updated</p>
                            <p class="fw-semibold mb-0">{{ optional($watchman->updated_at)->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Task assignments --}}
    <div class="card shadow-sm">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clipboard-check me-2"></i>Assigned Tasks</span>
            <span class="badge bg-secondary rounded-pill">{{ $watchman->watchmanTasks->count() }}</span>
        </div>

        {{-- Assign form --}}
        <div class="card-body border-bottom pb-3">
            @if ($availableTasks->isEmpty())
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    No active tasks available to assign.
                </p>
            @else
                <form action="{{ route('watchman-task.store-for-watchman', $watchman) }}" method="POST"
                      class="d-flex gap-2 align-items-end" style="max-width:520px">
                    @csrf
                    <div class="flex-grow-1">
                        <label class="form-label fw-semibold small mb-1">Assign Task</label>
                        <select name="task_id"
                                class="form-select form-select-sm @error('task_id') is-invalid @enderror">
                            <option value="">— Select task —</option>
                            @foreach ($availableTasks as $t)
                                <option value="{{ $t->id }}">{{ $t->title }}</option>
                            @endforeach
                        </select>
                        @error('task_id')
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
        @if ($watchman->watchmanTasks->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-clipboard-x d-block mb-2" style="font-size:2rem"></i>
                <p class="small mb-0">No tasks assigned yet</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Task</th>
                            <th class="py-3">Description</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($watchman->watchmanTasks as $wt)
                            <tr>
                                <td class="px-4 py-3 fw-semibold">{{ $wt->task->title }}</td>
                                <td class="py-3 text-muted" style="max-width:300px">
                                    {{ $wt->task->description
                                        ? \Illuminate\Support\Str::limit($wt->task->description, 70)
                                        : '—' }}
                                </td>
                                <td class="py-3">
                                    @if ($wt->status === 'active')
                                        <span class="badge bg-success rounded-pill px-3">Active</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <div class="d-flex justify-content-end gap-1">
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
                                              onsubmit="return confirm('Remove {{ $wt->task->title }} from this watchman?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection
