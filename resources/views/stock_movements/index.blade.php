@extends('layouts.app')
@section('title', 'Stock movements | StockManager')
@section('content')
<div class="page-heading">
    <div>
        <h1>Stock movements</h1>
        <p class="muted">Ledger records are immutable. Corrections use a linked reversal.</p>
    </div>
    <a class="button" href="{{ route('stock-movements.create') }}">Record movement</a>
</div>
<form class="filters" method="GET">
    <select name="product_id">
        <option value="">All products</option>
        @foreach($products as $product)
            <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
        @endforeach
    </select>
    <select name="type">
        <option value="">All types</option>
        <option value="stock_in" @selected(request('type') === 'stock_in')>Stock in</option>
        <option value="stock_out" @selected(request('type') === 'stock_out')>Stock out</option>
    </select>
    <button>Filter</button>
    <a href="{{ route('stock-movements.index') }}">Reset</a>
</form>
<div class="panel table-wrap">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Balance</th>
                <th>User</th>
                <th>Correction</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
                <tr>
                    <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        {{ $movement->product->name }}
                        @if($movement->product->trashed())
                            <span class="muted">(archived)</span>
                        @endif
                    </td>
                    <td>{{ str_replace('_', ' ', $movement->type) }}</td>
                    <td>{{ $movement->quantity }}</td>
                    <td>{{ $movement->quantity_before }} → {{ $movement->quantity_after }}</td>
                    <td>{{ $movement->user->name }}</td>
                    <td>{{ $movement->reversal ? '#'.$movement->reversal->id : ($movement->reversal_of_id ? 'Reverses #'.$movement->reversal_of_id : '—') }}</td>
                    <td class="table-actions-cell">
                        <div class="table-actions">
                            <a class="table-action table-action--view" href="{{ route('stock-movements.show', $movement) }}" aria-label="View stock movement {{ $movement->id }}">
                                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.75"/></svg>
                                <span>View</span>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No movements found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $movements->links() }}
@endsection
