@extends('layouts.app')
@section('title', 'Suppliers | StockManager')
@section('content')
<div class="page-heading">
    <h1>Suppliers</h1>
    @role('admin')
        <a class="button" href="{{ route('suppliers.create') }}">Add supplier</a>
    @endrole
</div>
<div class="panel table-wrap">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->email ?: '—' }}</td>
                    <td>{{ $supplier->phone ?: '—' }}</td>
                    <td class="table-actions-cell">
                        <div class="table-actions">
                            <a class="table-action table-action--view" href="{{ route('suppliers.show', $supplier) }}" aria-label="View {{ $supplier->name }}">
                                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.75"/></svg>
                                <span>View</span>
                            </a>
                            @role('admin')
                                <a class="table-action table-action--edit" href="{{ route('suppliers.edit', $supplier) }}" aria-label="Edit {{ $supplier->name }}">
                                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m4 16.5-.75 4.25L7.5 20 19 8.5 15.5 5 4 16.5Z"/><path d="m13.75 6.75 3.5 3.5"/></svg>
                                    <span>Edit</span>
                                </a>
                                <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm-message="Delete this supplier? This action may be blocked when products still reference it.">
                                    @csrf
                                    @method('DELETE')
                                    <button class="table-action table-action--delete" type="submit" aria-label="Delete {{ $supplier->name }}">
                                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h16M9 7V4h6v3M6.5 7l.75 13h9.5l.75-13M10 11v5M14 11v5"/></svg>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            @endrole
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No suppliers found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $suppliers->links() }}
@endsection
