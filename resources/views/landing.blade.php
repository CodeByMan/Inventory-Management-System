<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="StockManager provides secure product, supplier, stock movement, and inventory reporting workflows in one Laravel application.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>StockManager | Inventory Management</title>
    <link rel="stylesheet" href="{{ asset('build/app.css') }}">
    <script src="{{ asset('build/app.js') }}" defer></script>
</head>
<body class="landing-page">
<header class="landing-header">
    <div class="landing-shell landing-nav-wrap">
        <a class="landing-brand" href="{{ route('home') }}" aria-label="StockManager home">
            <span>Stock</span><strong>Manager</strong>
        </a>

        <button
            class="landing-menu-toggle"
            type="button"
            aria-expanded="false"
            aria-controls="landing-navigation"
            data-landing-menu-toggle
        >
            <span class="sr-only">Toggle navigation</span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>

        <div class="landing-navigation" id="landing-navigation" data-landing-menu>
            @auth
                <nav class="landing-nav-links" aria-label="Primary navigation">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <a href="{{ route('products.index') }}">Products</a>
                    <a href="{{ route('suppliers.index') }}">Suppliers</a>
                    <a href="{{ route('stock-movements.index') }}">Stock movements</a>
                    <a href="{{ route('reports.index') }}">Reports</a>
                </nav>
                <div class="landing-account">
                    <span>{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="landing-link-button" type="submit">Log out</button>
                    </form>
                </div>
            @else
                <nav class="landing-nav-links landing-nav-links--guest" aria-label="Primary navigation">
                    <a class="landing-login-link" href="{{ route('login') }}">Log in</a>
                </nav>
            @endauth
        </div>
    </div>
</header>

<main>
    <section class="landing-hero" aria-labelledby="landing-title">
        <div class="landing-shell landing-hero-grid">
            <div class="landing-hero-copy">
                <p class="landing-eyebrow">Secure inventory management</p>
                <h1 id="landing-title">Know what is in stock. <span>Move inventory with confidence.</span></h1>
                <p class="landing-intro">
                    Manage products, suppliers, stock movements, and reports in one clear workspace built around accurate inventory history.
                </p>

                <div class="landing-hero-actions">
                    @auth
                        <a class="landing-primary-button" href="{{ route('dashboard') }}">Open dashboard</a>
                    @else
                        <a class="landing-primary-button" href="{{ route('login') }}">Log in to StockManager</a>
                    @endauth
                </div>

                <ul class="landing-assurances" aria-label="StockManager strengths">
                    <li>Atomic stock updates</li>
                    <li>Admin and staff roles</li>
                    <li>Immutable movement history</li>
                </ul>
            </div>

            <div class="landing-visual" aria-label="Warehouse inventory illustration">
                <div class="landing-visual-frame">
                    <img
                        src="{{ asset('images/stockmanager-warehouse-hero.webp') }}"
                        alt="Warehouse worker recording inventory on a production line"
                        width="936"
                        height="829"
                        fetchpriority="high"
                    >
                </div>
                <div class="landing-visual-note landing-visual-note--top">Products and suppliers</div>
                <div class="landing-visual-note landing-visual-note--bottom">Movements and reports</div>
            </div>
        </div>
    </section>

    <section class="landing-capabilities" aria-labelledby="capabilities-title">
        <div class="landing-shell">
            <div class="landing-section-heading">
                <p class="landing-eyebrow">One connected workflow</p>
                <h2 id="capabilities-title">The existing StockManager tools, presented clearly.</h2>
            </div>

            <div class="landing-capability-grid">
                <article>
                    <span>01</span>
                    <h3>Products</h3>
                    <p>Maintain product details while protecting stock quantity from silent edits.</p>
                </article>
                <article>
                    <span>02</span>
                    <h3>Suppliers</h3>
                    <p>Keep supplier information connected to the inventory records that depend on it.</p>
                </article>
                <article>
                    <span>03</span>
                    <h3>Stock movements</h3>
                    <p>Record stock in, stock out, and compensating reversals through one atomic service.</p>
                </article>
                <article>
                    <span>04</span>
                    <h3>Reports</h3>
                    <p>Review low stock, product activity, movement history, Excel exports, and PDF summaries.</p>
                </article>
            </div>
        </div>
    </section>
</main>

<footer class="landing-footer">
    <div class="landing-shell">
        <a class="landing-brand landing-brand--footer" href="{{ route('home') }}">
            <span>Stock</span><strong>Manager</strong>
        </a>
        <p>StockManager Developed by Muhammad Ali Nawaz</p>
    </div>
</footer>
</body>
</html>
