@extends('layouts.app')

@section('title', $wing->name . ' — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('wing.index') }}">Wings</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $wing->name }}</li>
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('wing.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold text-dark mb-0">{{ $wing->name }}</h4>
                <p class="text-muted small mb-0">Wing details &amp; assigned units</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('wing.edit', $wing) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form action="{{ route('wing.destroy', $wing) }}" method="POST"
                  onsubmit="return confirm('Delete wing \'{{ addslashes($wing->name) }}\'? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </form>
        </div>
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

    <div class="row g-4">

        {{-- Wing details --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <i class="bi bi-info-circle me-2"></i>Wing Information
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <p class="text-muted small fw-semibold mb-1">Name</p>
                            <p class="fw-semibold mb-0">{{ $wing->name }}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted small fw-semibold mb-1">Units Assigned</p>
                            <p class="fw-semibold mb-0">{{ $units->count() }}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted small fw-semibold mb-1">Created On</p>
                            <p class="fw-semibold mb-0">{{ optional($wing->created_at)->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Assigned units --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-house me-2"></i>Units in this wing</span>
                    <span class="badge bg-secondary rounded-pill">{{ $units->count() }}</span>
                </div>

                @if ($units->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-house-x d-block mb-2" style="font-size:2rem"></i>
                        <p class="small mb-0">No units assigned to this wing yet.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Unit No.</th>
                                    <th class="py-3">Type</th>
                                    <th class="py-3">Floor</th>
                                    <th class="py-3">Owner / Name</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3 text-end px-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($units as $unit)
                                    <tr>
                                        <td class="px-4 py-3 fw-semibold">{{ $unit->unit_number }}</td>
                                        <td class="py-3 text-muted small">{{ $unit->unitType?->name ?? '—' }}</td>
                                        <td class="py-3 text-muted small">{{ $unit->floor ?? '—' }}</td>
                                        <td class="py-3 small">{{ $unit->registered_in_name_of ?? '—' }}</td>
                                        <td class="py-3">
                                            @if ($unit->status === 'active')
                                                <span class="badge bg-success rounded-pill px-2">Active</span>
                                            @else
                                                <span class="badge bg-danger rounded-pill px-2">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-end">
                                            <a href="{{ route('unit.show', $unit) }}"
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
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
