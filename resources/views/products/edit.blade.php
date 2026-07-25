@extends('layouts.app')
@section('title','Edit product | StockManager')
@section('content')<div class="page-heading"><div><h1>Edit product</h1><p class="muted">Quantity is read-only. Use a stock movement for adjustments.</p></div><a href="{{ route('products.show',$product) }}">Back</a></div><form method="POST" action="{{ route('products.update',$product) }}" class="panel stack">@csrf @method('PUT')@include('products._form')<button type="submit">Save changes</button></form>@endsection
