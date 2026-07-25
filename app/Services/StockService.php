<?php

namespace App\Services;

use App\Events\StockLevelChanged;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function stockIn(int $productId, int $quantity, int $userId, ?string $note = null): StockMovement
    {
        return $this->apply($productId, StockMovement::TYPE_IN, $quantity, $userId, $note);
    }

    public function stockOut(int $productId, int $quantity, int $userId, ?string $note = null): StockMovement
    {
        return $this->apply($productId, StockMovement::TYPE_OUT, $quantity, $userId, $note);
    }

    public function reverse(int $movementId, int $userId, string $reason): StockMovement
    {
        $movement = DB::transaction(function () use ($movementId, $userId, $reason): StockMovement {
            $original = StockMovement::query()->lockForUpdate()->findOrFail($movementId);

            if ($original->reversal_of_id !== null) {
                throw ValidationException::withMessages([
                    'movement' => 'A reversal record cannot be reversed directly.',
                ]);
            }

            if (StockMovement::query()->where('reversal_of_id', $original->id)->exists()) {
                throw ValidationException::withMessages([
                    'movement' => 'This movement has already been reversed.',
                ]);
            }

            $product = Product::withTrashed()
                ->lockForUpdate()
                ->findOrFail($original->product_id);
            $type = $original->type === StockMovement::TYPE_IN
                ? StockMovement::TYPE_OUT
                : StockMovement::TYPE_IN;

            $before = $product->quantity;
            $after = $type === StockMovement::TYPE_IN
                ? $before + $original->quantity
                : $before - $original->quantity;

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'movement' => 'The reversal would make stock negative.',
                ]);
            }

            if ($product->trashed() && $after > 0) {
                $product->restore();
            }

            $product->forceFill([
                'quantity' => $after,
            ])->saveQuietly();

            return StockMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $original->quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'user_id' => $userId,
                'note' => "Reversal of movement #{$original->id}: {$reason}",
                'reversal_of_id' => $original->id,
            ]);
        }, 3);

        $this->dispatchChangedEvent($movement->product);

        return $movement->load(['product.supplier', 'user', 'reversalOf']);
    }

    private function apply(
        int $productId,
        string $type,
        int $quantity,
        int $userId,
        ?string $note
    ): StockMovement {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Quantity must be at least 1.']);
        }

        $movement = DB::transaction(function () use ($productId, $type, $quantity, $userId, $note): StockMovement {
            // The availability check happens after the row lock is acquired.
            $product = Product::query()->lockForUpdate()->findOrFail($productId);
            $before = $product->quantity;
            $after = $type === StockMovement::TYPE_IN
                ? $before + $quantity
                : $before - $quantity;

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock for this withdrawal.',
                ]);
            }

            $product->forceFill(['quantity' => $after])->saveQuietly();

            return StockMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'user_id' => $userId,
                'note' => $note,
            ]);
        }, 3);

        $this->dispatchChangedEvent($movement->product);

        return $movement->load(['product.supplier', 'user']);
    }

    private function dispatchChangedEvent(Product $product): void
    {
        $productId = $product->getKey();

        DB::afterCommit(function () use ($productId): void {
            $freshProduct = Product::query()->find($productId);

            if ($freshProduct !== null) {
                StockLevelChanged::dispatch($freshProduct);
            }
        });
    }
}
