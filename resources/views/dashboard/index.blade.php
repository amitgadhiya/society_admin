@extends('layouts.app')

@section('title', 'Dashboard — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')

    {{-- Page heading --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">Dashboard</h1>
            <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name }}. Here's what's happening today.</p>
        </div>
        <span class="badge bg-secondary fs-6 px-3 py-2">{{ now()->format('D, d M Y') }}</span>
    </div>

    {{-- Welcome banner --}}
    <div class="card border-0 bg-primary text-white mb-4 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center py-4">
            <div>
                <h4 class="card-title mb-1 fw-semibold">
                    Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ Auth::user()->name }}!
                </h4>
                <p class="card-text mb-0 opacity-75">You are signed in to the Society Management System.</p>
            </div>
            <span class="badge bg-white text-primary fs-6 px-3 py-2 d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ ucfirst(Auth::user()->role) }}
            </span>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="28" height="28">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="h4 mb-0 fw-bold">—</div>
                        <div class="small text-muted">Total Units</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="28" height="28">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="h4 mb-0 fw-bold">—</div>
                        <div class="small text-muted">Total Members</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="28" height="28">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="h4 mb-0 fw-bold">—</div>
                        <div class="small text-muted">Payments This Month</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="28" height="28">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="h4 mb-0 fw-bold">—</div>
                        <div class="small text-muted">Open Complaints</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panels row 1 --}}
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom py-3">
                    <span class="fw-semibold text-dark">Recent Payments</span>
                    <a href="#" class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center text-muted py-5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40" class="mb-2 opacity-50">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <p class="mb-0 small">No recent payments</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom py-3">
                    <span class="fw-semibold text-dark">Open Complaints</span>
                    <a href="#" class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center text-muted py-5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40" class="mb-2 opacity-50">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <p class="mb-0 small">No open complaints</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Panels row 2 --}}
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom py-3">
                    <span class="fw-semibold text-dark">Pending Tasks</span>
                    <a href="#" class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center text-muted py-5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40" class="mb-2 opacity-50">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <p class="mb-0 small">No pending tasks</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom py-3">
                    <span class="fw-semibold text-dark">Today's Visitors</span>
                    <a href="#" class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center text-muted py-5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40" class="mb-2 opacity-50">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <p class="mb-0 small">No visitors today</p>
                </div>
            </div>
        </div>
    </div>

@endsection
