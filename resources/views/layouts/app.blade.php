<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no"
    >

    <title>@yield('title', 'Dashboard') &mdash; Warehouse Management</title>

    {{-- CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- General CSS --}}
    <link rel="stylesheet"
          href="{{ asset('backend/asset/library/bootstrap/dist/css/bootstrap.min.css') }}">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          crossorigin="anonymous" />

    {{-- Page Specific CSS --}}
    @stack('style')

    {{-- Template CSS --}}
    <link rel="stylesheet"
          href="{{ asset('backend/asset/css/style.css') }}">
    <link rel="stylesheet"
          href="{{ asset('backend/asset/css/components.css') }}">
</head>

<body>

<div id="app">
    <div class="main-wrapper">

        {{-- NAVBAR --}}
        @include('components.header')

        {{-- SIDEBAR --}}
        @include('components.sidebar')

        {{-- MAIN CONTENT --}}
        <div class="main-content">
            <section class="section">
                @yield('content')
            </section>
        </div>

        {{-- FOOTER --}}
        @include('components.footer')

    </div>
</div>

{{-- General JS --}}
<script src="{{ asset('backend/asset/library/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('backend/asset/library/popper.js/dist/umd/popper.min.js') }}"></script>
<script src="{{ asset('backend/asset/library/tooltip.js/dist/umd/tooltip.min.js') }}"></script>
<script src="{{ asset('backend/asset/library/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/asset/library/jquery.nicescroll/dist/jquery.nicescroll.min.js') }}"></script>
<script src="{{ asset('backend/asset/library/moment/min/moment.min.js') }}"></script>

{{-- Stisla Core --}}
<script src="{{ asset('backend/asset/js/stisla.js') }}"></script>

{{-- Page Specific JS --}}
@stack('scripts')

{{-- Template JS --}}
<script src="{{ asset('backend/asset/js/scripts.js') }}"></script>
<script src="{{ asset('backend/asset/js/custom.js') }}"></script>

</body>
</html>
