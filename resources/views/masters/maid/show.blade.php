@extends('layouts.app')

@section('title', $maid->name . ' — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('maid.index') }}">Maids</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $maid->name }}</li>
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
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('maid.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold text-dark mb-0">{{ $maid->name }}</h4>
                <p class="text-muted small mb-0">Maid details &amp; unit assignments</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('maid.edit', $maid) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form action="{{ route('maid.destroy', $maid) }}" method="POST"
                  onsubmit="return confirm('Delete {{ $maid->name }}? This cannot be undone.')">
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

    {{-- Profile + Details row --}}
    <div class="row g-4 mb-4">

        {{-- Profile card --}}
        <div class="col-lg-3">
            <div class="card shadow-sm text-center p-4">
                @if ($maid->photo)
                    <img src="{{ Storage::url($maid->photo) }}" alt="{{ $maid->name }}"
                         class="rounded-circle object-fit-cover border border-3 mx-auto mb-3"
                         style="width:90px;height:90px">
                @else
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-indigo text-white fw-bold"
                         style="width:90px;height:90px;font-size:2.5rem">
                        {{ strtoupper(substr($maid->name, 0, 1)) }}
                    </div>
                @endif

                <h5 class="fw-bold mb-1">{{ $maid->name }}</h5>

                @if ($maid->status === 'active')
                    <span class="badge bg-success rounded-pill px-3 py-2">Active</span>
                @else
                    <span class="badge bg-danger rounded-pill px-3 py-2">Inactive</span>
                @endif
            </div>
        </div>

        {{-- Details card --}}
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <i class="bi bi-info-circle me-2"></i>Maid Information
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Full Name</p>
                            <p class="fw-semibold mb-0">{{ $maid->name }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Mobile</p>
                            <p class="fw-semibold mb-0">{{ $maid->mobile ?? '—' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Aadhaar Number</p>
                            <p class="fw-semibold mb-0">
                                @if($maid->aadhaar_number)
                                    XXXX-XXXX-{{ substr($maid->aadhaar_number, -4) }}
                                @else
                                    —
                                @endif
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Address</p>
                            <p class="fw-semibold mb-0">{{ $maid->address ?? '—' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small fw-semibold mb-1">Registered On</p>
                            <p class="fw-semibold mb-0">{{ optional($maid->created_at)->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Unit Assignments --}}
    <div class="card shadow-sm">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-house-door me-2"></i>Unit Assignments</span>
            <span class="badge bg-secondary rounded-pill">{{ $maid->unitAssignments->count() }}</span>
        </div>

        {{-- Assign form --}}
        <div class="card-body border-bottom pb-4">
            <p class="fw-semibold small text-success mb-3">
                <i class="bi bi-plus-circle me-1"></i>New Assignment
            </p>
            @if ($availableUnits->isEmpty())
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    All society units are already assigned to this maid.
                </p>
            @else
                <form id="assignForm" action="{{ route('maid.unit.assign', $maid) }}" method="POST"
                      class="d-flex flex-wrap gap-3 align-items-start" novalidate>
                    @csrf

                    <div>
                        <label class="form-label fw-semibold small mb-1">
                            Unit <span class="text-danger">*</span>
                        </label>
                        <select id="assign_unit_id" name="assign_unit_id"
                                class="form-select form-select-sm @error('assign_unit_id') is-invalid @enderror"
                                style="min-width:220px" required>
                            <option value="">— Select unit —</option>
                            @foreach ($availableUnits as $u)
                                <option value="{{ $u->id }}" {{ old('assign_unit_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->unit_number }}
                                    @if ($u->wing) — {{ $u->wing->name }} @endif
                                    @if ($u->registered_in_name_of) ({{ $u->registered_in_name_of }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="assign_unit_error">
                            @error('assign_unit_id') {{ $message }} @else Please select a unit. @enderror
                        </div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold small mb-1">
                            Type <span class="text-danger">*</span>
                        </label>
                        <select id="assign_type" name="assign_type"
                                class="form-select form-select-sm @error('assign_type') is-invalid @enderror"
                                style="min-width:160px" required>
                            <option value="">— Select type —</option>
                            @foreach ($typeLabels as $val => $label)
                                <option value="{{ $val }}" {{ old('assign_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="assign_type_error">
                            @error('assign_type') {{ $message }} @else Please select a type. @enderror
                        </div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold small mb-1">
                            Start Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="assign_start_date" name="assign_start_date"
                               value="{{ old('assign_start_date', date('Y-m-d')) }}"
                               class="form-control form-control-sm @error('assign_start_date') is-invalid @enderror"
                               required>
                        <div class="invalid-feedback" id="assign_start_date_error">
                            @error('assign_start_date') {{ $message }} @else Start date is required. @enderror
                        </div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold small mb-1">
                            Start Time <span class="text-danger">*</span>
                        </label>
                        <input type="time" id="assign_start_time" name="assign_start_time"
                               value="{{ old('assign_start_time') }}"
                               class="form-control form-control-sm @error('assign_start_time') is-invalid @enderror"
                               required>
                        <div class="invalid-feedback" id="assign_start_time_error">
                            @error('assign_start_time') {{ $message }} @else Start time is required. @enderror
                        </div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold small mb-1">
                            End Time <span class="text-danger">*</span>
                        </label>
                        <input type="time" id="assign_end_time" name="assign_end_time"
                               value="{{ old('assign_end_time') }}"
                               class="form-control form-control-sm @error('assign_end_time') is-invalid @enderror"
                               required>
                        <div class="invalid-feedback" id="assign_end_time_error">
                            @error('assign_end_time') {{ $message }} @else End time must be after start time. @enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-end" style="padding-bottom:1px">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>Add Assignment
                        </button>
                    </div>
                </form>
            @endif
        </div>

        {{-- Assignment list --}}
        @if ($maid->unitAssignments->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-house-x d-block mb-2" style="font-size:2rem"></i>
                <p class="small mb-0">No units assigned yet</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Unit</th>
                            <th class="py-3">Wing</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Start Date</th>
                            <th class="py-3">End Date</th>
                            <th class="py-3">Start Time</th>
                            <th class="py-3">End Time</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($maid->unitAssignments as $a)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="fw-semibold mb-0">{{ $a->unit->unit_number }}</p>
                                    @if ($a->unit->registered_in_name_of)
                                        <small class="text-muted">{{ $a->unit->registered_in_name_of }}</small>
                                    @endif
                                </td>
                                <td class="py-3 text-muted">
                                    {{ optional($a->unit->wing)->name ?? '—' }}
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-info text-dark rounded-pill px-3">
                                        {{ $typeLabels[$a->type] ?? ucfirst($a->type) }}
                                    </span>
                                </td>
                                <td class="py-3 text-muted">
                                    {{ optional($a->start_date)->format('d M Y') }}
                                </td>
                                <td class="py-3 text-muted">
                                    {{ $a->end_date ? optional($a->end_date)->format('d M Y') : '—' }}
                                </td>
                                <td class="py-3 text-muted">
                                    {{ $a->start_time ? \Carbon\Carbon::parse($a->start_time)->format('h:i A') : '—' }}
                                </td>
                                <td class="py-3 text-muted">
                                    {{ $a->end_time ? \Carbon\Carbon::parse($a->end_time)->format('h:i A') : '—' }}
                                </td>
                                <td class="py-3">
                                    @if ($a->status === 'active')
                                        <span class="badge bg-success rounded-pill px-3">Active</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                title="Edit assignment"
                                                data-bs-toggle="modal" data-bs-target="#editDatesModal"
                                                data-action="{{ route('maid.unit.update', $a) }}"
                                                data-type="{{ $a->type }}"
                                                data-start="{{ optional($a->start_date)->format('Y-m-d') }}"
                                                data-end="{{ optional($a->end_date)->format('Y-m-d') }}"
                                                data-start-time="{{ $a->start_time }}"
                                                data-end-time="{{ $a->end_time }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="{{ route('maid.unit.toggle', $a) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-sm {{ $a->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ $a->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                <i class="bi {{ $a->status === 'active' ? 'bi-pause-fill' : 'bi-play-fill' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('maid.unit.remove', $a) }}" method="POST"
                                              onsubmit="return confirm('Remove assignment for unit {{ $a->unit->unit_number }}?')">
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

{{-- Edit Assignment Modal --}}
<div class="modal fade" id="editDatesModal" tabindex="-1" aria-labelledby="editDatesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editDatesForm" method="POST" novalidate>
                @csrf @method('PATCH')
                <div class="modal-header bg-warning-subtle">
                    <h6 class="modal-title fw-bold" id="editDatesModalLabel">
                        <i class="bi bi-pencil-square me-2 text-warning"></i>Edit Assignment
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_type" class="form-label fw-semibold small">
                            Type <span class="text-danger">*</span>
                        </label>
                        <select id="modal_type" name="type" class="form-select form-select-sm" required>
                            @foreach ($typeLabels as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a type.</div>
                    </div>
                    <div class="mb-3">
                        <label for="modal_start_date" class="form-label fw-semibold small">
                            Start Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="modal_start_date" name="start_date"
                               class="form-control form-control-sm" required>
                        <div class="invalid-feedback">Start date is required.</div>
                    </div>
                    <div class="mb-3">
                        <label for="modal_end_date" class="form-label fw-semibold small">End Date</label>
                        <input type="date" id="modal_end_date" name="end_date"
                               class="form-control form-control-sm">
                        <div class="invalid-feedback" id="modal_end_date_error"></div>
                        <div class="form-text">Leave blank if still active.</div>
                    </div>
                    <div class="mb-3">
                        <label for="modal_start_time" class="form-label fw-semibold small">
                            Start Time <span class="text-danger">*</span>
                        </label>
                        <input type="time" id="modal_start_time" name="start_time"
                               class="form-control form-control-sm" required>
                        <div class="invalid-feedback">Start time is required.</div>
                    </div>
                    <div class="mb-1">
                        <label for="modal_end_time" class="form-label fw-semibold small">
                            End Time <span class="text-danger">*</span>
                        </label>
                        <input type="time" id="modal_end_time" name="end_time"
                               class="form-control form-control-sm" required>
                        <div class="invalid-feedback" id="modal_end_time_error">End time is required.</div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil-square me-1"></i>Update Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    /* ── helpers ── */
    function setInvalid(el, msg, errorElId) {
        el.classList.add('is-invalid');
        el.classList.remove('is-valid');
        if (errorElId) document.getElementById(errorElId).textContent = msg;
        return false;
    }
    function setValid(el) {
        el.classList.remove('is-invalid');
    }

    /* ══════════════════════════════════════════
       INSERT — Assign form validation
    ══════════════════════════════════════════ */
    const assignForm      = document.getElementById('assignForm');

    if (assignForm) {
        assignForm.addEventListener('submit', function (e) {
            e.preventDefault();
            let valid = true;

            const unitId     = document.getElementById('assign_unit_id');
            const type       = document.getElementById('assign_type');
            const startDate  = document.getElementById('assign_start_date');
            const startTime  = document.getElementById('assign_start_time');
            const endTime    = document.getElementById('assign_end_time');

            // Unit
            if (!unitId.value) {
                setInvalid(unitId, 'Please select a unit.', 'assign_unit_error');
                valid = false;
            } else { setValid(unitId); }

            // Type
            if (!type.value) {
                setInvalid(type, 'Please select a type.', 'assign_type_error');
                valid = false;
            } else { setValid(type); }

            // Start Date
            if (!startDate.value) {
                setInvalid(startDate, 'Start date is required.', 'assign_start_date_error');
                valid = false;
            } else { setValid(startDate); }

            // Start Time
            if (!startTime.value) {
                setInvalid(startTime, 'Start time is required.', 'assign_start_time_error');
                valid = false;
            } else { setValid(startTime); }

            // End Time — required, must be after start time
            if (!endTime.value) {
                setInvalid(endTime, 'End time is required.', 'assign_end_time_error');
                valid = false;
            } else if (startTime.value && endTime.value <= startTime.value) {
                setInvalid(endTime, 'End time must be after start time.', 'assign_end_time_error');
                valid = false;
            } else { setValid(endTime); }

            if (valid) assignForm.submit();
        });
    }

    /* ══════════════════════════════════════════
       UPDATE — Edit modal validation
    ══════════════════════════════════════════ */
    const editModal      = document.getElementById('editDatesModal');
    const editForm       = document.getElementById('editDatesForm');
    const modalType      = document.getElementById('modal_type');
    const modalStartDate = document.getElementById('modal_start_date');
    const modalEndDate   = document.getElementById('modal_end_date');
    const modalStartTime = document.getElementById('modal_start_time');
    const modalEndTime   = document.getElementById('modal_end_time');

    editModal.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        editForm.action          = btn.dataset.action;
        modalType.value          = btn.dataset.type      || '';
        modalStartDate.value     = btn.dataset.start     || '';
        modalEndDate.value       = btn.dataset.end       || '';
        modalStartTime.value     = (btn.dataset.startTime || '').substring(0, 5);
        modalEndTime.value       = (btn.dataset.endTime   || '').substring(0, 5);

        // Clear previous validation state
        [modalType, modalStartDate, modalEndDate, modalStartTime, modalEndTime].forEach(function (el) {
            el.classList.remove('is-invalid', 'is-valid');
        });
        document.getElementById('modal_end_date_error').textContent = '';
        document.getElementById('modal_end_time_error').textContent = 'End time is required.';
    });

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();
        let valid = true;

        // Type
        if (!modalType.value) {
            setInvalid(modalType, 'Please select a type.', null);
            modalType.classList.add('is-invalid');
            valid = false;
        } else { setValid(modalType); }

        // Start Date
        if (!modalStartDate.value) {
            setInvalid(modalStartDate, null, null);
            valid = false;
        } else { setValid(modalStartDate); }

        // End Date — optional but must be >= start date if provided
        if (modalEndDate.value && modalStartDate.value && modalEndDate.value < modalStartDate.value) {
            document.getElementById('modal_end_date_error').textContent = 'End date must be on or after start date.';
            modalEndDate.classList.add('is-invalid');
            valid = false;
        } else {
            setValid(modalEndDate);
            document.getElementById('modal_end_date_error').textContent = '';
        }

        // Start Time
        if (!modalStartTime.value) {
            modalStartTime.classList.add('is-invalid');
            valid = false;
        } else { setValid(modalStartTime); }

        // End Time — required, must be after start time
        const endTimeError = document.getElementById('modal_end_time_error');
        if (!modalEndTime.value) {
            modalEndTime.classList.add('is-invalid');
            endTimeError.textContent = 'End time is required.';
            valid = false;
        } else if (modalStartTime.value && modalEndTime.value <= modalStartTime.value) {
            modalEndTime.classList.add('is-invalid');
            endTimeError.textContent = 'End time must be after start time.';
            valid = false;
        } else { setValid(modalEndTime); }

        if (valid) editForm.submit();
    });
</script>
@endpush

@endsection
