@extends('layouts.app')

@section('title', 'Watchmen — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Watchmen</li>
@endsection

@section('content')

    {{-- Page heading --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">Watchmen</h4>
            <p class="text-muted small mb-0">Manage security watchmen for your society</p>
        </div>
        <a href="{{ route('watchman.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>Add Watchman
        </a>
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
    <form method="GET" action="{{ route('watchman.index') }}" class="d-flex gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by name, mobile or employee ID…"
               class="form-control form-control-sm" style="max-width:360px">
        <button type="submit" class="btn btn-sm btn-secondary">
            <i class="bi bi-search me-1"></i>Search
        </button>
        @if (request('search'))
            <a href="{{ route('watchman.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x me-1"></i>Clear
            </a>
        @endif
    </form>

    {{-- Table card --}}
    <div class="card shadow-sm">
        @if ($watchmen->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Watchman</th>
                            <th class="py-3">Mobile</th>
                            <th class="py-3">Employee ID</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($watchmen as $w)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($w->photo)
                                            <img src="{{ Storage::url($w->photo) }}" alt="{{ $w->name }}"
                                                 class="rounded-circle object-fit-cover border"
                                                 style="width:36px;height:36px">
                                        @else
                                            <div class="avatar-initials">
                                                {{ strtoupper(substr($w->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <span class="fw-semibold">{{ $w->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-muted">{{ $w->mobile ?? '—' }}</td>
                                <td class="py-3 text-muted">{{ $w->employee_id ?? '—' }}</td>
                                <td class="py-3">
                                    @if ($w->active)
                                        <span class="badge bg-success rounded-pill px-3">Active</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('watchman.show', $w) }}"
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('watchman.edit', $w) }}"
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('watchman.destroy', $w) }}" method="POST"
                                              onsubmit="return confirm('Delete {{ $w->name }}? This cannot be undone.')">
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

            @if ($watchmen->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-end">
                    {{ $watchmen->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-person-x text-muted" style="font-size:3rem"></i>
                </div>
                <h6 class="fw-semibold text-dark">No watchmen found</h6>
                <p class="text-muted small mb-4">
                    {{ request('search') ? 'No results match your search.' : 'Add your first watchman to get started.' }}
                </p>
                @unless (request('search'))
                    <a href="{{ route('watchman.create') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Add Watchman
                    </a>
                @endunless
            </div>
        @endif
    </div>

@endsection
