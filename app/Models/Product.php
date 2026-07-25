<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku', 'name', 'description', 'price', 'minimum_quantity', 'supplier_id', 'category',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'minimum_quantity' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function archive(): void
    {
        DB::transaction(function (): void {
            $product = self::query()->lockForUpdate()->findOrFail($this->getKey());

            if ($product->quantity !== 0) {
                throw ValidationException::withMessages([
                    'product' => 'Stock must be reduced to zero before the product can be archived.',
                ]);
            }

            $product->delete();
        }, 3);
    }
}
