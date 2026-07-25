<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'StockManager')</title>
    <link rel="stylesheet" href="{{ asset('build/app.css') }}">
    <script src="{{ asset('build/app.js') }}" defer></script>
</head>
@php($hideNavigation = $__env->hasSection('hideNavigation'))
<body class="@yield('bodyClass')">
@unless($hideNavigation)
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="{{ auth()->check() ? route('dashboard') : route('home') }}">StockManager</a>
        @auth
            <nav aria-label="Primary navigation">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('products.index') }}">Products</a>
                <a href="{{ route('suppliers.index') }}">Suppliers</a>
                <a href="{{ route('stock-movements.index') }}">Stock movements</a>
                <a href="{{ route('reports.index') }}">Reports</a>
            </nav>

            <details class="user-menu">
                <summary>
                    <span class="user-menu__name">{{ auth()->user()->name }}</span>
                    <svg aria-hidden="true" viewBox="0 0 20 20" width="16" height="16">
                        <path d="m5 7.5 5 5 5-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                    </svg>
                </summary>
                <div class="user-menu__panel">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="user-menu__action" type="submit">
                            <svg aria-hidden="true" viewBox="0 0 24 24" width="18" height="18">
                                <path d="M10 5H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h5M14 8l4 4-4 4M18 12H9" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                            </svg>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </details>
        @endauth
    </div>
</header>
@endunless
<main class="container page{{ $hideNavigation ? ' page--auth' : '' }}">
    @if(session('success'))
        <div class="alert success" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert error" role="alert">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('content')
</main>
<footer class="container footer">StockManager Developed by Muhammad Ali Nawaz</footer>
</body>
</html>
