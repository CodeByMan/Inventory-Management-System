@extends('layouts.app')
@section('title','Edit supplier | StockManager')
@section('content')<div class="page-heading"><h1>Edit supplier</h1><a href="{{ route('suppliers.show',$supplier) }}">Back</a></div><form class="panel stack" method="POST" action="{{ route('suppliers.update',$supplier) }}">@csrf @method('PUT')@include('suppliers._form')<button>Save changes</button></form>@endsection
