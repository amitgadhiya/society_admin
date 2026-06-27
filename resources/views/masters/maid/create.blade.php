@extends('layouts.app')

@section('title', 'Add Maid — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('maid.index') }}">Maids</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Maid</li>
@endsection

@section('content')

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('maid.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold text-dark mb-0">Add Maid</h4>
            <p class="text-muted small mb-0">Register a new household staff member</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('maid.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                   placeholder="e.g. Sunita Devi"
                                   class="form-control @error('name') is-invalid @enderror">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mobile" class="form-label fw-semibold">Mobile Number</label>
                            <input type="text" id="mobile" name="mobile" value="{{ old('mobile') }}"
                                   placeholder="e.g. 9876543210"
                                   class="form-control @error('mobile') is-invalid @enderror">
                            @error('mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="aadhaar_number" class="form-label fw-semibold">Aadhaar Number</label>
                            <input type="text" id="aadhaar_number" name="aadhaar_number"
                                   value="{{ old('aadhaar_number') }}"
                                   placeholder="12-digit Aadhaar number"
                                   class="form-control @error('aadhaar_number') is-invalid @enderror">
                            @error('aadhaar_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Address</label>
                            <textarea id="address" name="address" rows="2"
                                      placeholder="Home address"
                                      class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label fw-semibold">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select id="status" name="status"
                                    class="form-select @error('status') is-invalid @enderror">
                                <option value="active"   {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="photo" class="form-label fw-semibold">Photo</label>
                            <input type="file" id="photo" name="photo" accept="image/*"
                                   class="form-control @error('photo') is-invalid @enderror">
                            <div class="form-text">JPG, PNG or WebP. Max 2 MB.</div>
                            @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex gap-2 pt-3 border-top">
                            <button type="submit" class="btn btn-success flex-fill">
                                <i class="bi bi-check-lg me-1"></i>Add Maid
                            </button>
                            <a href="{{ route('maid.index') }}" class="btn btn-outline-secondary flex-fill">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
