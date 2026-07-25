@extends('layouts.app')
@section('title', 'Products | StockManager')
@section('content')
<div class="page-heading">
    <div>
        <h1>Products</h1>
        <p class="muted">Inventory quantities are changed only through stock movements.</p>
    </div>
    <div class="actions">
        @role('admin')
            <a class="button" href="{{ route('products.create') }}">Add product</a>
        @endrole
        <a class="button secondary" href="{{ route('products.export', request()->query()) }}">Export Excel</a>
    </div>
</div>
<form class="filters" method="GET">
    <input name="search" value="{{ request('search') }}" placeholder="Search name or SKU">
    <select name="category">
        <option value="">All categories</option>
        @foreach($categories as $category)
            <option @selected(request('category') === $category)>{{ $category }}</option>
        @endforeach
    </select>
    <button>Filter</button>
    <a href="{{ route('products.index') }}">Reset</a>
</form>
<div class="panel table-wrap">
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Category</th>
                <th>Supplier</th>
                <th>Quantity</th>
                <th>Minimum</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr @class(['low-row' => $product->quantity <= $product->minimum_quantity])>
                    <td>{{ $product->sku ?: '—' }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category ?: '—' }}</td>
                    <td>{{ $product->supplier?->name ?: '—' }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>{{ $product->minimum_quantity }}</td>
                    <td>{{ number_format((float) $product->price, 2) }}</td>
                    <td class="table-actions-cell">
                        <div class="table-actions">
                            <a class="table-action table-action--view" href="{{ route('products.show', $product) }}" aria-label="View {{ $product->name }}">
                                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.75"/></svg>
                                <span>View</span>
                            </a>
                            @role('admin')
                                <a class="table-action table-action--edit" href="{{ route('products.edit', $product) }}" aria-label="Edit {{ $product->name }}">
                                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m4 16.5-.75 4.25L7.5 20 19 8.5 15.5 5 4 16.5Z"/><path d="m13.75 6.75 3.5 3.5"/></svg>
                                    <span>Edit</span>
                                </a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm-message="Archive this product? Products with stock cannot be archived.">
                                    @csrf
                                    @method('DELETE')
                                    <button class="table-action table-action--delete" type="submit" aria-label="Delete {{ $product->name }}">
                                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h16M9 7V4h6v3M6.5 7l.75 13h9.5l.75-13M10 11v5M14 11v5"/></svg>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            @endrole
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No products found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $products->links() }}
@endsection
