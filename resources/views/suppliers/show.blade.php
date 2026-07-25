@extends('layouts.app')

@section('title', $supplier->name.' | StockManager')

@section('content')
    <div class="page-heading">
        <h1>{{ $supplier->name }}</h1>

        <div class="actions">
            @role('admin')
                <a class="button" href="{{ route('suppliers.edit', $supplier) }}">
                    Edit
                </a>

                <form
                    method="POST"
                    action="{{ route('suppliers.destroy', $supplier) }}"
                    onsubmit="return confirm('Delete this supplier? Products will remain without a supplier.')"
                >
                    @csrf
                    @method('DELETE')

                    <button class="danger" type="submit">Delete</button>
                </form>
            @endrole
        </div>
    </div>

    <article class="panel">
        <dl>
            <dt>Email</dt>
            <dd>{{ $supplier->email ?: '—' }}</dd>

            <dt>Phone</dt>
            <dd>{{ $supplier->phone ?: '—' }}</dd>

            <dt>Address</dt>
            <dd>{{ $supplier->address ?: '—' }}</dd>

            <dt>Products</dt>
            <dd>{{ $supplier->products_count }}</dd>
        </dl>
    </article>
@endsection
