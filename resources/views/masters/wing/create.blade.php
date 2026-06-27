@extends('layouts.app')

@section('title', 'Add Wing — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('wing.index') }}">Wings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('wing.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold text-dark mb-0">Add Wing</h4>
            <p class="text-muted small mb-0">Create a new building wing</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('wing.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name') }}"
                                   placeholder="e.g. A Wing, B Wing…"
                                   class="form-control @error('name') is-invalid @enderror"
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2 pt-3 border-top">
                            <button type="submit" class="btn btn-success flex-fill">
                                <i class="bi bi-check-lg me-1"></i>Save
                            </button>
                            <a href="{{ route('wing.index') }}" class="btn btn-outline-secondary flex-fill">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
