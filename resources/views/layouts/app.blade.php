<!DOCTYPE html>
<html lang="en">
@include('layouts.head')
<body class="d-flex flex-column min-vh-100 bg-light">

    @include('layouts.header')
    @include('layouts.breadcum')

    <main class="flex-grow-1 py-4">
        <div class="container-fluid px-4">
            @yield('content')
        </div>
    </main>

    @include('layouts.footer')

    <!-- Bootstrap 5 JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
