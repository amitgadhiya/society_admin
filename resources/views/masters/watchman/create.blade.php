@extends('layouts.app')

@section('title', 'Add Watchman — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('watchman.index') }}">Watchmen</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Watchman</li>
@endsection

@section('content')

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('watchman.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold text-dark mb-0">Add Watchman</h4>
            <p class="text-muted small mb-0">Create a new security watchman account</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('watchman.store') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                   placeholder="e.g. Ramesh Kumar"
                                   class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mobile --}}
                        <div class="mb-3">
                            <label for="mobile" class="form-label fw-semibold">Mobile Number</label>
                            <input type="text" id="mobile" name="mobile" value="{{ old('mobile') }}"
                                   placeholder="e.g. 9876543210"
                                   class="form-control @error('mobile') is-invalid @enderror">
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Employee ID --}}
                        <div class="mb-3">
                            <label for="employee_id" class="form-label fw-semibold">Employee ID</label>
                            <input type="text" id="employee_id" name="employee_id" value="{{ old('employee_id') }}"
                                   placeholder="e.g. WM-001"
                                   class="form-control @error('employee_id') is-invalid @enderror">
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                App Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" id="password" name="password"
                                   placeholder="Minimum 6 characters"
                                   class="form-control @error('password') is-invalid @enderror">
                            <div class="form-text">Used to log in to the Watchman mobile app.</div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Photo --}}
                        <div class="mb-3">
                            <label for="photo" class="form-label fw-semibold">Photo</label>
                            <input type="file" id="photo" name="photo" accept="image/*"
                                   class="form-control @error('photo') is-invalid @enderror">
                            <div class="form-text">JPG, PNG or WebP. Max 2 MB.</div>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Active --}}
                        <div class="mb-4">
                            <input type="hidden" name="active" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="active"
                                       name="active" value="1"
                                       {{ old('active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="active">
                                    Mark as Active
                                </label>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex gap-2 pt-3 border-top">
                            <button type="submit" class="btn btn-success flex-fill">
                                <i class="bi bi-check-lg me-1"></i>Add Watchman
                            </button>
                            <a href="{{ route('watchman.index') }}" class="btn btn-outline-secondary flex-fill">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
