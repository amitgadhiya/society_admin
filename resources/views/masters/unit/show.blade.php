@extends('layouts.app')

@section('title', 'Unit {{ $unit->unit_number }} — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('unit.index') }}">Units</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $unit->unit_number }}</li>
@endsection

@section('content')

    {{-- Page heading --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('unit.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold text-dark mb-0">Unit {{ $unit->unit_number }}</h4>
                <p class="text-muted small mb-0">
                    {{ $unit->wing?->name ? $unit->wing->name . ' · ' : '' }}
                    {{ $unit->unitType?->name ?? ($unit->unit_type ?? 'No type') }}
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('unit.edit', $unit) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form action="{{ route('unit.destroy', $unit) }}" method="POST"
                  onsubmit="return confirm('Delete unit {{ $unit->unit_number }}? This cannot be undone.')">
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

        {{-- Unit Details --}}
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header py-3 fw-semibold">
                    <i class="bi bi-house-door me-2"></i>Unit Details
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0" style="font-size:14px">
                        <tbody>
                            <tr>
                                <td class="px-4 py-2 text-muted fw-semibold" style="width:45%">Unit Number</td>
                                <td class="py-2 fw-bold">{{ $unit->unit_number }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-muted fw-semibold">Wing</td>
                                <td class="py-2">{{ $unit->wing?->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-muted fw-semibold">Unit Type</td>
                                <td class="py-2">{{ $unit->unitType?->name ?? ($unit->unit_type ?? '—') }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-muted fw-semibold">Floor</td>
                                <td class="py-2">{{ $unit->floor ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-muted fw-semibold">Area</td>
                                <td class="py-2">
                                    {{ $unit->area_sqft ? number_format($unit->area_sqft, 2) . ' sq.ft.' : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-muted fw-semibold">Status</td>
                                <td class="py-2">
                                    @php
                                        $badge = match($unit->status) {
                                            'active'   => 'bg-success',
                                            'inactive' => 'bg-warning text-dark',
                                            default    => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }} rounded-pill px-3">
                                        {{ ucfirst($unit->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-muted fw-semibold">Registered In</td>
                                <td class="py-2">{{ $unit->registered_in_name_of ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-muted fw-semibold">Contact</td>
                                <td class="py-2">{{ $unit->contact_number ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-muted fw-semibold">Opening Balance</td>
                                <td class="py-2">₹ {{ number_format($unit->opening_balance, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Members column --}}
        <div class="col-lg-7 d-flex flex-column gap-4">

            {{-- Current Members --}}
            <div class="card shadow-sm">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people me-2"></i>Members</span>
                    <span class="badge bg-secondary rounded-pill">{{ $unit->members->count() }}</span>
                </div>

                @if ($unit->members->isEmpty())
                    <div class="card-body text-center text-muted py-4 small">
                        <i class="bi bi-person-x d-block mb-2" style="font-size:1.5rem"></i>
                        No members linked yet
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:13px">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="py-3">Type</th>
                                    <th class="py-3">Start Date</th>
                                    <th class="py-3">End Date</th>
                                    <th class="py-3 text-center">Primary</th>
                                    <th class="py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($unit->members as $member)
                                    <tr>
                                        <td class="px-4 py-2 fw-semibold">
                                            {{ $member->user?->name ?? '—' }}
                                            @if ($member->user?->mobile)
                                                <div class="text-muted small">{{ $member->user->mobile }}</div>
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            <span class="badge bg-info bg-opacity-10 text-info px-2 py-1">
                                                {{ ucfirst($member->member_type) }}
                                            </span>
                                        </td>
                                        <td class="py-2 text-muted small">
                                            {{ $member->start_date?->format('d M Y') ?? '—' }}
                                        </td>
                                        <td class="py-2 text-muted small">
                                            {{ $member->end_date?->format('d M Y') ?? '—' }}
                                        </td>
                                        <td class="py-2 text-center">
                                            @if ($member->is_primary)
                                                <i class="bi bi-star-fill text-warning" title="Primary"></i>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="py-2 text-end pe-3">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        title="Edit"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editMemberModal"
                                                        data-member-id="{{ $member->id }}"
                                                        data-member-name="{{ $member->user?->name }}"
                                                        data-member-type="{{ $member->member_type }}"
                                                        data-start-date="{{ $member->start_date?->format('Y-m-d') }}"
                                                        data-end-date="{{ $member->end_date?->format('Y-m-d') }}"
                                                        data-is-primary="{{ $member->is_primary ? '1' : '0' }}"
                                                        data-action="{{ route('unit.member.update', [$unit, $member]) }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('unit.member.remove', [$unit, $member]) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Remove {{ $member->user?->name }} from this unit?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                        <i class="bi bi-person-dash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Edit Member Modal --}}
                    <div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="editMemberForm" method="POST">
                                    @csrf @method('PATCH')
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-semibold" id="editMemberModalLabel">
                                            <i class="bi bi-pencil me-2"></i>Edit Member
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p class="text-muted small mb-3" id="editMemberName"></p>

                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Member Type</label>
                                                <select name="member_type" id="editMemberType" class="form-select form-select-sm">
                                                    @foreach (['owner' => 'Owner', 'tenant' => 'Tenant', 'family' => 'Family', 'other' => 'Other'] as $val => $label)
                                                        <option value="{{ $val }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label fw-semibold">Start Date</label>
                                                <input type="date" name="start_date" id="editStartDate" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label fw-semibold">End Date <span class="text-muted fw-normal">(optional)</span></label>
                                                <input type="date" name="end_date" id="editEndDate" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input type="hidden" name="is_primary" value="0">
                                                    <input class="form-check-input" type="checkbox" id="editIsPrimary" name="is_primary" value="1">
                                                    <label class="form-check-label fw-semibold" for="editIsPrimary">
                                                        Set as primary member
                                                    </label>
                                                    <div class="form-text">Marking primary will unset any existing primary member.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-check-lg me-1"></i>Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Add Member Form --}}
            <div class="card shadow-sm">
                <div class="card-header py-3 fw-semibold">
                    <i class="bi bi-person-plus me-2"></i>Add Member
                </div>
                <div class="card-body p-4">

                    @if ($availableUsers->isEmpty())
                        <p class="text-muted small mb-0">All society members are already linked to this unit.</p>
                    @else
                        <form action="{{ route('unit.member.add', $unit) }}" method="POST">
                            @csrf

                            <div class="row g-3">

                                {{-- User --}}
                                <div class="col-sm-6">
                                    <label for="user_id" class="form-label fw-semibold">
                                        Member <span class="text-danger">*</span>
                                    </label>
                                    <select id="user_id" name="user_id"
                                            class="form-select form-select-sm @error('user_id') is-invalid @enderror">
                                        <option value="">— Select member —</option>
                                        @foreach ($availableUsers as $u)
                                            <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                                {{ $u->name }}@if($u->mobile) · {{ $u->mobile }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Member Type --}}
                                <div class="col-sm-6">
                                    <label for="member_type" class="form-label fw-semibold">
                                        Type <span class="text-danger">*</span>
                                    </label>
                                    <select id="member_type" name="member_type"
                                            class="form-select form-select-sm @error('member_type') is-invalid @enderror">
                                        @foreach (['owner' => 'Owner', 'tenant' => 'Tenant', 'family' => 'Family', 'other' => 'Other'] as $val => $label)
                                            <option value="{{ $val }}" {{ old('member_type', 'owner') === $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('member_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Start Date --}}
                                <div class="col-sm-6">
                                    <label for="start_date" class="form-label fw-semibold">Start Date</label>
                                    <input type="date" id="start_date" name="start_date"
                                           value="{{ old('start_date', now()->toDateString()) }}"
                                           class="form-control form-control-sm @error('start_date') is-invalid @enderror">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- End Date --}}
                                <div class="col-sm-6">
                                    <label for="end_date" class="form-label fw-semibold">End Date <span class="text-muted fw-normal">(optional)</span></label>
                                    <input type="date" id="end_date" name="end_date"
                                           value="{{ old('end_date') }}"
                                           class="form-control form-control-sm @error('end_date') is-invalid @enderror">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Primary --}}
                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="hidden" name="is_primary" value="0">
                                        <input class="form-check-input" type="checkbox" id="is_primary"
                                               name="is_primary" value="1"
                                               {{ old('is_primary') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_primary">
                                            Set as primary member
                                        </label>
                                        <div class="form-text">Marking primary will unset any existing primary member.</div>
                                    </div>
                                </div>

                            </div>

                            <div class="d-flex gap-2 pt-3 mt-2 border-top">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-person-plus me-1"></i>Add Member
                                </button>
                            </div>
                        </form>
                    @endif

                </div>
            </div>

        </div>{{-- /members column --}}
    </div>

@endsection

@push('scripts')
<script>
document.getElementById('editMemberModal').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    document.getElementById('editMemberForm').action      = btn.dataset.action;
    document.getElementById('editMemberName').textContent = btn.dataset.memberName;
    document.getElementById('editMemberType').value       = btn.dataset.memberType;
    document.getElementById('editStartDate').value        = btn.dataset.startDate || '';
    document.getElementById('editEndDate').value          = btn.dataset.endDate || '';
    document.getElementById('editIsPrimary').checked      = btn.dataset.isPrimary === '1';
});
</script>
@endpush
