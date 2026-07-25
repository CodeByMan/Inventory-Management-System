@extends('layouts.app')
@section('title', 'Log in | StockManager')
@section('hideNavigation', 'true')
@section('bodyClass', 'auth-page')
@section('content')
<div class="auth-card">
    <h1>StockManager</h1>
    <p class="muted">Sign in to manage products, suppliers, and stock movements.</p>
    <form method="POST" action="{{ route('login.submit') }}" class="stack">
        @csrf
        <label>
            Email
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
        </label>
        <label>
            Password
            <span class="password-field">
                <input
                    id="login-password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    data-password-input
                >
                <button
                    class="password-toggle"
                    type="button"
                    aria-label="Show password"
                    aria-pressed="false"
                    aria-controls="login-password"
                    data-password-toggle
                >
                    <svg class="password-toggle__show" aria-hidden="true" viewBox="0 0 24 24" width="20" height="20">
                        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                        <circle cx="12" cy="12" r="2.75" fill="none" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                    <svg class="password-toggle__hide" aria-hidden="true" viewBox="0 0 24 24" width="20" height="20">
                        <path d="m3 3 18 18M10.7 6.1c.43-.07.86-.1 1.3-.1 6 0 9.5 6 9.5 6a17.7 17.7 0 0 1-2.1 2.75M6.6 6.65C3.95 8.35 2.5 12 2.5 12s3.5 6 9.5 6c1.5 0 2.82-.37 3.97-.93M9.88 9.88A3 3 0 0 0 14.12 14.12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                    </svg>
                    <span class="sr-only" data-password-toggle-text>Show password</span>
                </button>
            </span>
        </label>
        <div class="remember-option">
            <input id="remember" type="checkbox" name="remember" value="1" @checked(old('remember'))>
            <label for="remember">Remember me</label>
        </div>
        <button type="submit">Log in</button>
    </form>
</div>
@endsection
