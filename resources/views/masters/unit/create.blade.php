@extends('layouts.app')

@section('title', 'Add Unit — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('unit.index') }}">Units</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Unit</li>
@endsection

@section('content')

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('unit.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold text-dark mb-0">Add Unit</h4>
            <p class="text-muted small mb-0">Create a new unit in your society</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('unit.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">

                            {{-- Unit Number --}}
                            <div class="col-sm-6">
                                <label for="unit_number" class="form-label fw-semibold">
                                    Unit Number <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="unit_number" name="unit_number"
                                       value="{{ old('unit_number') }}"
                                       placeholder="e.g. A-101"
                                       class="form-control @error('unit_number') is-invalid @enderror">
                                @error('unit_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Floor --}}
                            <div class="col-sm-6">
                                <label for="floor" class="form-label fw-semibold">Floor</label>
                                <input type="number" id="floor" name="floor"
                                       value="{{ old('floor') }}"
                                       placeholder="e.g. 1"
                                       min="0"
                                       class="form-control @error('floor') is-invalid @enderror">
                                @error('floor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Wing --}}
                            <div class="col-sm-6">
                                <label for="wing_id" class="form-label fw-semibold">Wing</label>
                                <select id="wing_id" name="wing_id"
                                        class="form-select @error('wing_id') is-invalid @enderror">
                                    <option value="">— None —</option>
                                    @foreach ($wings as $wing)
                                        <option value="{{ $wing->id }}" {{ old('wing_id') == $wing->id ? 'selected' : '' }}>
                                            {{ $wing->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('wing_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Unit Type --}}
                            <div class="col-sm-6">
                                <label for="unit_type_id" class="form-label fw-semibold">Unit Type</label>
                                <select id="unit_type_id" name="unit_type_id"
                                        class="form-select @error('unit_type_id') is-invalid @enderror">
                                    <option value="">— None —</option>
                                    @foreach ($unitTypes as $ut)
                                        <option value="{{ $ut->id }}" {{ old('unit_type_id') == $ut->id ? 'selected' : '' }}>
                                            {{ $ut->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Area --}}
                            <div class="col-sm-6">
                                <label for="area_sqft" class="form-label fw-semibold">Area (sq.ft.)</label>
                                <input type="number" id="area_sqft" name="area_sqft"
                                       value="{{ old('area_sqft') }}"
                                       placeholder="e.g. 850"
                                       min="0" step="0.01"
                                       class="form-control @error('area_sqft') is-invalid @enderror">
                                @error('area_sqft')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-sm-6">
                                <label for="status" class="form-label fw-semibold">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select id="status" name="status"
                                        class="form-select @error('status') is-invalid @enderror">
                                    @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('status', 'active') === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Registered In Name Of --}}
                            <div class="col-sm-6">
                                <label for="registered_in_name_of" class="form-label fw-semibold">Registered In Name Of</label>
                                <input type="text" id="registered_in_name_of" name="registered_in_name_of"
                                       value="{{ old('registered_in_name_of') }}"
                                       placeholder="e.g. Ramesh Patel"
                                       class="form-control @error('registered_in_name_of') is-invalid @enderror">
                                @error('registered_in_name_of')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Contact Number --}}
                            <div class="col-sm-6">
                                <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                                <input type="text" id="contact_number" name="contact_number"
                                       value="{{ old('contact_number') }}"
                                       placeholder="e.g. 9876543210"
                                       class="form-control @error('contact_number') is-invalid @enderror">
                                @error('contact_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Opening Balance --}}
                            <div class="col-sm-6">
                                <label for="opening_balance" class="form-label fw-semibold">Opening Balance (₹)</label>
                                <input type="number" id="opening_balance" name="opening_balance"
                                       value="{{ old('opening_balance', 0) }}"
                                       placeholder="0.00"
                                       min="0" step="0.01"
                                       class="form-control @error('opening_balance') is-invalid @enderror">
                                @error('opening_balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="d-flex gap-2 pt-4 mt-2 border-top">
                            <button type="submit" class="btn btn-success flex-fill">
                                <i class="bi bi-check-lg me-1"></i>Add Unit
                            </button>
                            <a href="{{ route('unit.index') }}" class="btn btn-outline-secondary flex-fill">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
