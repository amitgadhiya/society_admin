<div class="border-bottom bg-white py-2 px-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0" style="font-size:13px">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}" class="text-decoration-none">
                    <i class="bi bi-house me-1"></i>Home
                </a>
            </li>
            @yield('breadcrumb')
        </ol>
    </nav>
</div>
