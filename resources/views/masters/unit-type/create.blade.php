@extends('layouts.app')

@section('title', 'Add Unit Type — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('unit-type.index') }}">Unit Types</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('unit-type.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold text-dark mb-0">Add Unit Type</h4>
            <p class="text-muted small mb-0">Create a new unit type category</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('unit-type.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">

                            {{-- Name --}}
                            <div class="col-12">
                                <label for="name" class="form-label fw-semibold">
                                    Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="name" name="name"
                                       value="{{ old('name') }}"
                                       placeholder="e.g. 1 BHK, 2 BHK, Shop…"
                                       class="form-control @error('name') is-invalid @enderror"
                                       autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-12">
                                <label for="status" class="form-label fw-semibold">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select id="status" name="status"
                                        class="form-select @error('status') is-invalid @enderror">
                                    <option value="active"   {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="d-flex gap-2 pt-4 mt-2 border-top">
                            <button type="submit" class="btn btn-success flex-fill">
                                <i class="bi bi-check-lg me-1"></i>Save
                            </button>
                            <a href="{{ route('unit-type.index') }}" class="btn btn-outline-secondary flex-fill">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
