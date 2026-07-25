@extends('layouts.app')

@section('title', $product->name.' | StockManager')

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $product->name }}</h1>
            <p class="muted">SKU: {{ $product->sku ?: 'Not assigned' }}</p>
        </div>

        <div class="actions">
            <a
                class="button"
                href="{{ route('stock-movements.create', ['product_id' => $product->id]) }}"
            >
                Record movement
            </a>

            @role('admin')
                <a class="button secondary" href="{{ route('products.edit', $product) }}">
                    Edit
                </a>

                @if ($product->quantity === 0)
                    <form
                        method="POST"
                        action="{{ route('products.destroy', $product) }}"
                        onsubmit="return confirm('Archive this product?')"
                    >
                        @csrf
                        @method('DELETE')

                        <button class="danger" type="submit">Archive</button>
                    </form>
                @else
                    <span
                        class="muted"
                        title="Record stock out or a compensating adjustment first."
                    >
                        Stock must be zero before archiving.
                    </span>
                @endif
            @endrole
        </div>
    </div>

    <div class="detail-grid">
        <article class="panel">
            <h2>Inventory</h2>

            <dl>
                <dt>Quantity</dt>
                <dd>{{ $product->quantity }}</dd>

                <dt>Minimum quantity</dt>
                <dd>{{ $product->minimum_quantity }}</dd>

                <dt>Price</dt>
                <dd>{{ number_format((float) $product->price, 2) }}</dd>

                <dt>Category</dt>
                <dd>{{ $product->category ?: '—' }}</dd>

                <dt>Supplier</dt>
                <dd>{{ $product->supplier?->name ?: '—' }}</dd>
            </dl>

            <h3>Description</h3>
            <p>{{ $product->description ?: 'No description.' }}</p>
        </article>

        <article class="panel">
            <h2>Movement history</h2>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $movement)
                            <tr>
                                <td>
                                    <a href="{{ route('stock-movements.show', $movement) }}">
                                        {{ $movement->created_at->format('Y-m-d H:i') }}
                                    </a>
                                </td>
                                <td>{{ str_replace('_', ' ', $movement->type) }}</td>
                                <td>{{ $movement->quantity }}</td>
                                <td>
                                    {{ $movement->quantity_before }} → {{ $movement->quantity_after }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No movements.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $movements->links() }}
        </article>
    </div>
@endsection
