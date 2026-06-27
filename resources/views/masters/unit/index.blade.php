@extends('layouts.app')

@section('title', 'Units — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Units</li>
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">Units</h4>
            <p class="text-muted small mb-0">Manage residential / commercial units in your society</p>
        </div>
        <a href="{{ route('unit.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>Add Unit
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

    {{-- Filters --}}
    <form method="GET" action="{{ route('unit.index') }}" class="row g-2 mb-4 align-items-center">
        <div class="col-12 col-sm-auto flex-grow-1" style="max-width:320px">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search unit no., owner, contact…"
                   class="form-control form-control-sm">
        </div>
        @if ($wings->count())
            <div class="col-12 col-sm-auto">
                <select name="wing" class="form-select form-select-sm">
                    <option value="">All Wings</option>
                    @foreach ($wings as $wing)
                        <option value="{{ $wing->id }}" {{ request('wing') == $wing->id ? 'selected' : '' }}>
                            {{ $wing->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        @if ($unitTypes->count())
            <div class="col-12 col-sm-auto">
                <select name="unit_type_id" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach ($unitTypes as $unitType)
                        <option value="{{ $unitType->id }}" {{ request('unit_type_id') == $unitType->id ? 'selected' : '' }}>
                            {{ $unitType->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="col-12 col-sm-auto">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $val => $label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-auto d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-secondary">
                <i class="bi bi-search me-1"></i>Search
            </button>
            @if (request()->hasAny(['search', 'wing', 'status']))
                <a href="{{ route('unit.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x me-1"></i>Clear
                </a>
            @endif
        </div>
    </form>

    <div class="card shadow-sm">
        @if ($units->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Unit No.</th>
                            <th class="py-3">Wing</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Floor</th>
                            <th class="py-3">Owner / Name</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($units as $unit)
                            <tr>
                                <td class="px-4 py-3 fw-semibold">{{ $unit->unit_number }}</td>
                                <td class="py-3 text-muted">{{ $unit->wing?->name ?? '—' }}</td>
                                <td class="py-3 text-muted">{{ $unit->unitType?->name ?? ($unit->unit_type ?? '—') }}</td>
                                <td class="py-3 text-muted">{{ $unit->floor ?? '—' }}</td>
                                <td class="py-3">
                                    <div class="fw-semibold">{{ $unit->registered_in_name_of ?? '—' }}</div>
                                    @if ($unit->contact_number)
                                        <div class="text-muted small">{{ $unit->contact_number }}</div>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @php
                                        $badge = match($unit->status) {
                                            'active'          => 'bg-success',
                                            'inactive'            => 'bg-warning text-dark',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }} rounded-pill px-3">
                                        {{ ucfirst(str_replace('_', ' ', $unit->status)) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('unit.show', $unit) }}"
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('unit.edit', $unit) }}"
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('unit.destroy', $unit) }}" method="POST"
                                              onsubmit="return confirm('Delete unit {{ $unit->unit_number }}? This cannot be undone.')">
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

            @if ($units->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-end">
                    {{ $units->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-house-door text-muted" style="font-size:3rem"></i>
                </div>
                <h6 class="fw-semibold text-dark">No units found</h6>
                <p class="text-muted small mb-4">
                    {{ request()->hasAny(['search', 'wing', 'status']) ? 'No results match your filters.' : 'Add your first unit to get started.' }}
                </p>
                @unless (request()->hasAny(['search', 'wing', 'status']))
                    <a href="{{ route('unit.create') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Add Unit
                    </a>
                @endunless
            </div>
        @endif
    </div>

@endsection
