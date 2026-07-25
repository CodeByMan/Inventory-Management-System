<?php

namespace Tests\Unit;

use App\Models\StockMovement;
use PHPUnit\Framework\TestCase;

class StockMovementConstantsTest extends TestCase
{
    public function test_movement_types_are_english_and_stable(): void
    {
        $this->assertSame('stock_in', StockMovement::TYPE_IN);
        $this->assertSame('stock_out', StockMovement::TYPE_OUT);
    }
}
