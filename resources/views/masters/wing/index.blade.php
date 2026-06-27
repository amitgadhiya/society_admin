@extends('layouts.app')

@section('title', 'Wings — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Wings</li>
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">Wings</h4>
            <p class="text-muted small mb-0">Manage building wings for your society</p>
        </div>
        <a href="{{ route('wing.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>Add Wing
        </a>
    </div>

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

    <form method="GET" action="{{ route('wing.index') }}" class="d-flex gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by name…"
               class="form-control form-control-sm" style="max-width:320px">
        <button type="submit" class="btn btn-sm btn-secondary">
            <i class="bi bi-search me-1"></i>Search
        </button>
        @if (request('search'))
            <a href="{{ route('wing.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x me-1"></i>Clear
            </a>
        @endif
    </form>

    <div class="card shadow-sm">
        @if ($wings->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="py-3">Name</th>
                            <th class="py-3">Units</th>
                            <th class="py-3 text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($wings as $wing)
                            <tr>
                                <td class="px-4 py-3 text-muted small">{{ $wings->firstItem() + $loop->index }}</td>
                                <td class="py-3 fw-semibold">{{ $wing->name }}</td>
                                <td class="py-3">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2">
                                        {{ $wing->units_count }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('wing.show', $wing) }}"
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('wing.edit', $wing) }}"
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('wing.destroy', $wing) }}" method="POST"
                                              onsubmit="return confirm('Delete wing \'{{ addslashes($wing->name) }}\'? This cannot be undone.')">
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

            @if ($wings->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-end">
                    {{ $wings->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-buildings text-muted" style="font-size:3rem"></i>
                </div>
                <h6 class="fw-semibold text-dark">No wings found</h6>
                <p class="text-muted small mb-4">
                    {{ request('search') ? 'No results match your search.' : 'Add your first wing to get started.' }}
                </p>
                @unless (request('search'))
                    <a href="{{ route('wing.create') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Add Wing
                    </a>
                @endunless
            </div>
        @endif
    </div>

@endsection
