@extends('layouts.app')
@section('title', 'Dashboard | StockManager')
@section('content')
<div class="page-heading"><div><h1>Dashboard</h1><p class="muted">A clear overview of inventory activity.</p></div><a class="button" href="{{ route('stock-movements.create') }}">Record movement</a></div>
<div class="stats">
 <article><span>Products</span><strong>{{ $productCount }}</strong></article>
 <article><span>Low stock</span><strong>{{ $lowStockCount }}</strong></article>
 <article><span>Movements</span><strong>{{ $movementCount }}</strong></article>
 <article><span>Total stock in</span><strong>{{ $stockInTotal }}</strong></article>
 <article><span>Total stock out</span><strong>{{ $stockOutTotal }}</strong></article>
</div>
<section class="panel"><h2>Recent movements</h2>
<table><thead><tr><th>Date</th><th>Product</th><th>Type</th><th>Quantity</th><th>Balance</th><th>User</th></tr></thead><tbody>
@forelse($recentMovements as $movement)<tr><td>{{ $movement->created_at->format('Y-m-d H:i') }}</td><td>@if($movement->product->trashed()){{ $movement->product->name }} <span class="muted">(archived)</span>@else<a href="{{ route('products.show',$movement->product) }}">{{ $movement->product->name }}</a>@endif</td><td><span class="badge {{ $movement->type }}">{{ str_replace('_',' ',ucwords($movement->type,'_')) }}</span></td><td>{{ $movement->quantity }}</td><td>{{ $movement->quantity_before }} → {{ $movement->quantity_after }}</td><td>{{ $movement->user->name }}</td></tr>@empty<tr><td colspan="6">No stock movements yet.</td></tr>@endforelse
</tbody></table></section>
@endsection
