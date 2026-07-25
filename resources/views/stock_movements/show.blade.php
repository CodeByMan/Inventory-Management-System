@extends('layouts.app')

@section('title', 'Movement #'.$movement->id.' | StockManager')

@section('content')
    <div class="page-heading">
        <h1>Stock movement #{{ $movement->id }}</h1>
        <a href="{{ route('stock-movements.index') }}">Back</a>
    </div>

    <article class="panel">
        <dl>
            <dt>Product</dt>
            <dd>
                @if ($movement->product->trashed())
                    {{ $movement->product->name }}
                    <span class="muted">(archived)</span>
                @else
                    <a href="{{ route('products.show', $movement->product) }}">
                        {{ $movement->product->name }}
                    </a>
                @endif
            </dd>

            <dt>Type</dt>
            <dd>{{ str_replace('_', ' ', $movement->type) }}</dd>

            <dt>Quantity</dt>
            <dd>{{ $movement->quantity }}</dd>

            <dt>Balance</dt>
            <dd>{{ $movement->quantity_before }} → {{ $movement->quantity_after }}</dd>

            <dt>Performed by</dt>
            <dd>{{ $movement->user->name }}</dd>

            <dt>Date</dt>
            <dd>{{ $movement->created_at->format('Y-m-d H:i:s') }}</dd>

            <dt>Note</dt>
            <dd>{{ $movement->note ?: '—' }}</dd>

            <dt>Linked record</dt>
            <dd>
                @if ($movement->reversal)
                    <a href="{{ route('stock-movements.show', $movement->reversal) }}">
                        Reversal #{{ $movement->reversal->id }}
                    </a>
                @elseif ($movement->reversalOf)
                    <a href="{{ route('stock-movements.show', $movement->reversalOf) }}">
                        Reverses #{{ $movement->reversalOf->id }}
                    </a>
                @else
                    —
                @endif
            </dd>
        </dl>
    </article>

    @role('admin')
        @if (! $movement->reversal && ! $movement->reversal_of_id)
            <form
                class="panel stack"
                method="POST"
                action="{{ route('stock-movements.reverse', $movement) }}"
            >
                @csrf

                <h2>Create compensating reversal</h2>
                <p class="muted">The original ledger entry remains unchanged.</p>

                <label>
                    Reason
                    <textarea
                        name="reason"
                        required
                        minlength="5"
                        maxlength="500"
                    ></textarea>
                </label>

                <button class="danger" type="submit">Record reversal</button>
            </form>
        @endif
    @endrole
@endsection
