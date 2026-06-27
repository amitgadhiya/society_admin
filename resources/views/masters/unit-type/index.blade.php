@extends('layouts.app')

@section('title', 'Unit Types — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Unit Types</li>
@endsection

@section('content')

    {{-- Page heading --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">Unit Types</h4>
            <p class="text-muted small mb-0">Manage unit type categories for your society</p>
        </div>
        <a href="{{ route('unit-type.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>Add Unit Type
        </a>
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

    {{-- Search --}}
    <form method="GET" action="{{ route('unit-type.index') }}" class="d-flex gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by name…"
               class="form-control form-control-sm" style="max-width:320px">
        <button type="submit" class="btn btn-sm btn-secondary">
            <i class="bi bi-search me-1"></i>Search
        </button>
        @if (request('search'))
            <a href="{{ route('unit-type.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x me-1"></i>Clear
            </a>
        @endif
    </form>

    {{-- Table card --}}
    <div class="card shadow-sm">
        @if ($unitTypes->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="py-3">Name</th>
                            <th class="py-3">Units</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($unitTypes as $type)
                            <tr>
                                <td class="px-4 py-3 text-muted small">{{ $unitTypes->firstItem() + $loop->index }}</td>
                                <td class="py-3 fw-semibold">{{ $type->name }}</td>
                                <td class="py-3">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2">
                                        {{ $type->units_count }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    @if ($type->status === 'active')
                                        <span class="badge bg-success rounded-pill px-3">Active</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('unit-type.show', $type) }}"
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('unit-type.edit', $type) }}"
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('unit-type.destroy', $type) }}" method="POST"
                                              onsubmit="return confirm('Delete unit type \'{{ addslashes($type->name) }}\'? This cannot be undone.')">
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

            @if ($unitTypes->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-end">
                    {{ $unitTypes->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-tags text-muted" style="font-size:3rem"></i>
                </div>
                <h6 class="fw-semibold text-dark">No unit types found</h6>
                <p class="text-muted small mb-4">
                    {{ request('search') ? 'No results match your search.' : 'Add your first unit type to get started.' }}
                </p>
                @unless (request('search'))
                    <a href="{{ route('unit-type.create') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Add Unit Type
                    </a>
                @endunless
            </div>
        @endif
    </div>

@endsection
