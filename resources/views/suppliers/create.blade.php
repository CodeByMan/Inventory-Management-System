@extends('layouts.app')
@section('title','Add supplier | StockManager')
@section('content')<div class="page-heading"><h1>Add supplier</h1><a href="{{ route('suppliers.index') }}">Back</a></div><form class="panel stack" method="POST" action="{{ route('suppliers.store') }}">@csrf@include('suppliers._form')<button>Create supplier</button></form>@endsection
