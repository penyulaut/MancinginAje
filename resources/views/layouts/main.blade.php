<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>MancinginAje</title>

    <!-- Preload critical CSS -->
    <link rel="preload"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
          as="style">
    <link rel="preload"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          as="style">

    <!-- Bootstrap 5.0.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
          crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom Global CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <!-- jQuery (WAJIB untuk AJAX ongkir, load sebelum script halaman) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Inline layout styling -->
    <style>
        :root { --navbar-height: 72px; }
        .modern-navbar { height: var(--navbar-height); }
        body { padding-top: var(--navbar-height); }

        @media (max-width: 992px) {
            :root { --navbar-height: 90px; }
        }
    </style>

    {{-- CSS tambahan dari halaman --}}
    @stack('styles')
</head>

<body>
    {{-- Navbar --}}
    <x-navbar/>

    {{-- CSRF Error Toast --}}
    @if(session('csrf_error'))
        <x-toast type="warning" :message="session('csrf_error')" />
    @endif

    <main class="container-fluid">
        @yield('content')
    </main>

    <x-footer/>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"></script>

    {{-- JS tambahan dari halaman --}}
    @stack('scripts')
</body>
</html>