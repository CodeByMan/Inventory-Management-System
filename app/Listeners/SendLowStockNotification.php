<?php

namespace App\Listeners;

use App\Events\StockLevelChanged;
use Illuminate\Support\Facades\Log;

class SendLowStockNotification
{
    public function handle(StockLevelChanged $event): void
    {
        if ($event->product->quantity <= $event->product->minimum_quantity) {
            Log::warning('Low stock alert', [
                'product_id' => $event->product->id,
                'product_name' => $event->product->name,
                'quantity' => $event->product->quantity,
            ]);
        }
    }
}
