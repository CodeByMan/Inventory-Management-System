@extends('layouts.app')
@section('title','Top products report | StockManager')
@section('content')<div class="page-heading"><h1>Top products by stock out</h1><a href="{{ route('reports.index') }}">Back</a></div><div class="panel"><table><thead><tr><th>Rank</th><th>Product</th><th>Total withdrawn</th></tr></thead><tbody>@forelse($products as $row)<tr><td>{{ $loop->iteration }}</td><td>{{ $row->product?->name ?: 'Archived product' }}</td><td>{{ $row->total_quantity }}</td></tr>@empty<tr><td colspan="3">No stock-out movements yet.</td></tr>@endforelse</tbody></table></div>@endsection
