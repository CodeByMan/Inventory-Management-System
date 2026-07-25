@extends('layouts.app')
@section('title','Add product | StockManager')
@section('content')<div class="page-heading"><h1>Add product</h1><a href="{{ route('products.index') }}">Back</a></div><form method="POST" action="{{ route('products.store') }}" class="panel stack">@csrf@include('products._form')<button type="submit">Create product</button></form>@endsection
