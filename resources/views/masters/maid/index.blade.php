@extends('layouts.app')

@section('title', 'Maids — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Maids</li>
@endsection

@section('content')

    @php
        $typeLabels = [
            'maid'       => 'Maid',
            'cook'       => 'Cook',
            'driver'     => 'Driver',
            'nanny'      => 'Nanny',
            'babysitter' => 'Babysitter',
            'cleaner'    => 'Cleaner',
            'others'     => 'Others',
        ];
    @endphp

    {{-- Page heading --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">Maids</h4>
            <p class="text-muted small mb-0">Manage household staff registered in your society</p>
        </div>
        <a href="{{ route('maid.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>Add Maid
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

    {{-- Filters --}}
    <form method="GET" action="{{ route('maid.index') }}" class="d-flex flex-wrap gap-2 mb-4 align-items-center">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by name or mobile…"
               class="form-control form-control-sm" style="max-width:280px">

        <select name="status" class="form-select form-select-sm" style="max-width:140px">
            <option value="">All Status</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>

        <button type="submit" class="btn btn-sm btn-secondary">
            <i class="bi bi-search me-1"></i>Filter
        </button>
        @if (request('search') || request('status'))
            <a href="{{ route('maid.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x me-1"></i>Clear
            </a>
        @endif
    </form>

    {{-- Table card --}}
    <div class="card shadow-sm">
        @if ($maids->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="py-3">Mobile</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($maids as $m)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($m->photo)
                                            <img src="{{ Storage::url($m->photo) }}" alt="{{ $m->name }}"
                                                 class="rounded-circle object-fit-cover border"
                                                 style="width:36px;height:36px">
                                        @else
                                            <div class="avatar-initials">
                                                {{ strtoupper(substr($m->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <span class="fw-semibold">{{ $m->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-muted">{{ $m->mobile ?? '—' }}</td>
                                <td class="py-3">
                                    @if ($m->status === 'active')
                                        <span class="badge bg-success rounded-pill px-3">Active</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('maid.show', $m) }}"
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('maid.edit', $m) }}"
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('maid.destroy', $m) }}" method="POST"
                                              onsubmit="return confirm('Delete {{ $m->name }}? This cannot be undone.')">
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

            @if ($maids->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-end">
                    {{ $maids->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-person-x text-muted" style="font-size:3rem"></i>
                </div>
                <h6 class="fw-semibold text-dark">No maids found</h6>
                <p class="text-muted small mb-4">
                    {{ (request('search') || request('status')) ? 'No results match your filters.' : 'Add your first maid to get started.' }}
                </p>
                @unless (request('search') || request('status'))
                    <a href="{{ route('maid.create') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Add Maid
                    </a>
                @endunless
            </div>
        @endif
    </div>

@endsection
